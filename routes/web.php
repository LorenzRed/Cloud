<?php

use App\Mail\PasswordResetOtpMail;
use App\Models\Barangay;
use App\Support\BhwHeadScope;
use App\Support\BhwResidentScope;
use App\Support\BnsChildHealth;
use App\Support\BnsChildNutrition;
use App\Support\BnsReportBuilder;
use App\Support\BnsChildMonitoring;
use App\Support\BnsHealthRecordList;
use App\Support\GeoBoundary;
use App\Support\PersonAge;
use App\Support\PurokLocator;
use App\Support\ResidentBhwChat;
use App\Models\Consultation;
use App\Models\Event;
use App\Models\HouseholdMonitoringCase;
use App\Models\HouseholdMonitoringVisit;
use App\Models\InboxMessage;
use App\Models\MedicalRecord;
use App\Models\Purok;
use App\Models\ResidentRequest;
use App\Models\ResidentBhwMessage;
use App\Models\ResidentTransaction;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
    Route::redirect('/regester', '/register');

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $normalized = mb_strtolower(trim($validated['email']));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalized])
            ->first();

        if ($user === null) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.user')]);
        }

        $throttleKey = 'forgot-password-otp:'.$normalized;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Too many attempts. Try again in '.$seconds.' seconds.']);
        }

        RateLimiter::hit($throttleKey, 60);

        $otp = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        $cacheKey = 'password_reset_otp:'.hash('sha256', mb_strtolower($user->email));
        Cache::put($cacheKey, Hash::make($otp), now()->addMinutes(15));

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($otp, $user->email));
        } catch (Throwable $e) {
            report($e);
            Cache::forget($cacheKey);
            RateLimiter::clear($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'We could not send the code. Check your mail settings or try again later.',
                ]);
        }

        $request->session()->put('password_reset_email', $user->email);

        return redirect()->route('password.reset')
            ->with('status', 'We sent a 6-digit code to your email. Enter it below with your new password.');
    })->name('password.email');

    Route::get('/reset-password', function (Request $request) {
        $email = $request->session()->get('password_reset_email');
        if (! $email) {
            return redirect()->route('password.request')
                ->with('error', 'Request a reset code first.');
        }

        return view('auth.reset-password', ['email' => $email]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $sessionEmail = $request->session()->get('password_reset_email');
        if (! $sessionEmail || mb_strtolower($sessionEmail) !== mb_strtolower($request->input('email'))) {
            return back()->withErrors(['email' => 'Invalid session. Start over from forgot password.']);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($request->input('email')))])
            ->first();

        if ($user === null) {
            return back()->withErrors(['email' => __('passwords.user')]);
        }

        $cacheKey = 'password_reset_otp:'.hash('sha256', mb_strtolower($user->email));
        $hash = Cache::get($cacheKey);

        if (! $hash || ! Hash::check($request->input('otp'), $hash)) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Request a new code from forgot password.']);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
        ])->save();

        Cache::forget($cacheKey);
        $request->session()->forget('password_reset_email');

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('passwords.reset'));
    })->name('password.update');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = mb_strtolower(trim((string) $credentials['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'This email does not exist.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt(
            ['email' => $user->email, 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            return back()->withErrors([
                'password' => 'Password is wrong.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'You are signed in.');
    })->name('login.perform');

    Route::post('/register', function (Request $request) {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email', ''))),
        ]);

        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'email.unique' => 'This email already exists.',
        ]);

        $user = User::create([
            'name' => strstr($data['email'], '@', true) ?: $data['email'],
            'email' => mb_strtolower(trim($data['email'])),
            'role' => 'users',
            'municipality' => 'Victoria',
            'province' => 'Oriental Mindoro',
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome! Your account has been created.');
    })->name('register.perform');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('status', 'You have been signed out.');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile-picture/{user}', function (User $user) {
        $path = $user->profile_picture_path;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path));
    })->name('profile.picture');

    Route::get('/verification-id/{residentRequest}/{side}', function (ResidentRequest $residentRequest, string $side) {
        $viewer = Auth::user();
        $canView = in_array($viewer->role, ['BrgCaptain', 'BNS', 'LGU'], true)
            || ($viewer->role === 'BHW' && BhwResidentScope::canAccess($viewer, $residentRequest))
            || $residentRequest->user_id === $viewer->id;

        if (! $canView) {
            abort(403);
        }

        $path = match ($side) {
            'front' => $residentRequest->id_front_path,
            'back' => $residentRequest->id_back_path,
            default => null,
        };

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path));
    })->whereIn('side', ['front', 'back'])->name('verification.id.photo');

    Route::get('/dashboard', function () {
        $role = Auth::user()->role;

        return match ($role) {
            'LGU' => redirect()->route('lgu.main'),
            'BrgCaptain' => redirect()->route('barangay-captain.main'),
            'BNS' => redirect()->route('bns.main'),
            'BHWHead' => redirect()->route('bhw-head.main'),
            'BHW' => redirect()->route('bhw.main'),
            'users' => redirect()->route('users.main'),
            default => abort(403),
        };
    })->name('dashboard');

    Route::get('/users', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $hasRequest = ResidentRequest::where('user_id', Auth::id())->exists();

        if (! $hasRequest) {
            return redirect()->route('users.verification');
        }

        $residentRequest = ResidentRequest::query()
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->first();

        $verificationStatus = $residentRequest?->status ?? 'unverified';
        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        $upcomingConsultations = Consultation::query()
            ->where('user_id', Auth::id())
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $lastCompletedCheckup = Consultation::query()
            ->where('user_id', Auth::id())
            ->where('scheduled_at', '<', now())
            ->orderByDesc('scheduled_at')
            ->first();

        $medicalSummaryRecords = MedicalRecord::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(6)
            ->get();

        $barangayName = $residentRequest?->barangay ?? null;
        $barangay = $barangayName ? Barangay::where('name', $barangayName)->first() : null;

        $events = $verificationStatus === 'accepted' && $barangay
            ? Event::query()
                ->where('barangay_id', $barangay->id)
                ->where('category', 'event')
                ->whereNull('disabled_at')
                ->where('event_date', '>=', today())
                ->orderBy('event_date')
                ->limit(10)
                ->get()
            : collect();

        $announcements = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(10)
            ->get();

        $nextConsultation = $upcomingConsultations->first();
        $nextCheckupLabel = $nextConsultation
            ? $nextConsultation->scheduled_at?->format('M d')
            : 'N/A';

        $lastCheckupLabel = $lastCompletedCheckup
            ? $lastCompletedCheckup->scheduled_at?->format('M d, Y')
            : 'N/A';

        return view('users.main', compact('unreadInboxCount', 'verificationStatus', 'upcomingConsultations', 'nextCheckupLabel', 'lastCheckupLabel', 'medicalSummaryRecords', 'events', 'announcements'));
    })->name('users.main');

    Route::redirect('/users/events', '/users/announcements')->name('users.events');

    Route::get('/users/announcements', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        $residentRequest = ResidentRequest::query()
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->first();

        if (! $residentRequest) {
            return redirect()->route('users.verification');
        }

        $barangayName = $residentRequest->barangay ?? null;
        $barangay = $barangayName ? Barangay::where('name', $barangayName)->first() : null;

        $announcements = $barangay
            ? Event::query()
                ->where('barangay_id', $barangay->id)
                ->where('category', 'announcement')
                ->whereNull('disabled_at')
                ->orderByDesc('event_date')
                ->orderByDesc('start_time')
                ->get()
            : collect();

        return view('users.announcements', compact('announcements', 'unreadInboxCount'));
    })->name('users.announcements');

    Route::get('/users/notifications', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $notifications = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->where('title', '!=', 'Message from BHW')
            ->latest()
            ->get();

        $unreadInboxCount = $notifications->whereNull('read_at')->count();

        return view('users.notifications', compact('notifications', 'unreadInboxCount'));
    })->name('users.notifications');

    Route::post('/users/notifications/read-all', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return redirect()->route('users.notifications')->with('status', 'All notifications marked as read.');
    })->name('users.notifications.read-all');

    Route::get('/users/profile', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->with(['purok', 'residentRequest.purok'])->findOrFail(Auth::id());
        $user->syncHouseholdNoFromPurok();

        $bhw = ResidentBhwChat::assignedBhwForResident($user);
        $unreadBhwChatCount = $bhw ? ResidentBhwChat::unreadCountForResident($user, $bhw) : 0;

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('users.profile', [
            'user' => $user->fresh(['purok']),
            'unreadInboxCount' => $unreadInboxCount,
            'unreadBhwChatCount' => $unreadBhwChatCount,
        ]);
    })->name('users.profile');

    Route::get('/users/profile/details', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->with('purok')->findOrFail(Auth::id());
        $user->syncHouseholdNoFromPurok();

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('users.profile-details', [
            'user' => $user->fresh(['purok']),
            'unreadInboxCount' => $unreadInboxCount,
        ]);
    })->name('users.profile.details');

    Route::get('/users/profile/chat-support', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('users.chat-support', [
            'user' => Auth::user(),
            'unreadInboxCount' => $unreadInboxCount,
        ]);
    })->name('users.profile.chat-support');

    Route::post('/users/profile/chat-support', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $apiKey = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-4o-mini');

        if (! $apiKey) {
            return response()->json([
                'message' => 'AI chat is not configured yet. Add OPENAI_API_KEY to your .env file.',
            ], 503);
        }

        $history = session()->get('users_chat_history', []);
        if (! is_array($history)) {
            $history = [];
        }

        $history[] = [
            'role' => 'user',
            'content' => $validated['message'],
        ];

        $messages = array_merge([
            [
                'role' => 'system',
                'content' => 'You are Bantay Kalusugan, a concise and helpful health support assistant for residents. Help with appointments, registration, notifications, and medical records. Do not claim to be a doctor. If the user describes severe symptoms or an emergency, tell them to contact local emergency services or a health worker immediately.',
            ],
        ], array_slice($history, -10));

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            report(new \RuntimeException('OpenAI chat request failed: '.$response->body()));

            return response()->json([
                'message' => 'The AI chat service is unavailable right now.',
            ], 502);
        }

        $reply = data_get($response->json(), 'choices.0.message.content', 'Sorry, I could not generate a reply.');

        $history[] = [
            'role' => 'assistant',
            'content' => $reply,
        ];

        session()->put('users_chat_history', array_slice($history, -20));

        return response()->json([
            'message' => $reply,
        ]);
    })->name('users.profile.chat-support.send');

    Route::get('/users/profile/bhw-chat', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->with(['purok', 'residentRequest.purok'])->findOrFail(Auth::id());
        $bhw = ResidentBhwChat::assignedBhwForResident($user);

        $messages = collect();
        $unreadBhwMessageIds = [];
        $unreadBhwChatCount = 0;
        if ($bhw) {
            $unreadBhwMessageIds = ResidentBhwChat::unreadMessageIdsForResident($user, $bhw);
            $unreadBhwChatCount = count($unreadBhwMessageIds);
            ResidentBhwChat::markReadForResident($user, $bhw);
            $messages = ResidentBhwChat::threadMessages($user, $bhw, $user->id);
        }

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('users.bhw-chat', [
            'user' => $user,
            'bhw' => $bhw,
            'messages' => $messages,
            'unreadInboxCount' => $unreadInboxCount,
            'unreadBhwChatCount' => $unreadBhwChatCount,
            'unreadBhwMessageIds' => $unreadBhwMessageIds,
        ]);
    })->name('users.profile.bhw-chat');

    Route::post('/users/profile/bhw-chat/message', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::query()->with(['purok', 'residentRequest.purok'])->findOrFail(Auth::id());
        $bhw = ResidentBhwChat::assignedBhwForResident($user);

        if (! $bhw) {
            return response()->json(['message' => 'No health worker is assigned to your purok yet.'], 422);
        }

        $message = ResidentBhwChat::storeMessage($request, $user, $bhw, $user);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, $user->id),
        ]);
    })->name('users.profile.bhw-chat.message');

    Route::patch('/users/profile/bhw-chat/message/{residentBhwMessage}', function (Request $request, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'users') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! ResidentBhwChat::canEditMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json([
                'message' => ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage)
                    ? 'You can only edit messages within '.ResidentBhwChat::MESSAGE_EDIT_MINUTES.' minutes of sending.'
                    : 'Unable to edit this message.',
            ], ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage) ? 422 : 403);
        }

        $data = $request->validate(['message' => ['required', 'string', 'max:500']]);
        $message = ResidentBhwChat::updateMessage($residentBhwMessage, $data['message']);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, Auth::id()),
        ]);
    })->name('users.profile.bhw-chat.message.update');

    Route::delete('/users/profile/bhw-chat/message/{residentBhwMessage}', function (Request $request, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'users') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json(['message' => 'Unable to remove this message.'], 403);
        }

        $data = $request->validate(['scope' => ['required', 'in:everyone,you']]);
        $result = ResidentBhwChat::unsendMessage($residentBhwMessage, $data['scope'], Auth::id());

        return response()->json(['ok' => true, ...$result]);
    })->name('users.profile.bhw-chat.message.destroy');

    Route::post('/users/profile/bhw-chat/message/{residentBhwMessage}/reaction', function (Request $request, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'users') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! ResidentBhwChat::canAccessMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json(['message' => 'Unable to react to this message.'], 403);
        }

        $data = $request->validate(['emoji' => ['required', 'string', 'max:32']]);
        $message = ResidentBhwChat::toggleReaction($residentBhwMessage, Auth::user(), $data['emoji']);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, Auth::id()),
        ]);
    })->name('users.profile.bhw-chat.message.reaction');

    Route::post('/users/profile', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->with('purok')->findOrFail(Auth::id());
        $user->syncHouseholdNoFromPurok();

        $validator = Validator::make($request->all(), [
            'gender' => ['nullable', 'string', 'max:50'],
            'civil_status' => ['nullable', 'string', Rule::in(User::CIVIL_STATUSES)],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'household_no' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('users.profile.details')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        $profilePicturePath = $user->profile_picture_path;

        if ($request->hasFile('profile_picture')) {
            if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $user->update([
            'gender' => $data['gender'] ?: null,
            'civil_status' => $data['civil_status'] ?: null,
            'contact_number' => $data['contact_number'] ?: null,
            'barangay' => $data['barangay'] ?: null,
            'household_no' => $user->purok_id
                ? ($user->householdPurokLabel() ?: $user->household_no)
                : ($data['household_no'] ?: null),
            'profile_picture_path' => $profilePicturePath,
        ]);

        return redirect()->route('users.profile.details')->with('status', 'Profile updated successfully.');
    })->name('users.profile.update');

    Route::post('/users/profile/password', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('users.profile.details')
                ->withErrors($validator, 'password')
                ->with('open_password_modal', true);
        }

        $user->update([
            'password' => $request->input('password'),
        ]);

        return redirect()->route('users.profile.details')
            ->with('status', 'Password updated successfully.');
    })->name('users.profile.password');

    Route::post('/users/profile/avatar', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user = User::query()->findOrFail(Auth::id());

        $validator = Validator::make($request->all(), [
            'profile_picture' => ['required', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $profilePicturePath = $user->profile_picture_path;

        if ($request->hasFile('profile_picture')) {
            if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->update(['profile_picture_path' => $profilePicturePath]);
        }

        $url = route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]);

        return response()->json(['status' => 'ok', 'url' => $url]);
    })->name('users.profile.avatar');

    Route::get('/users/medical-records', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        $hasRequest = ResidentRequest::where('user_id', Auth::id())->exists();
        
        if (!$hasRequest) {
            return redirect()->route('users.verification');
        }

        $medicalRecords = MedicalRecord::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('users.medical-records', compact('medicalRecords', 'unreadInboxCount'));
    })->name('users.medical-records');

    Route::get('/users/verification', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $existingRequest = ResidentRequest::with('purok')->where('user_id', Auth::id())->first();
        $showSentState = session('request_sent_state', false);
        $barangayBoundaries = Barangay::query()
            ->whereNotNull('boundary_geojson')
            ->orderBy('name')
            ->get(['name', 'boundary_geojson'])
            ->map(fn (Barangay $barangay) => [
                'name' => $barangay->name,
                'geometry' => $barangay->boundary_geojson,
            ])
            ->values();
        $purokBoundaries = Purok::query()
            ->whereNotNull('boundary_geojson')
            ->where('name', '!=', PurokLocator::NON_NAME)
            ->with('barangay:id,name')
            ->orderBy('name')
            ->get(['id', 'barangay_id', 'name', 'boundary_geojson'])
            ->map(fn (Purok $purok) => [
                'id' => $purok->id,
                'name' => $purok->name,
                'barangay_name' => $purok->barangay?->name,
                'geometry' => $purok->boundary_geojson,
            ])
            ->values();
        $barangayOptions = Barangay::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $purokOptions = Purok::query()
            ->where('name', '!=', PurokLocator::NON_NAME)
            ->with('barangay:id,name')
            ->orderBy('name')
            ->get(['id', 'barangay_id', 'name'])
            ->map(fn (Purok $purok) => [
                'id' => $purok->id,
                'name' => $purok->name,
                'barangay_id' => $purok->barangay_id,
                'barangay_name' => $purok->barangay?->name,
            ])
            ->values();

        if ($existingRequest && ! $showSentState && $existingRequest->status !== 'rejected') {
            return redirect()->route('users.main');
        }

        $requestSent = $showSentState;

        return view('users.verification', [
            'requestSent' => $requestSent,
            'residentRequest' => $existingRequest,
            'isRejectedResubmit' => $existingRequest?->status === 'rejected' && ! $requestSent,
            'barangayBoundaries' => $barangayBoundaries,
            'purokBoundaries' => $purokBoundaries,
            'barangayOptions' => $barangayOptions,
            'purokOptions' => $purokOptions,
        ]);
    })->name('users.verification');

    Route::post('/users/verification', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $namePattern = 'regex:/^[A-Za-z,]+(?: [A-Za-z,]+)*$/';
        $childFullNamePattern = 'regex:/^[A-Za-z,.]+(?: [A-Za-z,.]+)*$/';
        $plainTextPattern = 'regex:/^[A-Za-z0-9,]+(?: [A-Za-z0-9,]+)*$/';
        $optionalNamePattern = 'regex:/^$|^[A-Za-z,]+(?: [A-Za-z,]+)*$/';
        $optionalPlainTextPattern = 'regex:/^$|^[A-Za-z0-9,]+(?: [A-Za-z0-9,]+)*$/';

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255', $namePattern],
            'last_name' => ['required', 'string', 'max:255', $namePattern],
            'middle_initial' => ['nullable', 'string', 'max:1', 'regex:/^[A-Za-z]?$/'],
            'location_mode' => ['required', Rule::in(['map', 'manual'])],
            'barangay' => ['required', 'string', 'max:255'],
            'purok' => ['required', 'string', 'max:255', Rule::notIn([PurokLocator::NON_NAME])],
            'contact' => ['required', 'string', 'regex:/^\d{11}$/'],
            'address' => [Rule::requiredIf(fn () => $request->input('location_mode') === 'map'), 'nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'birthdate_display' => ['required', 'date', 'before:today'],
            'parent_role' => ['required', Rule::in(ResidentRequest::PARENT_ROLES)],
            'gender_display' => ['required', 'in:Male,Female'],
            'civil_status' => ['required', 'string', Rule::in(User::CIVIL_STATUSES)],
            'place_of_birth' => ['required', 'string', 'max:255', $plainTextPattern],
            'educational_attainment' => ['required', 'string', Rule::in(ResidentRequest::EDUCATIONAL_ATTAINMENTS)],
            'spouse_first_name' => ['nullable', 'string', 'max:255', $optionalNamePattern],
            'spouse_last_name' => ['nullable', 'string', 'max:255', $optionalNamePattern],
            'spouse_middle_initial' => ['nullable', 'string', 'max:1', 'regex:/^[A-Za-z]?$/'],
            'spouse_birthdate_display' => ['nullable', 'date', 'before:today'],
            'spouse_parent_role' => ['nullable', Rule::in(ResidentRequest::PARENT_ROLES)],
            'spouse_gender_display' => ['nullable', 'in:Male,Female'],
            'spouse_place_of_birth' => ['nullable', 'string', 'max:255', $optionalPlainTextPattern],
            'spouse_educational_attainment' => ['nullable', 'string', Rule::in(ResidentRequest::EDUCATIONAL_ATTAINMENTS)],
            'spouse_full_name' => ['nullable', 'string', 'max:255'],
            'children' => ['nullable', 'array'],
            'children.*.full_name' => ['required', 'string', 'max:255', $childFullNamePattern],
            'children.*.birthdate' => ['required', 'date', 'before_or_equal:today'],
            'children.*.gender' => ['required', 'in:Male,Female'],
            'children.*.relationship' => ['required', Rule::in(ResidentRequest::CHILD_RELATIONSHIPS)],
            'children.*.currently_studying' => ['required', 'in:yes,no,infant'],
            'children.*.school' => ['nullable', 'required_if:children.*.currently_studying,yes', 'string', 'max:255', $optionalPlainTextPattern],
            'children.*.educational_attainment' => ['nullable', 'required_if:children.*.currently_studying,no', Rule::in(ResidentRequest::EDUCATIONAL_ATTAINMENTS)],
            'children.*.occupation' => ['nullable', 'required_if:children.*.currently_studying,no', 'string', 'max:255', $optionalPlainTextPattern],
            'seniors' => ['nullable', 'array'],
            'seniors.*.full_name' => ['required', 'string', 'max:255', $childFullNamePattern],
            'seniors.*.birthdate' => ['required', 'date', 'before_or_equal:today'],
            'seniors.*.gender' => ['required', 'in:Male,Female'],
            'seniors.*.relationship' => ['required', Rule::in(ResidentRequest::HOUSEHOLD_SENIOR_RELATIONSHIPS)],
            'seniors.*.place_of_birth' => ['required', 'string', 'max:255', $plainTextPattern],
            'seniors.*.educational_attainment' => ['required', Rule::in(ResidentRequest::EDUCATIONAL_ATTAINMENTS)],
            'seniors.*.occupation' => ['nullable', 'string', 'max:255', $optionalPlainTextPattern],
            'location_consent' => [Rule::requiredIf(fn () => $request->input('location_mode') === 'map'), 'accepted'],
            'privacy_consent' => ['accepted'],
            'id_type' => ['required', Rule::in(array_keys(ResidentRequest::ID_TYPES))],
            'id_other_label' => ['nullable', 'required_if:id_type,other', 'string', 'max:255', $optionalPlainTextPattern],
            'id_photo_front' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'id_photo_back' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'first_name.required' => 'Please enter your first name.',
            'first_name.regex' => 'First name may only use letters, spaces, and comma as a special character.',
            'last_name.required' => 'Please enter your last name.',
            'last_name.regex' => 'Last name may only use letters, spaces, and comma as a special character.',
            'middle_initial.regex' => 'Middle initial must be a single letter.',
            'birthdate_display.required' => 'Please enter your birthdate.',
            'birthdate_display.before' => 'Birthdate must be a valid past date.',
            'parent_role.required' => 'Please select whether you are the father or mother registering for your family.',
            'parent_role.in' => 'Only a father or mother may register for the family account.',
            'gender_display.required' => 'Please select your parent role.',
            'civil_status.required' => 'Please select your marital status.',
            'civil_status.in' => 'Please select a valid marital status.',
            'place_of_birth.required' => 'Please enter your place of birth.',
            'place_of_birth.regex' => 'Place of birth may only use letters, numbers, spaces, and comma as a special character.',
            'spouse_place_of_birth.regex' => 'Spouse place of birth may only use letters, numbers, spaces, and comma as a special character.',
            'id_other_label.regex' => 'ID label may only use letters, numbers, spaces, and comma as a special character.',
            'children.*.school.regex' => 'School name may only use letters, numbers, spaces, and comma as a special character.',
            'children.*.occupation.regex' => 'Occupation may only use letters, numbers, spaces, and comma as a special character.',
            'children.*.full_name.regex' => 'Child full name may only use letters, spaces, comma, and period as special characters.',
            'seniors.*.full_name.regex' => 'Senior full name may only use letters, spaces, comma, and period as special characters.',
            'seniors.*.place_of_birth.regex' => 'Place of birth may only use letters, numbers, spaces, and comma as a special character.',
            'seniors.*.occupation.regex' => 'Occupation may only use letters, numbers, spaces, and comma as a special character.',
            'educational_attainment.required' => 'Please select your educational attainment.',
            'educational_attainment.in' => 'Please select a valid educational attainment.',
            'contact.required' => 'Please enter your contact number.',
            'contact.regex' => 'Contact number must be exactly 11 digits with no letters or symbols.',
            'address.required' => 'Please use map-based tracking and save your location first.',
            'barangay.required' => 'Please set your barangay and purok before sending your request.',
            'purok.required' => 'Please select a purok.',
            'purok.not_in' => 'Please select a valid purok.',
            'location_mode.required' => 'Please choose how to set your barangay and purok.',
            'location_mode.in' => 'Please choose a valid location tracking method.',
            'location_consent.accepted' => 'You must accept the location terms before using your current position.',
            'privacy_consent.accepted' => 'You must accept the data privacy terms before submitting your verification request.',
            'id_type.required' => 'Please select a valid ID type.',
            'id_type.in' => 'Please select a supported valid ID type.',
            'id_other_label.required_if' => 'Please specify your ID type when Other is selected.',
            'id_photo_front.required' => 'Please capture the front photo of your valid ID.',
            'id_photo_front.image' => 'The front ID file must be an image.',
            'id_photo_front.mimes' => 'The front ID must be a JPG, PNG, or WEBP image.',
            'id_photo_front.max' => 'The front ID image must not be larger than 5 MB.',
            'id_photo_back.required' => 'Please capture the back photo of your valid ID.',
            'id_photo_back.image' => 'The back ID file must be an image.',
            'id_photo_back.mimes' => 'The back ID must be a JPG, PNG, or WEBP image.',
            'id_photo_back.max' => 'The back ID image must not be larger than 5 MB.',
        ]);

        $middleInitial = strtoupper(trim((string) ($data['middle_initial'] ?? '')));
        $middleSegment = $middleInitial !== '' ? $middleInitial.'.' : null;
        $parentRole = (string) ($data['parent_role'] ?? '');
        $expectedGender = $parentRole === ResidentRequest::PARENT_ROLE_FATHER ? 'Male' : 'Female';
        $normalizedGender = filled($data['gender_display'] ?? null)
            ? ucfirst(strtolower(trim((string) $data['gender_display'])))
            : $expectedGender;

        if ($normalizedGender !== $expectedGender) {
            return back()->withErrors([
                'parent_role' => 'Parent role must match the registrant (Father = Male, Mother = Female).',
            ])->withInput();
        }

        $civilStatus = trim((string) ($data['civil_status'] ?? ''));
        $placeOfBirth = trim((string) ($data['place_of_birth'] ?? ''));
        $educationalAttainment = trim((string) ($data['educational_attainment'] ?? ''));
        $requiresSpouseDetails = in_array($civilStatus, User::REQUIRED_SPOUSE_STATUSES, true);
        $showsSpouseSection = in_array($civilStatus, User::SPOUSE_SECTION_STATUSES, true);
        $spouseMiddleInitial = strtoupper(trim((string) ($data['spouse_middle_initial'] ?? '')));
        $spouseMiddleSegment = $spouseMiddleInitial !== '' ? $spouseMiddleInitial.'.' : null;
        $spouseFirstName = trim((string) ($data['spouse_first_name'] ?? ''));
        $spouseLastName = trim((string) ($data['spouse_last_name'] ?? ''));
        $spouseBirthdate = filled($data['spouse_birthdate_display'] ?? null)
            ? (string) $data['spouse_birthdate_display']
            : '';
        $spouseParentRole = trim((string) ($data['spouse_parent_role'] ?? ''));
        $spouseGender = filled($data['spouse_gender_display'] ?? null)
            ? ucfirst(strtolower(trim((string) $data['spouse_gender_display'])))
            : ($spouseParentRole === ResidentRequest::PARENT_ROLE_FATHER ? 'Male' : ($spouseParentRole === ResidentRequest::PARENT_ROLE_MOTHER ? 'Female' : ''));
        $spousePlaceOfBirth = trim((string) ($data['spouse_place_of_birth'] ?? ''));
        $spouseEducationalAttainment = trim((string) ($data['spouse_educational_attainment'] ?? ''));
        $spouseFullName = trim(implode(' ', array_filter([
            $spouseFirstName,
            $spouseMiddleSegment,
            $spouseLastName,
        ])));

        $hasAnySpouseInput = $spouseFirstName !== ''
            || $spouseLastName !== ''
            || $spouseMiddleInitial !== ''
            || $spouseBirthdate !== ''
            || $spouseParentRole !== ''
            || $spousePlaceOfBirth !== ''
            || $spouseEducationalAttainment !== '';

        if ($requiresSpouseDetails || ($showsSpouseSection && $hasAnySpouseInput)) {
            if ($spouseFirstName === '' || $spouseLastName === '') {
                return back()->withErrors([
                    'spouse_full_name' => $requiresSpouseDetails
                        ? 'Please complete your spouse details when marital status is '.$civilStatus.'.'
                        : 'Please enter your spouse\'s first and last name, or leave spouse details empty.',
                ])->withInput();
            }

            if ($spouseBirthdate === '') {
                return back()->withErrors([
                    'spouse_birthdate_display' => 'Please enter your spouse\'s birthdate.',
                ])->withInput();
            }

            if ($spouseParentRole === '' || ! in_array($spouseParentRole, ResidentRequest::PARENT_ROLES, true)) {
                return back()->withErrors([
                    'spouse_parent_role' => 'Please select your spouse\'s parent role.',
                ])->withInput();
            }

            if ($spouseParentRole === $parentRole) {
                return back()->withErrors([
                    'spouse_parent_role' => 'Your spouse\'s parent role must be different from yours.',
                ])->withInput();
            }

            if ($spouseGender === '') {
                return back()->withErrors([
                    'spouse_gender_display' => 'Please select your spouse\'s parent role.',
                ])->withInput();
            }

            if ($spousePlaceOfBirth === '') {
                return back()->withErrors([
                    'spouse_place_of_birth' => 'Please enter your spouse\'s place of birth.',
                ])->withInput();
            }

            if ($spouseEducationalAttainment === '') {
                return back()->withErrors([
                    'spouse_educational_attainment' => 'Please select your spouse\'s educational attainment.',
                ])->withInput();
            }
        } elseif (! $showsSpouseSection) {
            $spouseFullName = '';
            $spouseFirstName = '';
            $spouseLastName = '';
            $spouseMiddleInitial = '';
            $spouseBirthdate = '';
            $spouseParentRole = '';
            $spouseGender = '';
            $spousePlaceOfBirth = '';
            $spouseEducationalAttainment = '';
        }

        $spouseDetails = $spouseFullName !== ''
            ? [
                'first_name' => $spouseFirstName,
                'last_name' => $spouseLastName,
                'middle_initial' => $spouseMiddleInitial !== '' ? $spouseMiddleInitial : null,
                'full_name' => $spouseFullName,
                'birthdate' => $spouseBirthdate,
                'parent_role' => $spouseParentRole,
                'gender' => $spouseGender,
                'place_of_birth' => $spousePlaceOfBirth,
                'educational_attainment' => $spouseEducationalAttainment,
            ]
            : null;
        $householdChildren = [];

        foreach ($data['children'] ?? [] as $child) {
            if (! is_array($child)) {
                continue;
            }

            $childFullName = trim((string) ($child['full_name'] ?? ''));
            if ($childFullName === '') {
                continue;
            }

            $currentlyStudying = in_array($child['currently_studying'] ?? '', ['yes', 'no', 'infant'], true)
                ? (string) $child['currently_studying']
                : 'no';

            if ($currentlyStudying === 'infant') {
                $childBirthdate = (string) $child['birthdate'];
                $householdChildren[] = [
                    'full_name' => $childFullName,
                    'birthdate' => $childBirthdate,
                    'age_in_months' => PersonAge::ageInWholeMonths($childBirthdate),
                    'gender' => ucfirst(strtolower(trim((string) $child['gender']))),
                    'relationship' => (string) $child['relationship'],
                    'currently_studying' => 'infant',
                    'school' => null,
                    'educational_attainment' => null,
                    'occupation' => null,
                ];

                continue;
            }

            if ($currentlyStudying === 'yes') {
                $childSchool = trim((string) ($child['school'] ?? ''));
                if ($childSchool === '') {
                    return back()->withErrors([
                        'children' => 'Please enter the school for each child who is currently studying.',
                    ])->withInput();
                }

                $childBirthdate = (string) $child['birthdate'];
                $householdChildren[] = [
                    'full_name' => $childFullName,
                    'birthdate' => $childBirthdate,
                    'age_in_months' => PersonAge::ageInWholeMonths($childBirthdate),
                    'gender' => ucfirst(strtolower(trim((string) $child['gender']))),
                    'relationship' => (string) $child['relationship'],
                    'currently_studying' => 'yes',
                    'school' => $childSchool,
                    'educational_attainment' => null,
                    'occupation' => null,
                ];

                continue;
            }

            $childEducationalAttainment = trim((string) ($child['educational_attainment'] ?? ''));
            $occupation = trim((string) ($child['occupation'] ?? ''));

            if ($childEducationalAttainment === '' || $occupation === '') {
                return back()->withErrors([
                    'children' => 'Please complete educational attainment and occupation for each child who is not studying.',
                ])->withInput();
            }

            $childBirthdate = (string) $child['birthdate'];
            $householdChildren[] = [
                'full_name' => $childFullName,
                'birthdate' => $childBirthdate,
                'age_in_months' => PersonAge::ageInWholeMonths($childBirthdate),
                'gender' => ucfirst(strtolower(trim((string) $child['gender']))),
                'relationship' => (string) $child['relationship'],
                'currently_studying' => 'no',
                'school' => null,
                'educational_attainment' => $childEducationalAttainment,
                'occupation' => $occupation,
            ];
        }

        $householdSeniors = [];

        foreach ($data['seniors'] ?? [] as $senior) {
            if (! is_array($senior)) {
                continue;
            }

            $seniorFullName = trim((string) ($senior['full_name'] ?? ''));
            if ($seniorFullName === '') {
                continue;
            }

            $seniorBirthdate = (string) $senior['birthdate'];
            $seniorAge = PersonAge::ageInYears($seniorBirthdate);

            if ($seniorAge === null || $seniorAge < 60) {
                return back()->withErrors([
                    'seniors' => 'Each elderly household member must be at least 60 years old.',
                ])->withInput();
            }

            $occupation = trim((string) ($senior['occupation'] ?? ''));
            $placeOfBirth = trim((string) ($senior['place_of_birth'] ?? ''));
            $educationalAttainment = trim((string) ($senior['educational_attainment'] ?? ''));

            if ($placeOfBirth === '' || $educationalAttainment === '') {
                return back()->withErrors([
                    'seniors' => 'Please complete place of birth and educational attainment for each elderly household member.',
                ])->withInput();
            }

            $householdSeniors[] = [
                'full_name' => $seniorFullName,
                'birthdate' => $seniorBirthdate,
                'age_in_years' => $seniorAge,
                'gender' => ucfirst(strtolower(trim((string) $senior['gender']))),
                'relationship' => (string) $senior['relationship'],
                'place_of_birth' => $placeOfBirth,
                'educational_attainment' => $educationalAttainment,
                'occupation' => $occupation !== '' ? $occupation : null,
            ];
        }

        $birthdate = filled($data['birthdate_display'] ?? null)
            ? Carbon::parse((string) $data['birthdate_display'])
            : null;
        $calculatedAge = PersonAge::ageInYears($birthdate?->toDateString());
        $fullName = trim(implode(' ', array_filter([
            trim((string) $data['first_name']),
            $middleSegment,
            trim((string) $data['last_name']),
        ])));
        $locationMode = (string) ($data['location_mode'] ?? 'map');
        $submittedBarangay = trim((string) $data['barangay']);
        $submittedPurokName = trim((string) ($data['purok'] ?? ''));
        $coordinateString = trim((string) ($data['address'] ?? ''));
        $geoPoint = null;
        $resolvedPurok = null;
        $savedAddress = null;

        if ($locationMode === 'manual') {
            $barangayModel = Barangay::query()->where('name', $submittedBarangay)->first();

            if (! $barangayModel) {
                return back()->withErrors([
                    'barangay' => 'Please select a valid barangay.',
                ])->withInput();
            }

            $purokName = $submittedPurokName;

            if ($purokName === '' || $purokName === PurokLocator::NON_NAME) {
                return back()->withErrors([
                    'purok' => 'Please select a purok.',
                ])->withInput();
            }

            $resolvedPurok = Purok::query()
                ->where('barangay_id', $barangayModel->id)
                ->where('name', $purokName)
                ->where('name', '!=', PurokLocator::NON_NAME)
                ->first();

            if (! $resolvedPurok) {
                return back()->withErrors([
                    'purok' => 'Please select a valid purok for the chosen barangay.',
                ])->withInput();
            }

            $submittedBarangay = $barangayModel->name;
        } else {
            if ($coordinateString !== '' && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $coordinateString, $matches)) {
                $geoPoint = [
                    'lat' => (float) $matches[1],
                    'lng' => (float) $matches[2],
                ];
            }

            $pointInRing = static function (float $lat, float $lng, array $ring): bool {
                $inside = false;
                $vertexCount = count($ring);

                if ($vertexCount < 3) {
                    return false;
                }

                for ($i = 0, $j = $vertexCount - 1; $i < $vertexCount; $j = $i++) {
                    $current = $ring[$i] ?? [];
                    $previous = $ring[$j] ?? [];

                    $xi = (float) ($current[0] ?? 0);
                    $yi = (float) ($current[1] ?? 0);
                    $xj = (float) ($previous[0] ?? 0);
                    $yj = (float) ($previous[1] ?? 0);

                    $intersects = (($yi > $lat) !== ($yj > $lat))
                        && ($lng < (($xj - $xi) * ($lat - $yi) / (($yj - $yi) !== 0.0 ? ($yj - $yi) : 0.0000001)) + $xi);

                    if ($intersects) {
                        $inside = ! $inside;
                    }
                }

                return $inside;
            };

            $geometryContainsPoint = null;
            $geometryContainsPoint = static function (array $geometry, float $lat, float $lng) use (&$geometryContainsPoint, $pointInRing): bool {
                $geometryType = $geometry['type'] ?? '';
                $coordinates = $geometry['coordinates'] ?? [];

                if ($geometryType === 'FeatureCollection') {
                    foreach ($geometry['features'] ?? [] as $feature) {
                        if ($geometryContainsPoint($feature, $lat, $lng)) {
                            return true;
                        }
                    }

                    return false;
                }

                if ($geometryType === 'Feature') {
                    return $geometryContainsPoint($geometry['geometry'] ?? [], $lat, $lng);
                }

                if ($geometryType === 'Polygon') {
                    $rings = is_array($coordinates) ? $coordinates : [];
                    $outerRing = $rings[0] ?? [];

                    if (! $pointInRing($lat, $lng, $outerRing)) {
                        return false;
                    }

                    foreach (array_slice($rings, 1) as $hole) {
                        if ($pointInRing($lat, $lng, $hole)) {
                            return false;
                        }
                    }

                    return true;
                }

                if ($geometryType === 'MultiPolygon') {
                    foreach ($coordinates as $polygon) {
                        if ($geometryContainsPoint(['type' => 'Polygon', 'coordinates' => $polygon], $lat, $lng)) {
                            return true;
                        }
                    }
                }

                return false;
            };

            $hasBarangayBoundaries = Barangay::query()->whereNotNull('boundary_geojson')->exists();

            if ($geoPoint) {
                $matchedBarangay = Barangay::query()
                    ->whereNotNull('boundary_geojson')
                    ->get(['name', 'boundary_geojson'])
                    ->first(function (Barangay $barangay) use ($geometryContainsPoint, $geoPoint) {
                        return is_array($barangay->boundary_geojson)
                            && $geometryContainsPoint($barangay->boundary_geojson, $geoPoint['lat'], $geoPoint['lng']);
                    });

                if ($matchedBarangay) {
                    $submittedBarangay = $matchedBarangay->name;
                } elseif ($hasBarangayBoundaries) {
                    return back()->withErrors([
                        'barangay' => 'Your location is outside the barangay boundary. Please choose a point inside the highlighted area.',
                    ])->withInput();
                }
            }

            if ($geoPoint && $submittedBarangay === '') {
                return back()->withErrors([
                    'barangay' => 'Your location could not be matched to a barangay boundary. Please track a valid location.',
                ])->withInput();
            }

            if ($geoPoint) {
                $barangayModel = Barangay::query()
                    ->where('name', $submittedBarangay)
                    ->first();

                if ($barangayModel && $submittedPurokName !== '' && $submittedPurokName !== PurokLocator::NON_NAME) {
                    $resolvedPurok = Purok::query()
                        ->where('barangay_id', $barangayModel->id)
                        ->where('name', $submittedPurokName)
                        ->where('name', '!=', PurokLocator::NON_NAME)
                        ->first();
                }
            }

            $savedAddress = $coordinateString !== '' ? $coordinateString : null;
        }

        if (! $resolvedPurok) {
            return back()->withErrors([
                'purok' => 'Please select a valid purok.',
            ])->withInput();
        }

        $existingRequest = ResidentRequest::query()->where('user_id', Auth::id())->first();

        foreach (['id_front_path', 'id_back_path'] as $pathColumn) {
            $oldPath = $existingRequest?->{$pathColumn};

            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $storageFolder = 'verification-ids/'.Auth::id();
        $idFrontPath = $request->file('id_photo_front')->store($storageFolder, 'public');
        $idBackPath = $request->hasFile('id_photo_back')
            ? $request->file('id_photo_back')->store($storageFolder, 'public')
            : null;
        $idOtherLabel = $data['id_type'] === ResidentRequest::ID_TYPE_OTHER
            ? trim((string) ($data['id_other_label'] ?? ''))
            : null;

        ResidentRequest::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'full_name' => $fullName,
                'parent_role' => $parentRole,
                'barangay' => $submittedBarangay,
                'purok_id' => $resolvedPurok?->id,
                'contact' => $data['contact'] ?? null,
                'address' => $savedAddress,
                'age' => $calculatedAge,
                'place_of_birth' => $placeOfBirth,
                'civil_status' => $civilStatus,
                'educational_attainment' => $educationalAttainment,
                'spouse_full_name' => $spouseFullName !== '' ? $spouseFullName : null,
                'spouse_details' => $spouseDetails,
                'household_children' => $householdChildren !== [] ? $householdChildren : null,
                'household_seniors' => $householdSeniors !== [] ? $householdSeniors : null,
                'status' => 'pending',
                'id_type' => $data['id_type'],
                'id_other_label' => $idOtherLabel,
                'id_front_path' => $idFrontPath,
                'id_back_path' => $idBackPath,
                'reviewed_at' => null,
            ]
        );

        User::query()
            ->whereKey(Auth::id())
            ->update([
                'name' => $fullName,
                'full_name' => $fullName,
                'gender' => $normalizedGender,
                'parent_role' => $parentRole,
                'date_of_birth' => $birthdate?->toDateString(),
                'age' => $calculatedAge,
                'civil_status' => $civilStatus,
                'educational_attainment' => $educationalAttainment,
                'spouse_full_name' => $spouseFullName !== '' ? $spouseFullName : null,
                'spouse_details' => $spouseDetails,
                'place_of_birth' => $placeOfBirth,
                'contact_number' => $data['contact'] ?? null,
                'barangay' => $submittedBarangay ?: null,
                'purok_id' => $resolvedPurok?->id,
                'household_no' => $resolvedPurok?->name,
            ]);

        return redirect()->route('users.verification')->with('request_sent_state', true);
    })->name('users.verification.submit');

    Route::get('/lgu', function () {
        return Auth::user()->role === 'LGU'
            ? view('lgu.main')
            : redirect()->route('dashboard');
    })->name('lgu.main');

    Route::get('/lgu/barangays', function () {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        $barangays = Barangay::with('captain')
            ->latest()
            ->get();

        $captains = User::query()
            ->with('ledBarangay')
            ->where('role', 'BrgCaptain')
            ->latest()
            ->get();

        return view('lgu.barangays', compact('barangays', 'captains'));
    })->name('lgu.barangays');

    Route::post('/lgu/barangays', function (Request $request) {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        $barangayId = $request->input('barangay_id');

        if (filled($barangayId)) {
            $barangay = Barangay::query()->find($barangayId);

            if (! $barangay) {
                return back()->withErrors(['name' => 'Barangay not found.'])->withInput();
            }

            $data = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('barangays', 'name')->ignore($barangay->id)],
                'boundary_geojson' => ['required', 'json'],
            ]);

            $barangay->update([
                'name' => $data['name'],
                'boundary_geojson' => json_decode($data['boundary_geojson'], true),
            ]);

            if ($barangay->captain) {
                $barangay->captain->update([
                    'barangay' => $barangay->name,
                ]);
            }

            return redirect()->route('lgu.barangays')->with('status', 'Barangay updated successfully.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:barangays,name'],
            'boundary_geojson' => ['required', 'json'],
        ]);

        Barangay::create([
            'name' => $data['name'],
            'boundary_geojson' => json_decode($data['boundary_geojson'], true),
        ]);

        return redirect()->route('lgu.barangays')->with('status', 'Barangay added successfully.');
    })->name('lgu.barangays.store');

    Route::post('/lgu/barangays/captains', function (Request $request) {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        $request->session()->flash('reopen_captain_modal', true);

        $data = $request->validate([
            'captain_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'captain_password' => ['required', 'string', 'min:8', 'confirmed'],
            'captain_barangay_id' => ['nullable', 'integer', 'exists:barangays,id'],
        ]);

        $email = mb_strtolower(trim($data['captain_email']));
        $captainName = Str::title(str_replace(['.', '_', '-'], ' ', Str::before($email, '@')));

        $barangay = ! empty($data['captain_barangay_id'])
            ? Barangay::query()->findOrFail($data['captain_barangay_id'])
            : null;

        $captain = User::query()->create([
            'name' => $captainName,
            'full_name' => $captainName,
            'email' => $email,
            'role' => 'BrgCaptain',
            'barangay' => $barangay?->name,
            'password' => $data['captain_password'],
        ]);

        if ($barangay) {
            $barangay->update([
                'captain_user_id' => $captain->id,
            ]);
        }

        $status = $barangay
            ? 'Barangay captain created and assigned successfully.'
            : 'Barangay captain created successfully. Assign them to a barangay from the table.';

        return redirect()->route('lgu.barangays')->with('status', $status);
    })->name('lgu.barangays.captains.store');

    Route::post('/lgu/barangays/captains/assignment', function (Request $request) {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'captain_user_id' => ['required', 'integer', 'exists:users,id'],
            'captain_barangay_id' => ['nullable', 'integer', 'exists:barangays,id'],
            'captain_action' => ['required', Rule::in(['assign', 'remove'])],
        ]);

        $captain = User::query()
            ->where('role', 'BrgCaptain')
            ->findOrFail($data['captain_user_id']);

        DB::transaction(function () use ($captain, $data) {
            $currentBarangay = Barangay::query()
                ->where('captain_user_id', $captain->id)
                ->first();

            if ($data['captain_action'] === 'remove') {
                if ($currentBarangay) {
                    $currentBarangay->update(['captain_user_id' => null]);
                }

                $captain->update(['barangay' => null]);

                return;
            }

            $targetBarangay = Barangay::query()->findOrFail($data['captain_barangay_id']);
            $existingCaptain = $targetBarangay->captain;

            if ($currentBarangay && $currentBarangay->isNot($targetBarangay)) {
                $currentBarangay->update(['captain_user_id' => null]);
            }

            if ($existingCaptain && $existingCaptain->isNot($captain)) {
                $existingCaptain->update(['barangay' => null]);
            }

            $targetBarangay->update([
                'captain_user_id' => $captain->id,
            ]);

            $captain->update([
                'barangay' => $targetBarangay->name,
            ]);
        });

        return redirect()->route('lgu.barangays')->with('status', 'Barangay captain assignment updated successfully.');
    })->name('lgu.barangays.captains.assignment');

    Route::delete('/lgu/barangays/captains/{captain}', function (User $captain) {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        if ($captain->role !== 'BrgCaptain') {
            return back()->withErrors(['captain' => 'Selected user is not a barangay captain.']);
        }

        DB::transaction(function () use ($captain) {
            Barangay::query()
                ->where('captain_user_id', $captain->id)
                ->update(['captain_user_id' => null]);

            $captain->delete();
        });

        return redirect()->route('lgu.barangays')->with('status', 'Barangay captain deleted successfully.');
    })->name('lgu.barangays.captains.destroy');

    Route::delete('/lgu/barangays/{barangay}', function (Barangay $barangay) {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        $captainId = $barangay->captain_user_id;

        $barangay->delete();

        if ($captainId) {
            User::query()
                ->whereKey($captainId)
                ->update(['barangay' => null]);
        }

        return redirect()->route('lgu.barangays')->with('status', 'Barangay deleted successfully.');
    })->name('lgu.barangays.destroy');

    Route::get('/lgu/reports', function () {
        return Auth::user()->role === 'LGU'
            ? view('lgu.reports')
            : redirect()->route('dashboard');
    })->name('lgu.reports');

    Route::get('/lgu/officials', function () {
        return Auth::user()->role === 'LGU'
            ? view('lgu.officials')
            : redirect()->route('dashboard');
    })->name('lgu.officials');

    $buildCaptainDashboardPayload = function (?Barangay $assignedBarangay, ?\Illuminate\Support\Collection $residentsOverride = null): array {
        if (! $assignedBarangay) {
            return [
                'dashboardStats' => [],
                'recentHealthUpdates' => [],
                'monthlyIllnessTrend' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    'values' => [0, 0, 0, 0, 0, 0],
                ],
                'vaccinationDistribution' => [
                    'vaccinated' => 0,
                    'unvaccinated' => 0,
                ],
                'ageDistribution' => [
                    'labels' => ['0-17', '18-35', '36-59', '60+'],
                    'values' => [0, 0, 0, 0],
                ],
            ];
        }

        $acceptedResidents = $residentsOverride ?? ResidentRequest::query()
            ->where('status', 'accepted')
            ->where('barangay', $assignedBarangay->name)
            ->with('user')
            ->get();

        $residentIds = $acceptedResidents->pluck('id')->filter()->values();

        $totalResidents = $acceptedResidents->count();
        $totalHouseholds = $acceptedResidents
            ->map(fn (ResidentRequest $resident) => trim((string) ($resident->user?->household_no ?? '')))
            ->filter()
            ->unique()
            ->count();

        $seniorCitizens = $acceptedResidents->filter(fn (ResidentRequest $resident) => (int) ($resident->age ?? 0) >= 60)->count();
        $pregnantWomen = $acceptedResidents->filter(function (ResidentRequest $resident) {
            $isFemale = strtolower((string) ($resident->user?->gender ?? '')) === 'female';
            $age = (int) ($resident->age ?? 0);

            return $isFemale && $age >= 15 && $age <= 49;
        })->count();

        $medicalRecords = $residentIds->isNotEmpty()
            ? MedicalRecord::query()->whereIn('resident_request_id', $residentIds)->get()
            : collect();

        $vaccinatedResidents = $medicalRecords
            ->filter(fn (MedicalRecord $record) => str_contains(strtolower((string) $record->record_type), 'vacc'))
            ->pluck('resident_request_id')
            ->unique()
            ->count();

        $activeIllnessCases = $medicalRecords
            ->filter(fn (MedicalRecord $record) => preg_match('/illness|condition|diagnosis/i', (string) $record->record_type) === 1)
            ->pluck('resident_request_id')
            ->unique()
            ->count();

        $malnutritionCases = $medicalRecords
            ->filter(fn (MedicalRecord $record) => preg_match('/malnutrition|underweight|wasting|stunting/i', (string) $record->record_type) === 1)
            ->pluck('resident_request_id')
            ->unique()
            ->count();

        $dashboardStats = [
            'total_registered_residents' => $totalResidents,
            'total_households' => $totalHouseholds,
            'active_illness_cases' => $activeIllnessCases,
            'malnutrition_cases' => $malnutritionCases,
            'vaccinated_residents' => $vaccinatedResidents,
            'pregnant_women' => $pregnantWomen,
            'senior_citizens' => $seniorCitizens,
        ];

        $monthOffsets = [5, 4, 3, 2, 1, 0];
        $trendLabels = [];
        $trendValues = [];

        foreach ($monthOffsets as $offset) {
            $monthStart = Carbon::now()->subMonths($offset)->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();

            $trendLabels[] = $monthStart->format('M');
            $trendValues[] = $residentIds->isNotEmpty()
                ? Consultation::query()
                    ->whereIn('resident_request_id', $residentIds)
                    ->whereBetween('scheduled_at', [$monthStart, $monthEnd])
                    ->count()
                : 0;
        }

        $ageDistribution = [
            'labels' => ['0-17', '18-35', '36-59', '60+'],
            'values' => [
                $acceptedResidents->filter(fn (ResidentRequest $resident) => (int) ($resident->age ?? 0) >= 0 && (int) ($resident->age ?? 0) <= 17)->count(),
                $acceptedResidents->filter(fn (ResidentRequest $resident) => (int) ($resident->age ?? 0) >= 18 && (int) ($resident->age ?? 0) <= 35)->count(),
                $acceptedResidents->filter(fn (ResidentRequest $resident) => (int) ($resident->age ?? 0) >= 36 && (int) ($resident->age ?? 0) <= 59)->count(),
                $acceptedResidents->filter(fn (ResidentRequest $resident) => (int) ($resident->age ?? 0) >= 60)->count(),
            ],
        ];

        $recentHealthUpdates = $residentIds->isNotEmpty()
            ? MedicalRecord::query()
                ->with('residentRequest')
                ->whereIn('resident_request_id', $residentIds)
                ->latest()
                ->take(6)
                ->get()
                ->map(function (MedicalRecord $record) {
                    $residentName = $record->residentRequest?->full_name ?: 'Resident';
                    $recordType = trim((string) $record->record_type) !== '' ? $record->record_type : 'Medical update';
                    $recordValue = trim((string) $record->record_value) !== '' ? $record->record_value : 'No value provided';
                    $note = trim((string) ($record->note ?? ''));

                    return [
                        'title' => $residentName.' - '.$recordType,
                        'summary' => $note !== '' ? $recordValue.' ('.$note.')' : $recordValue,
                        'created_at' => optional($record->created_at)->format('M d, Y h:i A'),
                    ];
                })
                ->all()
            : [];

        return [
            'dashboardStats' => $dashboardStats,
            'recentHealthUpdates' => $recentHealthUpdates,
            'monthlyIllnessTrend' => [
                'labels' => $trendLabels,
                'values' => $trendValues,
            ],
            'vaccinationDistribution' => [
                'vaccinated' => $vaccinatedResidents,
                'unvaccinated' => max($totalResidents - $vaccinatedResidents, 0),
            ],
            'ageDistribution' => $ageDistribution,
        ];
    };

    Route::get('/barangay-captain', function () use ($buildCaptainDashboardPayload) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        $dashboardPayload = $buildCaptainDashboardPayload($assignedBarangay);

        return view('barangay_captain.main', array_merge([
            'assignedBarangay' => $assignedBarangay,
        ], $dashboardPayload));
    })->name('barangay-captain.main');

    Route::get('/brgcaptain', function () use ($buildCaptainDashboardPayload) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        $dashboardPayload = $buildCaptainDashboardPayload($assignedBarangay);

        return view('barangay_captain.main', array_merge([
            'assignedBarangay' => $assignedBarangay,
        ], $dashboardPayload));
    })->name('brgcaptain.main');

    Route::get('/barangay-captain/residents', function () {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        $residents = collect();

        if ($assignedBarangay) {
            $residents = ResidentRequest::query()
                ->where('status', 'accepted')
                ->where('barangay', $assignedBarangay->name)
                ->with('user')
                ->latest('reviewed_at')
                ->get();
        }

        return view('barangay_captain.residents', compact('assignedBarangay', 'residents'));
    })->name('barangay-captain.residents');

    Route::get('/barangay-captain/reports', function () {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        $fromDate = request('from_date');
        $toDate = request('to_date');

        $residentsQuery = ResidentRequest::query()
            ->where('status', 'accepted');

        if ($assignedBarangay) {
            $residentsQuery->where('barangay', $assignedBarangay->name);
        }

        if ($fromDate) {
            $residentsQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $residentsQuery->whereDate('created_at', '<=', $toDate);
        }

        $residents = $residentsQuery->latest('reviewed_at')->get();

        $totalResidents = $residents->count();
        $totalHouseholds = $residents
            ->pluck('address')
            ->filter(fn ($address) => filled($address))
            ->unique()
            ->count();
        $seniorCitizens = $residents->filter(fn ($resident) => (int) ($resident->age ?? 0) >= 60)->count();
        $childrenUnderFive = $residents->filter(fn ($resident) => (int) ($resident->age ?? 0) <= 5)->count();

        $reportData = [
            'monthly_barangay_health_report' => [
                'total_residents' => $totalResidents,
                'total_households' => $totalHouseholds,
                'senior_citizens' => $seniorCitizens,
            ],
            'malnutrition_summary' => [
                'identified_cases' => 0,
                'under_monitoring' => $childrenUnderFive,
            ],
            'vaccination_coverage_report' => [
                'vaccinated' => 0,
                'unvaccinated' => $totalResidents,
            ],
            'maternal_summary' => [
                'pregnant_women' => 0,
                'prenatal_monitoring' => 0,
            ],
            'illness_case_summary' => [
                'active_cases' => 0,
                'resolved_cases' => 0,
            ],
            'health_program_status_report' => [
                ['program' => 'Nutrition Program', 'status' => 'Ongoing'],
                ['program' => 'Vaccination Drive', 'status' => 'Planned'],
                ['program' => 'Community Health Education', 'status' => 'Ongoing'],
            ],
        ];

        return view('barangay_captain.reports', compact('assignedBarangay', 'residents', 'reportData', 'fromDate', 'toDate'));
    })->name('barangay-captain.reports');

    Route::get('/barangay-captain/events', function () {
        return redirect()->route('dashboard');
    })->name('barangay-captain.events');

    Route::get('/barangay-captain/posts', function () {
        return redirect()->route('dashboard');
    })->name('barangay-captain.posts');

    Route::post('/barangay-captain/posts', function () {
        return redirect()->route('dashboard');
    })->name('barangay-captain.posts.store');

    Route::put('/barangay-captain/posts/{post}', function () {
        return redirect()->route('dashboard');
    })->name('barangay-captain.posts.update');

    Route::delete('/barangay-captain/posts/{post}', function () {
        return redirect()->route('dashboard');
    })->name('barangay-captain.posts.destroy');

    Route::post('/barangay-captain/events', function () {
        return redirect()->route('dashboard');
    })->name('barangay-captain.events.store');

    Route::get('/bhw/announcements', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        return view('bhw.events');
    })->name('bhw.announcements');

    Route::redirect('/bhw/events', '/bhw/announcements')->name('bhw.events');

    Route::get('/bhw/posts', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $barangayName = trim((string) Auth::user()->barangay);
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        $posts = $assignedBarangay
            ? Event::query()
                ->where('barangay_id', $assignedBarangay->id)
                ->where('category', 'announcement')
                ->orderByDesc('event_date')
                ->orderByDesc('start_time')
                ->get()
            : collect();

        return view('bhw.posts', compact('assignedBarangay', 'posts'));
    })->name('bhw.posts');

    Route::post('/bhw/posts', function (Request $request) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $barangayName = trim((string) Auth::user()->barangay);
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        if (! $assignedBarangay) {
            return response()->json(['error' => 'No assigned barangay'], 404);
        }

        $data = $request->validate([
            'type' => ['nullable', 'in:announcement'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('event-images', 'public');
        }

        $post = Event::create([
            'barangay_id' => $assignedBarangay->id,
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'],
            'category' => 'announcement',
            'image_url' => $imageUrl,
        ]);

        $notificationMessage = $data['title'];

        if (filled($data['description'] ?? null)) {
            $notificationMessage .= ': '.Str::limit(trim((string) $data['description']), 240);
        }

        $residentUserIds = ResidentRequest::query()
            ->where('barangay', $assignedBarangay->name)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $now = now();

        foreach ($residentUserIds as $residentUserId) {
            InboxMessage::query()->create([
                'user_id' => $residentUserId,
                'title' => 'New Barangay Announcement',
                'message' => $notificationMessage,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! $request->expectsJson()) {
            return redirect()
                ->route('bhw.posts')
                ->with('status', 'Post created successfully.');
        }

        return response()->json([
            'success' => true,
            'post' => $post,
            'image_url' => $imageUrl ? Storage::url($imageUrl) : null,
            'message' => 'Post created successfully',
        ]);
    })->name('bhw.posts.store');

    Route::post('/bhw/posts/{post}/toggle-disable', function (Event $post) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $barangayName = trim((string) Auth::user()->barangay);
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        abort_unless($assignedBarangay && $post->barangay_id === $assignedBarangay->id, 404);

        $post->disabled_at = $post->disabled_at ? null : now();
        $post->save();

        return redirect()->route('bhw.posts')->with('status', $post->disabled_at ? 'Post disabled successfully.' : 'Post re-enabled successfully.');
    })->name('bhw.posts.toggle-disable');

    Route::put('/bhw/posts/{post}', function (Request $request, Event $post) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $barangayName = trim((string) Auth::user()->barangay);
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        abort_unless($assignedBarangay && $post->barangay_id === $assignedBarangay->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $imageUrl = $post->image_url;

        if ($request->hasFile('image')) {
            if ($imageUrl && Storage::disk('public')->exists($imageUrl)) {
                Storage::disk('public')->delete($imageUrl);
            }

            $imageUrl = $request->file('image')->store('event-images', 'public');
        }

        $post->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'],
            'category' => 'announcement',
            'image_url' => $imageUrl,
        ]);

        return redirect()->route('bhw.posts')->with('status', 'Post updated successfully.');
    })->name('bhw.posts.update');

    Route::delete('/bhw/posts/{post}', function (Event $post) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $barangayName = trim((string) Auth::user()->barangay);
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        abort_unless($assignedBarangay && $post->barangay_id === $assignedBarangay->id, 404);

        if ($post->image_url && Storage::disk('public')->exists($post->image_url)) {
            Storage::disk('public')->delete($post->image_url);
        }

        $post->delete();

        return redirect()->route('bhw.posts')->with('status', 'Post deleted successfully.');
    })->name('bhw.posts.destroy');

    Route::get('/barangay-captain/bhw-workers', function () {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        $bhwWorkers = collect();
        $puroks = collect();
        $manageablePuroks = collect();

        if ($assignedBarangay) {
            PurokLocator::ensureNonPurok($assignedBarangay);

            $bhwWorkers = User::query()
                ->with('assignedPuroks')
                ->whereIn('role', [User::ROLE_BHW, User::ROLE_BHW_HEAD])
                ->where('barangay', $assignedBarangay->name)
                ->orderBy('name')
                ->get();

            $puroks = Purok::query()
                ->where('barangay_id', $assignedBarangay->id)
                ->orderBy('name')
                ->get();

            $manageablePuroks = $puroks
                ->reject(fn (Purok $purok) => $purok->name === PurokLocator::NON_NAME)
                ->values();
        }

        return view('barangay_captain.bhw_workers', array_merge([
            'assignedBarangay' => $assignedBarangay,
            'assignedBarangayBoundary' => $assignedBarangay?->boundary_geojson,
        ], compact('bhwWorkers', 'puroks', 'manageablePuroks')));
    })->name('barangay-captain.bhw-workers');

    Route::post('/barangay-captain/bhw-workers/puroks', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['name' => 'No assigned barangay yet. You cannot create a purok.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'boundary_geojson' => ['required', 'json'],
        ]);

        $purokName = trim((string) $data['name']);
        $purokBoundary = json_decode($data['boundary_geojson'], true);

        if (! is_array($purokBoundary) || ($purokBoundary['type'] ?? '') === '') {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Select a valid purok boundary.']);
        }

        if (! empty($assignedBarangay->boundary_geojson) && ! GeoBoundary::geometryWithinBoundary($purokBoundary, $assignedBarangay->boundary_geojson)) {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Purok range must stay within your assigned barangay boundary.']);
        }

        $alreadyExists = Purok::query()
            ->where('barangay_id', $assignedBarangay->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($purokName)])
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'This purok already exists in your barangay.']);
        }

        Purok::query()->create([
            'barangay_id' => $assignedBarangay->id,
            'name' => $purokName,
            'created_by' => Auth::id(),
            'boundary_geojson' => $purokBoundary,
        ]);

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'Purok created successfully.');
    })->name('barangay-captain.puroks.store');

    Route::post('/barangay-captain/puroks/{id}/update', function (Request $request, $id) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['name' => 'No assigned barangay yet. You cannot update a purok.']);
        }

        $purok = Purok::query()
            ->whereKey($id)
            ->where('barangay_id', $assignedBarangay->id)
            ->first();

        if (! $purok) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['name' => 'Purok not found.']);
        }

        if ($purok->name === PurokLocator::NON_NAME) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['name' => 'The system purok "Non" cannot be edited.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'boundary_geojson' => ['required', 'json'],
        ]);

        $purokName = trim((string) $data['name']);
        $purokBoundary = json_decode($data['boundary_geojson'], true);

        if (! is_array($purokBoundary) || ($purokBoundary['type'] ?? '') === '') {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Select a valid purok boundary.']);
        }

        if (! empty($assignedBarangay->boundary_geojson) && ! GeoBoundary::geometryWithinBoundary($purokBoundary, $assignedBarangay->boundary_geojson)) {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Purok range must stay within your assigned barangay boundary.']);
        }

        $alreadyExists = Purok::query()
            ->where('barangay_id', $assignedBarangay->id)
            ->whereKeyNot($purok->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($purokName)])
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'This purok name already exists in your barangay.']);
        }

        $oldName = $purok->name;

        $purok->update([
            'name' => $purokName,
            'boundary_geojson' => $purokBoundary,
        ]);

        if ($oldName !== $purokName) {
            User::query()
                ->where('role', 'BHW')
                ->where('purok_id', $purok->id)
                ->update(['household_no' => $purokName]);
        }

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'Purok updated successfully.');
    })->name('barangay-captain.puroks.update');

    Route::post('/barangay-captain/bhw-workers', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['email' => 'No assigned barangay yet. You cannot create a BHW worker.']);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'worker_role' => ['required', 'in:BHW,BHWHead'],
        ]);

        $name = strstr($data['email'], '@', true) ?: $data['email'];
        $workerRole = $data['worker_role'];
        $statusMessage = $workerRole === User::ROLE_BHW_HEAD
            ? 'BHW Head created successfully.'
            : 'BHW worker created successfully.';

        User::query()->create([
            'name' => $name,
            'full_name' => null,
            'email' => mb_strtolower(trim($data['email'])),
            'role' => $workerRole,
            'barangay' => $assignedBarangay->name,
            'purok_id' => null,
            'managed_by_user_id' => null,
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', $statusMessage);
    })->name('barangay-captain.bhw-workers.store');

    Route::post('/barangay-captain/bhw-workers/assign', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withInput()
                ->withErrors(['worker_id' => 'No assigned barangay yet.']);
        }

        $data = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:users,id'],
            'purok_ids' => ['nullable', 'array'],
            'purok_ids.*' => ['integer', 'exists:puroks,id'],
        ]);

        $worker = User::query()
            ->whereKey($data['worker_id'])
            ->where('role', User::ROLE_BHW)
            ->where('barangay', $assignedBarangay->name)
            ->first();

        if (! $worker) {
            return back()
                ->withInput()
                ->withErrors([
                    'worker_id' => 'Unable to assign worker to this purok.',
                ]);
        }

        $purokIds = collect($data['purok_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($purokIds !== []) {
            $validCount = Purok::query()
                ->whereIn('id', $purokIds)
            ->where('barangay_id', $assignedBarangay->id)
                ->count();

            if ($validCount !== count($purokIds)) {
            return back()
                ->withInput([
                    'worker_id' => $data['worker_id'],
                        'purok_ids' => $purokIds,
                ])
                ->withErrors([
                        'purok_ids' => 'One or more selected purok are invalid.',
                    ]);
            }
        }

        $worker->syncBhwPurokAssignments($purokIds, $assignedBarangay->name);

        $count = count($purokIds);
        $message = $count === 0
            ? 'BHW worker unassigned from all purok successfully.'
            : ($count === 1
                ? 'BHW worker assigned to 1 purok successfully.'
                : "BHW worker assigned to {$count} purok successfully.");

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', $message);
    })->name('barangay-captain.bhw-workers.assign');

    Route::post('/barangay-captain/bhw-workers/kick', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withInput()
                ->withErrors(['worker_id' => 'No assigned barangay yet.']);
        }

        $data = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $worker = User::query()
            ->whereKey($data['worker_id'])
            ->whereIn('role', [User::ROLE_BHW, User::ROLE_BHW_HEAD])
            ->where('barangay', $assignedBarangay->name)
            ->first();

        if (! $worker) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors([
                    'worker_id' => 'Unable to update this account.',
                ]);
        }

        if ($worker->role === User::ROLE_BHW_HEAD) {
            User::query()
                ->where('managed_by_user_id', $worker->id)
                ->update(['managed_by_user_id' => null]);
        }

        $worker->assignedPuroks()->detach();
        $worker->update([
            'role' => 'users',
            'barangay' => null,
            'purok_id' => null,
            'household_no' => null,
            'managed_by_user_id' => null,
        ]);

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'Worker account role removed successfully.');
    })->name('barangay-captain.bhw-workers.kick');

    Route::get('/barangay-captain/bns-workers', function () {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        $bnsWorkers = collect();

        if ($assignedBarangay) {
            $bnsWorkers = User::query()
                ->where('role', User::ROLE_BNS)
                ->where('barangay', $assignedBarangay->name)
                ->orderBy('name')
                ->get();
        }

        return view('barangay_captain.bns_workers', compact('assignedBarangay', 'bnsWorkers'));
    })->name('barangay-captain.bns-workers');

    Route::post('/barangay-captain/bns-workers', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bns-workers')
                ->withErrors(['email' => 'No assigned barangay yet. You cannot create a BNS worker.']);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $name = strstr($data['email'], '@', true) ?: $data['email'];

        User::query()->create([
            'name' => $name,
            'full_name' => null,
            'email' => mb_strtolower(trim($data['email'])),
            'role' => User::ROLE_BNS,
            'barangay' => $assignedBarangay->name,
            'purok_id' => null,
            'managed_by_user_id' => null,
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('barangay-captain.bns-workers')
            ->with('status', 'BNS worker created successfully.');
    })->name('barangay-captain.bns-workers.store');

    Route::post('/barangay-captain/bns-workers/kick', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bns-workers')
                ->withInput()
                ->withErrors(['worker_id' => 'No assigned barangay yet.']);
        }

        $data = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $worker = User::query()
            ->whereKey($data['worker_id'])
            ->where('role', User::ROLE_BNS)
            ->where('barangay', $assignedBarangay->name)
            ->first();

        if (! $worker) {
            return redirect()
                ->route('barangay-captain.bns-workers')
                ->withErrors([
                    'worker_id' => 'Unable to update this account.',
                ]);
        }

        $worker->update([
            'role' => User::ROLE_USERS,
            'barangay' => null,
        ]);

        return redirect()
            ->route('barangay-captain.bns-workers')
            ->with('status', 'BNS worker role removed successfully.');
    })->name('barangay-captain.bns-workers.kick');

    Route::post('/barangay-captain/puroks/{id}/delete', function ($id) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = Barangay::query()
            ->where('captain_user_id', Auth::id())
            ->first();

        if (! $assignedBarangay) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['purok_id' => 'No assigned barangay yet.']);
        }

        $purok = Purok::query()
            ->whereKey($id)
            ->where('barangay_id', $assignedBarangay->id)
            ->first();

        if (! $purok) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['purok_id' => 'Purok not found.']);
        }

        if ($purok->name === PurokLocator::NON_NAME) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors(['purok_id' => 'The system purok "Non" cannot be deleted. It is used for residents outside mapped purok areas.']);
        }

        $affectedUserIds = $purok->bhwWorkers()->pluck('users.id');

        $purok->bhwWorkers()->detach();
        $purok->delete();

        User::query()
            ->whereIn('id', $affectedUserIds)
            ->where('role', 'BHW')
            ->get()
            ->each(fn (User $user) => $user->refreshBhwPrimaryPurok());

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'Purok deleted successfully.');
    })->name('barangay-captain.puroks.delete');

    Route::get('/barangay-captain/settings', function () {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        return view('barangay_captain.settings', [
            'user' => Auth::user(),
        ]);
    })->name('barangay-captain.settings');

    Route::post('/barangay-captain/settings', function (Request $request) {
        if (Auth::user()->role !== 'BrgCaptain') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $data = $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'barangay' => ['nullable', 'string', 'max:255'],
            'household_no' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $profilePicturePath = $user->profile_picture_path;

        if ($request->hasFile('profile_picture')) {
            if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $user->update([
            'name' => $data['full_name'] ?: $user->name,
            'full_name' => $data['full_name'] ?: null,
            'gender' => $data['gender'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'civil_status' => $data['civil_status'] ?: null,
            'contact_number' => $data['contact_number'] ?: null,
            'email' => $data['email'],
            'barangay' => $data['barangay'] ?: null,
            'household_no' => $data['household_no'] ?: null,
            'profile_picture_path' => $profilePicturePath,
        ]);

        return redirect()->route('barangay-captain.settings')->with('status', 'Profile updated successfully.');
    })->name('barangay-captain.settings.update');

    Route::get('/bns', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $payload = BnsChildMonitoring::dashboardPayloadFor(Auth::user());
        $barangayName = $payload['barangayName'] ?? '';
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;
        $nutritionStats = $payload['nutritionStats'];
        $dashboardCharts = $payload['dashboardCharts'];

        return view('bns.main', compact('assignedBarangay', 'barangayName', 'nutritionStats', 'dashboardCharts'));
    })->name('bns.main');

    Route::get('/bns/monitoring', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $payload = BnsChildMonitoring::dashboardPayloadFor(Auth::user());
        $healthSummaries = BnsChildHealth::summariesForChildren($payload['monitoredChildren']);

        return view('bns.monitoring', [
            'barangayName' => $payload['barangayName'],
            'monitoredChildren' => $payload['monitoredChildren'],
            'totalChildren' => $payload['totalChildren'],
            'purokCount' => $payload['purokCount'],
            'householdCount' => $payload['householdCount'],
            'healthSummaries' => $healthSummaries,
        ]);
    })->name('bns.monitoring');

    Route::get('/bns/monitoring/children/{childKey}/health', function (string $childKey) {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(BnsChildHealth::healthPayload(Auth::user(), $childKey));
    })->name('bns.monitoring.health');

    Route::post('/bns/monitoring/children/{childKey}/weight', function (Request $request, string $childKey) {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'weight_kg' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'height_cm' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'muac_cm' => ['nullable', 'numeric', 'min:5', 'max:25'],
            'recorded_on' => ['required', 'date', 'before_or_equal:today'],
        ]);

        BnsChildHealth::storeWeight(Auth::user(), $childKey, $data);

        return response()->json(BnsChildHealth::healthPayload(Auth::user(), $childKey));
    })->name('bns.monitoring.weight.store');

    Route::post('/bns/monitoring/children/{childKey}/deworming', function (Request $request, string $childKey) {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'given_on' => ['required', 'date', 'before_or_equal:today'],
            'medicine' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        BnsChildHealth::storeDeworming(Auth::user(), $childKey, $data);

        return response()->json(BnsChildHealth::healthPayload(Auth::user(), $childKey));
    })->name('bns.monitoring.deworming.store');

    Route::post('/bns/monitoring/children/{childKey}/vitamin-a', function (Request $request, string $childKey) {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'given_on' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        BnsChildHealth::storeVitaminA(Auth::user(), $childKey, $data);

        return response()->json(BnsChildHealth::healthPayload(Auth::user(), $childKey));
    })->name('bns.monitoring.vitamin-a.store');

    Route::post('/bns/monitoring/children/{childKey}/immunizations/{immunization}', function (Request $request, string $childKey, int $immunization) {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:pending,given,missed'],
            'given_date' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        BnsChildHealth::updateImmunization(Auth::user(), $childKey, $immunization, $data);

        return response()->json(BnsChildHealth::healthPayload(Auth::user(), $childKey));
    })->name('bns.monitoring.immunization.update');

    Route::get('/bns/vitamin-distribution', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $barangayName = BnsChildMonitoring::barangayNameFor(Auth::user());
        $records = BnsHealthRecordList::vitaminDistributionRows(Auth::user());

        return view('bns.vitamin-distribution', compact('barangayName', 'records'));
    })->name('bns.vitamin-distribution');

    Route::get('/bns/vitamin-distribution/export', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $rows = BnsHealthRecordList::vitaminDistributionRows(Auth::user());

        return BnsHealthRecordList::csvDownload(
            'vitamin-a-distribution-'.now()->format('Y-m-d').'.csv',
            ['Child Name', 'Current Age', 'Gender', 'Purok', 'Family Number', 'Date Given', 'Age When Given', 'Notes'],
            ['child_name', 'age_label', 'gender', 'purok', 'family_number', 'given_on_label', 'age_in_months_label', 'notes'],
            $rows
        );
    })->name('bns.vitamin-distribution.export');

    Route::get('/bns/deworming', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $barangayName = BnsChildMonitoring::barangayNameFor(Auth::user());
        $records = BnsHealthRecordList::dewormingRows(Auth::user());

        return view('bns.deworming', compact('barangayName', 'records'));
    })->name('bns.deworming');

    Route::get('/bns/deworming/export', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $rows = BnsHealthRecordList::dewormingRows(Auth::user());

        return BnsHealthRecordList::csvDownload(
            'deworming-records-'.now()->format('Y-m-d').'.csv',
            ['Child Name', 'Age', 'Gender', 'Purok', 'Family Number', 'Date Given', 'Medicine', 'Notes'],
            ['child_name', 'age_label', 'gender', 'purok', 'family_number', 'given_on_label', 'medicine', 'notes'],
            $rows
        );
    })->name('bns.deworming.export');

    Route::get('/bns/reports', function () {
        if (Auth::user()->role !== User::ROLE_BNS) {
            return redirect()->route('dashboard');
        }

        $period = request('period', 'this_month');
        [$fromDate, $toDate, $periodKey] = BnsReportBuilder::resolvePeriod(
            $period,
            request('from_date'),
            request('to_date')
        );
        $report = BnsReportBuilder::build(Auth::user(), $fromDate, $toDate, $periodKey);

        return view('bns.reports', [
            'report' => $report,
            'activePeriod' => $periodKey,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    })->name('bns.reports');

    Route::get('/bns/profile', function () {
        if (Auth::user()->role !== 'BNS') {
            return redirect()->route('dashboard');
        }

        return view('bns.profile', [
            'user' => Auth::user(),
        ]);
    })->name('bns.profile');

    Route::post('/bns/profile/avatar', function (Request $request) {
        if (Auth::user()->role !== 'BNS') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user = User::query()->findOrFail(Auth::id());

        $validator = Validator::make($request->all(), [
            'profile_picture' => ['required', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $profilePicturePath = $user->profile_picture_path;

        if ($request->hasFile('profile_picture')) {
            if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->update(['profile_picture_path' => $profilePicturePath]);
        }

        $url = route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]);

        return response()->json(['status' => 'ok', 'url' => $url]);
    })->name('bns.profile.avatar');

    Route::get('/bns/settings', function () {
        if (Auth::user()->role !== 'BNS') {
            return redirect()->route('dashboard');
        }

        return view('bns.settings', [
            'user' => Auth::user(),
        ]);
    })->name('bns.settings');

    Route::post('/bns/settings', function (Request $request) {
        if (Auth::user()->role !== 'BNS') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $data['full_name'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
        ]);

        return redirect()->route('bns.settings')->with('status', 'Profile updated successfully.');
    })->name('bns.settings.update');

    Route::post('/bns/settings/password', function (Request $request) {
        if (Auth::user()->role !== 'BNS') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('bns.settings')
                ->withErrors($validator, 'password')
                ->with('open_password_modal', true);
        }

        $user->update([
            'password' => $request->input('password'),
        ]);

        return redirect()->route('bns.settings')
            ->with('status', 'Password updated successfully.');
    })->name('bns.settings.password');

    $resolveBhwHeadBarangay = function (User $head): ?Barangay {
        $barangayName = trim((string) ($head->barangay ?? ''));

        if ($barangayName === '') {
            return null;
        }

        return Barangay::query()->where('name', $barangayName)->first();
    };

    Route::get('/bhw-head', function () use ($buildCaptainDashboardPayload, $resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $head = Auth::user();
        $assignedBarangay = $resolveBhwHeadBarangay($head);
        $scopedResidents = $assignedBarangay
            ? BhwHeadScope::apply(
                ResidentRequest::query()->where('status', 'accepted'),
                $head
            )->with('user')->get()
            : null;

        $dashboardPayload = $buildCaptainDashboardPayload($assignedBarangay, $scopedResidents);
        $managedBhwCount = BhwHeadScope::managedBhwWorkers($head)->count();

        return view('bhw_head.main', array_merge([
            'assignedBarangay' => $assignedBarangay,
            'managedBhwCount' => $managedBhwCount,
        ], $dashboardPayload));
    })->name('bhw-head.main');

    Route::get('/bhw-head/bhw-workers', function () use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $head = Auth::user();
        $assignedBarangay = $resolveBhwHeadBarangay($head);
        $bhwWorkers = BhwHeadScope::managedBhwWorkers($head);
        $puroks = collect();
        $manageablePuroks = collect();

        if ($assignedBarangay) {
            PurokLocator::ensureNonPurok($assignedBarangay);

            $puroks = Purok::query()
                ->where('barangay_id', $assignedBarangay->id)
                ->orderBy('name')
                ->get();

            $manageablePuroks = $puroks
                ->reject(fn (Purok $purok) => $purok->name === PurokLocator::NON_NAME)
                ->values();
        }

        return view('bhw_head.bhw_workers', array_merge([
            'assignedBarangay' => $assignedBarangay,
            'assignedBarangayBoundary' => $assignedBarangay?->boundary_geojson,
        ], compact('bhwWorkers', 'puroks', 'manageablePuroks')));
    })->name('bhw-head.bhw-workers');

    Route::post('/bhw-head/bhw-workers', function (Request $request) use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $head = Auth::user();
        $assignedBarangay = $resolveBhwHeadBarangay($head);

        if (! $assignedBarangay) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['email' => 'No assigned barangay yet. You cannot create a BHW worker.']);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $name = strstr($data['email'], '@', true) ?: $data['email'];

        User::query()->create([
            'name' => $name,
            'full_name' => null,
            'email' => mb_strtolower(trim($data['email'])),
            'role' => User::ROLE_BHW,
            'barangay' => $assignedBarangay->name,
            'purok_id' => null,
            'managed_by_user_id' => $head->id,
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('bhw-head.bhw-workers')
            ->with('status', 'BHW worker created successfully. Assign their purok next.');
    })->name('bhw-head.bhw-workers.store');

    Route::post('/bhw-head/bhw-workers/assign', function (Request $request) use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $head = Auth::user();
        $assignedBarangay = $resolveBhwHeadBarangay($head);

        if (! $assignedBarangay) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withInput()
                ->withErrors(['worker_id' => 'No assigned barangay yet.']);
        }

        $data = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:users,id'],
            'purok_ids' => ['nullable', 'array'],
            'purok_ids.*' => ['integer', 'exists:puroks,id'],
        ]);

        $worker = User::query()->whereKey($data['worker_id'])->first();

        if (! $worker || ! BhwHeadScope::canManageWorker($head, $worker)) {
            return back()
                ->withInput()
                ->withErrors(['worker_id' => 'Unable to assign this worker.']);
        }

        $purokIds = collect($data['purok_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($purokIds !== []) {
            $validCount = Purok::query()
                ->whereIn('id', $purokIds)
                ->where('barangay_id', $assignedBarangay->id)
                ->count();

            if ($validCount !== count($purokIds)) {
                return back()
                    ->withInput([
                        'worker_id' => $data['worker_id'],
                        'purok_ids' => $purokIds,
                    ])
                    ->withErrors(['purok_ids' => 'One or more selected purok are invalid.']);
            }
        }

        $worker->syncBhwPurokAssignments($purokIds, $assignedBarangay->name);

        $count = count($purokIds);
        $message = $count === 0
            ? 'BHW worker unassigned from all purok successfully.'
            : ($count === 1
                ? 'BHW worker assigned to 1 purok successfully.'
                : "BHW worker assigned to {$count} purok successfully.");

        return redirect()
            ->route('bhw-head.bhw-workers')
            ->with('status', $message);
    })->name('bhw-head.bhw-workers.assign');

    Route::post('/bhw-head/bhw-workers/kick', function (Request $request) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $head = Auth::user();

        $data = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $worker = User::query()->whereKey($data['worker_id'])->first();

        if (! $worker || ! BhwHeadScope::canManageWorker($head, $worker)) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['worker_id' => 'Unable to update this account.']);
        }

        $worker->assignedPuroks()->detach();
        $worker->update([
            'role' => User::ROLE_USERS,
            'barangay' => null,
            'purok_id' => null,
            'household_no' => null,
            'managed_by_user_id' => null,
        ]);

        return redirect()
            ->route('bhw-head.bhw-workers')
            ->with('status', 'BHW account role removed successfully.');
    })->name('bhw-head.bhw-workers.kick');

    Route::post('/bhw-head/bhw-workers/puroks', function (Request $request) use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = $resolveBhwHeadBarangay(Auth::user());

        if (! $assignedBarangay) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['name' => 'No assigned barangay yet. You cannot create a purok.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'boundary_geojson' => ['required', 'json'],
        ]);

        $purokName = trim((string) $data['name']);
        $purokBoundary = json_decode($data['boundary_geojson'], true);

        if (! is_array($purokBoundary) || ($purokBoundary['type'] ?? '') === '') {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Select a valid purok boundary.']);
        }

        if (! empty($assignedBarangay->boundary_geojson) && ! GeoBoundary::geometryWithinBoundary($purokBoundary, $assignedBarangay->boundary_geojson)) {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Purok range must stay within your assigned barangay boundary.']);
        }

        $alreadyExists = Purok::query()
            ->where('barangay_id', $assignedBarangay->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($purokName)])
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'This purok already exists in your barangay.']);
        }

        Purok::query()->create([
            'barangay_id' => $assignedBarangay->id,
            'name' => $purokName,
            'created_by' => Auth::id(),
            'boundary_geojson' => $purokBoundary,
        ]);

        return redirect()
            ->route('bhw-head.bhw-workers')
            ->with('status', 'Purok created successfully.');
    })->name('bhw-head.puroks.store');

    Route::post('/bhw-head/puroks/{id}/update', function (Request $request, $id) use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = $resolveBhwHeadBarangay(Auth::user());

        if (! $assignedBarangay) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['name' => 'No assigned barangay yet. You cannot update a purok.']);
        }

        $purok = Purok::query()
            ->whereKey($id)
            ->where('barangay_id', $assignedBarangay->id)
            ->first();

        if (! $purok) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['name' => 'Purok not found.']);
        }

        if ($purok->name === PurokLocator::NON_NAME) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['name' => 'The system purok "Non" cannot be edited.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'boundary_geojson' => ['required', 'json'],
        ]);

        $purokName = trim((string) $data['name']);
        $purokBoundary = json_decode($data['boundary_geojson'], true);

        if (! is_array($purokBoundary) || ($purokBoundary['type'] ?? '') === '') {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Select a valid purok boundary.']);
        }

        if (! empty($assignedBarangay->boundary_geojson) && ! GeoBoundary::geometryWithinBoundary($purokBoundary, $assignedBarangay->boundary_geojson)) {
            return back()
                ->withInput()
                ->withErrors(['boundary_geojson' => 'Purok range must stay within your assigned barangay boundary.']);
        }

        $alreadyExists = Purok::query()
            ->where('barangay_id', $assignedBarangay->id)
            ->whereKeyNot($purok->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($purokName)])
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'This purok name already exists in your barangay.']);
        }

        $oldName = $purok->name;

        $purok->update([
            'name' => $purokName,
            'boundary_geojson' => $purokBoundary,
        ]);

        if ($oldName !== $purokName) {
            User::query()
                ->where('role', User::ROLE_BHW)
                ->where('purok_id', $purok->id)
                ->update(['household_no' => $purokName]);
        }

        return redirect()
            ->route('bhw-head.bhw-workers')
            ->with('status', 'Purok updated successfully.');
    })->name('bhw-head.puroks.update');

    Route::post('/bhw-head/puroks/{id}/delete', function ($id) use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $assignedBarangay = $resolveBhwHeadBarangay(Auth::user());

        if (! $assignedBarangay) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['purok_id' => 'No assigned barangay yet.']);
        }

        $purok = Purok::query()
            ->whereKey($id)
            ->where('barangay_id', $assignedBarangay->id)
            ->first();

        if (! $purok) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['purok_id' => 'Purok not found.']);
        }

        if ($purok->name === PurokLocator::NON_NAME) {
            return redirect()
                ->route('bhw-head.bhw-workers')
                ->withErrors(['purok_id' => 'The system purok "Non" cannot be deleted.']);
        }

        $affectedUserIds = $purok->bhwWorkers()->pluck('users.id');

        $purok->bhwWorkers()->detach();
        $purok->delete();

        User::query()
            ->whereIn('id', $affectedUserIds)
            ->where('role', User::ROLE_BHW)
            ->get()
            ->each(fn (User $user) => $user->refreshBhwPrimaryPurok());

        return redirect()
            ->route('bhw-head.bhw-workers')
            ->with('status', 'Purok deleted successfully.');
    })->name('bhw-head.puroks.delete');

    Route::get('/bhw-head/reports', function () use ($resolveBhwHeadBarangay) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $head = Auth::user();
        $assignedBarangay = $resolveBhwHeadBarangay($head);
        $fromDate = request('from_date');
        $toDate = request('to_date');

        $residentsQuery = ResidentRequest::query()->where('status', 'accepted');

        if ($assignedBarangay) {
            $residentsQuery = BhwHeadScope::apply($residentsQuery, $head);
        } else {
            $residentsQuery->whereRaw('0 = 1');
        }

        if ($fromDate) {
            $residentsQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $residentsQuery->whereDate('created_at', '<=', $toDate);
        }

        $residents = $residentsQuery->latest('reviewed_at')->get();

        $totalResidents = $residents->count();
        $totalHouseholds = $residents
            ->pluck('address')
            ->filter(fn ($address) => filled($address))
            ->unique()
            ->count();
        $seniorCitizens = $residents->filter(fn ($resident) => (int) ($resident->age ?? 0) >= 60)->count();
        $childrenUnderFive = $residents->filter(fn ($resident) => (int) ($resident->age ?? 0) <= 5)->count();

        $reportData = [
            'monthly_barangay_health_report' => [
                'total_residents' => $totalResidents,
                'total_households' => $totalHouseholds,
                'senior_citizens' => $seniorCitizens,
            ],
            'malnutrition_summary' => [
                'identified_cases' => 0,
                'under_monitoring' => $childrenUnderFive,
            ],
            'vaccination_coverage_report' => [
                'vaccinated' => 0,
                'unvaccinated' => $totalResidents,
            ],
            'maternal_summary' => [
                'pregnant_women' => 0,
                'prenatal_monitoring' => 0,
            ],
            'illness_case_summary' => [
                'active_cases' => 0,
                'resolved_cases' => 0,
            ],
            'health_program_status_report' => [
                ['program' => 'Nutrition Program', 'status' => 'Ongoing'],
                ['program' => 'Vaccination Drive', 'status' => 'Planned'],
                ['program' => 'Community Health Education', 'status' => 'Ongoing'],
            ],
        ];

        return view('bhw_head.reports', compact('assignedBarangay', 'residents', 'reportData', 'fromDate', 'toDate'));
    })->name('bhw-head.reports');

    Route::get('/bhw-head/settings', function () {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        return view('bhw_head.settings', [
            'user' => Auth::user(),
        ]);
    })->name('bhw-head.settings');

    Route::post('/bhw-head/settings', function (Request $request) {
        if (Auth::user()->role !== User::ROLE_BHW_HEAD) {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $data = $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $profilePicturePath = $user->profile_picture_path;

        if ($request->hasFile('profile_picture')) {
            if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $user->update([
            'name' => $data['full_name'] ?: $user->name,
            'full_name' => $data['full_name'] ?: null,
            'gender' => $data['gender'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'civil_status' => $data['civil_status'] ?: null,
            'contact_number' => $data['contact_number'] ?: null,
            'email' => $data['email'],
            'profile_picture_path' => $profilePicturePath,
        ]);

        return redirect()->route('bhw-head.settings')->with('status', 'Profile updated successfully.');
    })->name('bhw-head.settings.update');

    Route::get('/bhw', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $assignedResidents = ResidentRequest::query()
            ->forBhw(Auth::user())
            ->where('status', 'accepted')
            ->latest('reviewed_at')
            ->get();

        $totalResidentsAssigned = $assignedResidents->count();
        $seniorCitizensMonitored = $assignedResidents->filter(fn ($resident) => (int) ($resident->age ?? 0) >= 60)->count();

        $bhwStats = [
            'total_residents_assigned' => $totalResidentsAssigned,
            'active_illness_cases' => 0,
            'recovered_cases' => 0,
            'vaccinated_residents' => 0,
            'pending_vaccinations' => $totalResidentsAssigned,
            'pregnant_women_monitored' => 0,
            'senior_citizens_monitored' => $seniorCitizensMonitored,
        ];

        $monthlyIllnessTrend = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'values' => [0, 0, 0, 0, 0, 0],
        ];

        $vaccinationCoverage = [
            'vaccinated' => 0,
            'pending' => $totalResidentsAssigned,
        ];

        $commonIllnesses = [
            'labels' => ['Flu', 'Cough/Cold', 'Diarrhea', 'Hypertension'],
            'values' => [0, 0, 0, 0],
        ];

        return view('bhw.main', compact('bhwStats', 'monthlyIllnessTrend', 'vaccinationCoverage', 'commonIllnesses'));
    })->name('bhw.main');

    Route::get('/bhw/residents', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $acceptedResidents = ResidentRequest::query()
            ->forBhw(Auth::user())
            ->where('status', 'accepted')
            ->with(['user.purok', 'purok'])
            ->withMax('consultations as last_consultation_at', 'scheduled_at')
            ->latest('reviewed_at')
            ->get();

        $residentUserIds = $acceptedResidents->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $unreadByResident = ResidentBhwChat::unreadCountsByResidentForBhw(Auth::user(), $residentUserIds);

        return view('bhw.residents', compact('acceptedResidents', 'unreadByResident'));
    })->name('bhw.residents');

    Route::get('/bhw/requests', function (Request $request) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $search = trim((string) $request->query('search', ''));

        $pendingRequests = ResidentRequest::query()
            ->forBhw(Auth::user())
            ->where('status', 'pending')
            ->with(['user', 'purok'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        $bhw = Auth::user();
        $residentUserIds = $pendingRequests->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $unreadByResident = ResidentBhwChat::unreadCountsByResidentForBhw($bhw, $residentUserIds);

        return view('bhw.requests', [
            'pendingRequests' => $pendingRequests,
            'search' => $search,
            'unreadByResident' => $unreadByResident,
        ]);
    })->name('bhw.requests');

    Route::get('/bhw/requests/{residentRequest}/chat', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if ($residentRequest->status !== 'pending') {
            return redirect()->route('bhw.requests')->with('status', 'Chat is only available for pending verification requests.');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.requests')->with('status', 'This request is outside your assigned purok.');
        }

        $residentRequest->load(['user', 'purok']);
        $bhw = Auth::user();
        $resident = $residentRequest->user;

        $unreadBhwMessageIds = [];
        $unreadBhwChatCount = 0;
        if ($resident) {
            $unreadBhwMessageIds = ResidentBhwChat::unreadMessageIdsForBhw($bhw, $resident);
            $unreadBhwChatCount = count($unreadBhwMessageIds);
            ResidentBhwChat::markReadForBhw($bhw, $resident);
        }

        $messages = $resident
            ? ResidentBhwChat::threadMessages($resident, $bhw, $bhw->id)
            : collect();

        return view('bhw.request_chat', [
            'residentRequest' => $residentRequest,
            'messages' => $messages,
            'unreadBhwChatCount' => $unreadBhwChatCount,
            'unreadBhwMessageIds' => $unreadBhwMessageIds,
        ]);
    })->name('bhw.requests.chat');

    Route::get('/bhw/residents/{residentRequest}/profile', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $residentRequest->load([
            'user.purok',
            'purok',
            'transactions' => fn ($query) => $query->latest(),
            'monitoringCases' => fn ($query) => $query->with(['visits' => fn ($visitQuery) => $visitQuery->latest('visit_date')])->latest(),
            'consultations' => fn ($query) => $query->orderByDesc('scheduled_at'),
        ]);

        $residentChatUnreadCount = $residentRequest->user
            ? ResidentBhwChat::unreadCountForBhw(Auth::user(), $residentRequest->user)
            : 0;

        return view('bhw.profile', [
            'resident' => $residentRequest,
            'residentChatUnreadCount' => $residentChatUnreadCount,
        ]);
    })->name('bhw.residents.profile');

    Route::get('/bhw/residents/{residentRequest}/chat', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Chat is only available for accepted residents.');
        }

        $residentRequest->load(['user', 'purok']);

        $bhw = Auth::user();
        $resident = $residentRequest->user;
        $unreadBhwMessageIds = [];
        $messages = collect();

        if ($resident) {
            $unreadBhwMessageIds = ResidentBhwChat::unreadMessageIdsForBhw($bhw, $resident);
            ResidentBhwChat::markReadForBhw($bhw, $resident);
            $messages = ResidentBhwChat::threadMessages($resident, $bhw, $bhw->id);
        }

        return view('bhw.resident_chat', [
            'residentRequest' => $residentRequest,
            'messages' => $messages,
            'unreadBhwMessageIds' => $unreadBhwMessageIds,
        ]);
    })->name('bhw.residents.chat');

    Route::post('/bhw/residents/{residentRequest}/chat/message', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user()) || $residentRequest->status !== 'accepted' || ! $residentRequest->user) {
            return response()->json(['message' => 'Unable to message this resident.'], 403);
        }

        $bhw = Auth::user();
        $message = ResidentBhwChat::storeMessage($request, $residentRequest->user, $bhw, $bhw);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, $bhw->id),
        ]);
    })->name('bhw.residents.chat.message');

    Route::patch('/bhw/residents/{residentRequest}/chat/message/{residentBhwMessage}', function (Request $request, ResidentRequest $residentRequest, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($residentRequest->status !== 'accepted'
            || ! $residentRequest->isAccessibleByBhw(Auth::user())
            || (int) $residentBhwMessage->resident_user_id !== (int) $residentRequest->user_id
            || ! ResidentBhwChat::canEditMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json([
                'message' => ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage)
                    ? 'You can only edit messages within '.ResidentBhwChat::MESSAGE_EDIT_MINUTES.' minutes of sending.'
                    : 'Unable to edit this message.',
            ], ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage) ? 422 : 403);
        }

        $data = $request->validate(['message' => ['required', 'string', 'max:500']]);
        $message = ResidentBhwChat::updateMessage($residentBhwMessage, $data['message']);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, Auth::id()),
        ]);
    })->name('bhw.residents.chat.message.update');

    Route::delete('/bhw/residents/{residentRequest}/chat/message/{residentBhwMessage}', function (Request $request, ResidentRequest $residentRequest, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($residentRequest->status !== 'accepted'
            || ! $residentRequest->isAccessibleByBhw(Auth::user())
            || (int) $residentBhwMessage->resident_user_id !== (int) $residentRequest->user_id
            || ! ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json(['message' => 'Unable to remove this message.'], 403);
        }

        $data = $request->validate(['scope' => ['required', 'in:everyone,you']]);
        $result = ResidentBhwChat::unsendMessage($residentBhwMessage, $data['scope'], Auth::id());

        return response()->json(['ok' => true, ...$result]);
    })->name('bhw.residents.chat.message.destroy');

    Route::post('/bhw/residents/{residentRequest}/chat/message/{residentBhwMessage}/reaction', function (Request $request, ResidentRequest $residentRequest, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($residentRequest->status !== 'accepted'
            || ! $residentRequest->isAccessibleByBhw(Auth::user())
            || (int) $residentBhwMessage->resident_user_id !== (int) $residentRequest->user_id
            || ! ResidentBhwChat::canAccessMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json(['message' => 'Unable to react to this message.'], 403);
        }

        $data = $request->validate(['emoji' => ['required', 'string', 'max:32']]);
        $message = ResidentBhwChat::toggleReaction($residentBhwMessage, Auth::user(), $data['emoji']);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, Auth::id()),
        ]);
    })->name('bhw.residents.chat.message.reaction');

    Route::post('/bhw/residents/{residentRequest}/medical-history', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $medicationRules = [];

        foreach (array_keys(ResidentTransaction::MEDICATIONS) as $field) {
            $medicationRules[$field] = ['nullable', 'integer', 'min:0', 'max:999'];
        }

        $data = $request->validate(array_merge([
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'transaction_date' => ['required', 'date'],
            'gender' => ['required', 'in:M,F'],
            'philhealth_number' => ['required', 'string', 'max:50'],
        ], $medicationRules));

        $transaction = ResidentTransaction::query()->create([
            'resident_request_id' => $residentRequest->id,
            'user_id' => $residentRequest->user_id,
            'recorded_by' => Auth::id(),
            'age' => $data['age'],
            'transaction_date' => $data['transaction_date'],
            'gender' => $data['gender'],
            'philhealth_number' => trim((string) $data['philhealth_number']),
            'amlodipine_pcs' => $data['amlodipine_pcs'] ?? null,
            'losartan_pcs' => $data['losartan_pcs'] ?? null,
            'metoprolol_pcs' => $data['metoprolol_pcs'] ?? null,
            'simvastatin_pcs' => $data['simvastatin_pcs'] ?? null,
            'metformin_pcs' => $data['metformin_pcs'] ?? null,
            'gliclazide_pcs' => $data['gliclazide_pcs'] ?? null,
        ]);

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Transaction Updated',
            'message' => $transaction->notificationSummary(),
        ]);

        return redirect()->route('bhw.residents.profile', ['residentRequest' => $residentRequest, 'tab' => 'medical-history'])
            ->with('status', 'Transaction saved and sent to resident notifications.');
    })->name('bhw.residents.medical-history.store');

    Route::post('/bhw/residents/{residentRequest}/monitoring-cases', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $data = $request->validate([
            'member_type' => ['required', 'in:pregnant,senior'],
            'member_name' => ['required', 'string', 'max:255'],
            'member_source' => ['nullable', 'string', 'max:50'],
            'member_index' => ['nullable', 'integer', 'min:0'],
            'issue_notes' => ['nullable', 'string', 'max:1000'],
            'first_period_date' => ['nullable', 'date', 'required_if:member_type,pregnant'],
            'last_period_date' => ['nullable', 'date', 'after_or_equal:first_period_date'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'bp_systolic' => ['nullable', 'integer', 'min:50', 'max:300'],
            'bp_diastolic' => ['nullable', 'integer', 'min:30', 'max:200'],
        ]);

        $monitoringCase = HouseholdMonitoringCase::query()->create([
            'resident_request_id' => $residentRequest->id,
            'user_id' => $residentRequest->user_id,
            'recorded_by' => Auth::id(),
            'member_type' => $data['member_type'],
            'member_name' => trim((string) $data['member_name']),
            'member_source' => filled($data['member_source'] ?? null) ? trim((string) $data['member_source']) : null,
            'member_index' => $data['member_index'] ?? null,
            'issue_notes' => filled($data['issue_notes'] ?? null) ? trim((string) $data['issue_notes']) : null,
            'first_period_date' => $data['first_period_date'] ?? null,
            'last_period_date' => $data['last_period_date'] ?? null,
            'is_active' => true,
        ]);

        $weightKg = isset($data['weight_kg']) && $data['weight_kg'] !== '' ? (float) $data['weight_kg'] : null;
        $heightCm = isset($data['height_cm']) && $data['height_cm'] !== '' ? (float) $data['height_cm'] : null;
        $bmiValue = null;

        if ($weightKg !== null && $heightCm !== null && $heightCm > 0) {
            $bmiValue = round($weightKg / pow($heightCm / 100, 2), 1);
        }

        $bloodPressure = null;
        $systolic = $data['bp_systolic'] ?? null;
        $diastolic = $data['bp_diastolic'] ?? null;

        if ($systolic !== null && $diastolic !== null) {
            $bloodPressure = $systolic.'/'.$diastolic;
        }

        if ($weightKg !== null || $heightCm !== null || $bloodPressure !== null) {
            HouseholdMonitoringVisit::query()->create([
                'household_monitoring_case_id' => $monitoringCase->id,
                'recorded_by' => Auth::id(),
                'visit_date' => today(),
                'weight_kg' => $weightKg,
                'height_cm' => $heightCm,
                'bmi_value' => $bmiValue,
                'blood_pressure' => $bloodPressure,
                'notes' => 'Initial monitoring baseline',
            ]);
        }

        return redirect()->route('bhw.residents.profile', ['residentRequest' => $residentRequest, 'tab' => 'maintenance'])
            ->with('status', 'Household monitoring case added successfully.');
    })->name('bhw.residents.monitoring-cases.store');

    Route::post('/bhw/monitoring-cases/{monitoringCase}/visits', function (Request $request, HouseholdMonitoringCase $monitoringCase) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $residentRequest = $monitoringCase->residentRequest;

        if (! $residentRequest || ! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $rules = [
            'visit_date' => ['required', 'date'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($monitoringCase->isPregnant()) {
            $rules['muac_cm'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['anti_tetanus'] = ['nullable', 'string', 'max:100'];
        }

        if ($monitoringCase->isSenior()) {
            $rules['bp_systolic'] = ['nullable', 'integer', 'min:50', 'max:300'];
            $rules['bp_diastolic'] = ['nullable', 'integer', 'min:30', 'max:200'];
            $rules['maintenance_medicines'] = ['nullable', 'string', 'max:1000'];
        }

        $data = $request->validate($rules);

        $weightKg = isset($data['weight_kg']) && $data['weight_kg'] !== '' ? (float) $data['weight_kg'] : null;
        $heightCm = isset($data['height_cm']) && $data['height_cm'] !== '' ? (float) $data['height_cm'] : null;
        $bmiValue = null;

        if ($weightKg !== null && $heightCm !== null && $heightCm > 0) {
            $bmiValue = round($weightKg / pow($heightCm / 100, 2), 1);
        }

        $bloodPressure = null;

        if ($monitoringCase->isSenior()) {
            $systolic = $data['bp_systolic'] ?? null;
            $diastolic = $data['bp_diastolic'] ?? null;

            if ($systolic !== null && $diastolic !== null) {
                $bloodPressure = $systolic.'/'.$diastolic;
            }
        }

        $visit = HouseholdMonitoringVisit::query()->create([
            'household_monitoring_case_id' => $monitoringCase->id,
            'recorded_by' => Auth::id(),
            'visit_date' => $data['visit_date'],
            'weight_kg' => $weightKg,
            'height_cm' => $heightCm,
            'bmi_value' => $bmiValue,
            'muac_cm' => $monitoringCase->isPregnant() ? ($data['muac_cm'] ?? null) : null,
            'anti_tetanus' => $monitoringCase->isPregnant() ? ($data['anti_tetanus'] ?? null) : null,
            'blood_pressure' => $bloodPressure,
            'maintenance_medicines' => $monitoringCase->isSenior() ? ($data['maintenance_medicines'] ?? null) : null,
            'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
        ]);

        return redirect()->route('bhw.residents.profile', ['residentRequest' => $residentRequest, 'tab' => 'maintenance'])
            ->with('status', 'Monitoring visit saved successfully.');
    })->name('bhw.monitoring-cases.visits.store');

    Route::post('/bhw/medical-records/{medicalRecord}/update', function (Request $request, MedicalRecord $medicalRecord) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $residentRequest = $medicalRecord->residentRequest;

        if (! $residentRequest || $residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        $data = $request->validate([
            'record_type' => ['required', 'string', 'max:100'],
            'record_value' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $medicalRecord->update([
            'record_type' => trim((string) $data['record_type']),
            'record_value' => trim((string) $data['record_value']),
            'note' => filled($data['note'] ?? null) ? trim((string) $data['note']) : null,
            'recorded_by' => Auth::id(),
        ]);

        return redirect()->route('bhw.residents.profile', ['residentRequest' => $residentRequest, 'tab' => 'medical-history'])
            ->with('status', 'Medical record updated successfully.');
    })->name('bhw.medical-records.update');

    Route::post('/bhw/residents/{residentRequest}/consultations', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.residents')->with('status', 'This resident is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $data = $request->validate([
            'consultation_date' => ['required', 'date'],
            'consultation_time' => ['required', 'date_format:H:i'],
            'consultation_checkup' => ['required', 'string', 'max:255'],
            'consultation_note' => ['nullable', 'string', 'max:500'],
        ]);

        $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $data['consultation_date'].' '.$data['consultation_time']);
        $medicalCheckup = trim((string) $data['consultation_checkup']);

        Consultation::query()->create([
            'resident_request_id' => $residentRequest->id,
            'user_id' => $residentRequest->user_id,
            'scheduled_at' => $scheduledAt,
            'medical_checkup' => $medicalCheckup,
            'note' => filled($data['consultation_note'] ?? null) ? trim((string) $data['consultation_note']) : null,
            'set_by' => Auth::id(),
        ]);

        $message = 'Appointment: '.$scheduledAt->format('M d, Y h:i A').'. Medical checkup: '.$medicalCheckup;

        if (filled($data['consultation_note'] ?? null)) {
            $message .= '. Note: '.trim((string) $data['consultation_note']);
        }

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Consultation Schedule Set',
            'message' => $message,
        ]);

        return redirect()->route('bhw.residents.profile', ['residentRequest' => $residentRequest, 'tab' => 'consultations'])
            ->with('status', 'Consultation schedule sent to resident account notifications.');
    })->name('bhw.residents.consultations.store');

    Route::post('/bhw/requests/{residentRequest}/accept', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.requests')->with('status', 'This request is outside your assigned purok.');
        }

        if ($residentRequest->status !== 'pending') {
            return redirect()->route('bhw.requests')->with('status', 'Only pending requests can be accepted.');
        }

        $validator = Validator::make($request->all(), [
            'family_number' => ['required', 'string', 'max:50'],
        ], [
            'family_number.required' => 'Family number is required before accepting a request.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('bhw.requests')
                ->withErrors($validator)
                ->withInput()
                ->with('reopen_accept_request_id', $residentRequest->id);
        }

        $familyNumber = trim($validator->validated()['family_number']);

        $residentRequest->update([
            'status' => 'accepted',
            'reviewed_at' => now(),
            'family_number' => $familyNumber,
        ]);

        if ($residentRequest->user) {
            $residentRequest->user->update([
                'family_number' => $familyNumber,
            ]);
        }

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Resident Request Approved',
            'message' => 'Your resident verification request has been approved. You may now access your resident portal features.',
        ]);

        return redirect()->route('bhw.requests')->with('status', 'Request accepted and family number assigned.');
    })->name('bhw.requests.accept');

    Route::post('/bhw/requests/{residentRequest}/reject', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user())) {
            return redirect()->route('bhw.requests')->with('status', 'This request is outside your assigned purok.');
        }

        $residentRequest->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
        ]);

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Resident Request Rejected',
            'message' => 'Your resident verification request was rejected. Please review your details and submit a new request.',
        ]);

        return redirect()->route('bhw.requests')->with('status', 'Request rejected.');
    })->name('bhw.requests.reject');

    Route::post('/bhw/requests/{residentRequest}/message', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if ($residentRequest->status !== 'pending') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chat is only available for pending requests.'], 422);
            }

            return redirect()->route('bhw.requests')->with('status', 'Chat is only available for pending verification requests.');
        }

        if (! $residentRequest->isAccessibleByBhw(Auth::user()) || ! $residentRequest->user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This request is outside your assigned purok.'], 403);
            }

            return redirect()->route('bhw.requests')->with('status', 'This request is outside your assigned purok.');
        }

        $bhw = Auth::user();
        $resident = $residentRequest->user;
        $message = ResidentBhwChat::storeMessage($request, $resident, $bhw, $bhw);
        $formatted = ResidentBhwChat::formatMessage($message, $bhw->id);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $formatted]);
        }

        if ($request->boolean('from_chat')) {
            return redirect()->route('bhw.requests.chat', $residentRequest)->with('status', 'Message sent.');
        }

        return redirect()->route('bhw.requests', ['search' => $request->query('search')])->with('status', 'Message sent.');
    })->name('bhw.requests.message');

    Route::patch('/bhw/requests/{residentRequest}/message/{residentBhwMessage}', function (Request $request, ResidentRequest $residentRequest, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($residentRequest->status !== 'pending'
            || ! $residentRequest->isAccessibleByBhw(Auth::user())
            || (int) $residentBhwMessage->resident_user_id !== (int) $residentRequest->user_id
            || ! ResidentBhwChat::canEditMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json([
                'message' => ($residentRequest->status === 'pending'
                    && $residentRequest->isAccessibleByBhw(Auth::user())
                    && (int) $residentBhwMessage->resident_user_id === (int) $residentRequest->user_id
                    && ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage))
                    ? 'You can only edit messages within '.ResidentBhwChat::MESSAGE_EDIT_MINUTES.' minutes of sending.'
                    : 'Unable to edit this message.',
            ], ($residentRequest->status === 'pending'
                && $residentRequest->isAccessibleByBhw(Auth::user())
                && (int) $residentBhwMessage->resident_user_id === (int) $residentRequest->user_id
                && ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage)) ? 422 : 403);
        }

        $data = $request->validate(['message' => ['required', 'string', 'max:500']]);
        $message = ResidentBhwChat::updateMessage($residentBhwMessage, $data['message']);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, Auth::id()),
        ]);
    })->name('bhw.requests.message.update');

    Route::delete('/bhw/requests/{residentRequest}/message/{residentBhwMessage}', function (Request $request, ResidentRequest $residentRequest, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($residentRequest->status !== 'pending'
            || ! $residentRequest->isAccessibleByBhw(Auth::user())
            || (int) $residentBhwMessage->resident_user_id !== (int) $residentRequest->user_id
            || ! ResidentBhwChat::canModifyMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json(['message' => 'Unable to remove this message.'], 403);
        }

        $data = $request->validate(['scope' => ['required', 'in:everyone,you']]);
        $result = ResidentBhwChat::unsendMessage($residentBhwMessage, $data['scope'], Auth::id());

        return response()->json(['ok' => true, ...$result]);
    })->name('bhw.requests.message.destroy');

    Route::post('/bhw/requests/{residentRequest}/message/{residentBhwMessage}/reaction', function (Request $request, ResidentRequest $residentRequest, ResidentBhwMessage $residentBhwMessage) {
        if (Auth::user()->role !== 'BHW') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($residentRequest->status !== 'pending'
            || ! $residentRequest->isAccessibleByBhw(Auth::user())
            || (int) $residentBhwMessage->resident_user_id !== (int) $residentRequest->user_id
            || ! ResidentBhwChat::canAccessMessage(Auth::user(), $residentBhwMessage)) {
            return response()->json(['message' => 'Unable to react to this message.'], 403);
        }

        $data = $request->validate(['emoji' => ['required', 'string', 'max:32']]);
        $message = ResidentBhwChat::toggleReaction($residentBhwMessage, Auth::user(), $data['emoji']);

        return response()->json([
            'ok' => true,
            'message' => ResidentBhwChat::formatMessage($message, Auth::id()),
        ]);
    })->name('bhw.requests.message.reaction');

    Route::get('/chat/bhw-resident-messages/{residentBhwMessage}/attachment', function (ResidentBhwMessage $residentBhwMessage) {
        if (! Auth::check() || ! ResidentBhwChat::canAccessMessage(Auth::user(), $residentBhwMessage)) {
            abort(403);
        }

        if (! $residentBhwMessage->attachment_path || ! Storage::disk('public')->exists($residentBhwMessage->attachment_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($residentBhwMessage->attachment_path));
    })->name('resident-bhw.message.attachment');

    Route::get('/bhw/reports', function (Request $request) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $period = $request->query('period', 'monthly');
        $fromDate = null;
        $toDate = null;

        if ($period === 'weekly') {
            $fromDate = now()->startOfWeek()->toDateString();
            $toDate = now()->toDateString();
        } elseif ($period === 'custom') {
            $validated = $request->validate([
                'from_date' => ['required', 'date'],
                'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            ]);

            $fromDate = $validated['from_date'];
            $toDate = $validated['to_date'];
        } else {
            $period = 'monthly';
            $fromDate = now()->startOfMonth()->toDateString();
            $toDate = now()->toDateString();
        }

        $residentsQuery = ResidentRequest::query()
            ->forBhw(Auth::user())
            ->where('status', 'accepted');

        if ($fromDate) {
            $residentsQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $residentsQuery->whereDate('created_at', '<=', $toDate);
        }

        $residents = $residentsQuery->get();

        $totalResidents = $residents->count();
        $seniorCitizens = $residents->filter(fn ($resident) => (int) ($resident->age ?? 0) >= 60)->count();
        $seniorStable = (int) floor($seniorCitizens * 0.8);

        $maternalCases = $residents->filter(function ($resident) {
            $isFemale = strtolower((string) ($resident->gender ?? '')) === 'female';
            $age = (int) ($resident->age ?? 0);

            return $isFemale && $age >= 15 && $age <= 49;
        })->count();

        $maternalMonitored = (int) floor($maternalCases * 0.7);

        $reportData = [
            'monthly_illness_report' => [
                'active_cases' => 0,
                'recovered_cases' => 0,
                'households_visited' => $residents
                    ->pluck('address')
                    ->filter(fn ($address) => filled($address))
                    ->unique()
                    ->count(),
            ],
            'vaccination_report' => [
                'vaccinated' => 0,
                'pending' => $totalResidents,
                'coverage_rate' => 0,
            ],
            'maternal_health_report' => [
                'pregnant_women_monitored' => $maternalMonitored,
                'prenatal_visits_completed' => $maternalMonitored,
                'high_risk_cases' => max($maternalCases - $maternalMonitored, 0),
            ],
            'senior_citizen_monitoring_report' => [
                'seniors_monitored' => $seniorCitizens,
                'stable_condition' => $seniorStable,
                'needs_follow_up' => max($seniorCitizens - $seniorStable, 0),
            ],
        ];

        return view('bhw.reports', compact('reportData', 'fromDate', 'toDate', 'period'));
    })->name('bhw.reports');

    Route::get('/bhw/schedule', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $scheduleEvents = Consultation::query()
            ->whereHas('residentRequest', fn ($query) => $query->forBhw(Auth::user()))
            ->with('residentRequest')
            ->orderBy('scheduled_at')
            ->get()
            ->map(function (Consultation $consultation) {
                $checkup = (string) ($consultation->medical_checkup ?: 'General Check-up');
                $normalizedCheckup = strtolower($checkup);

                if (str_contains($normalizedCheckup, 'prenatal')) {
                    $type = 'prenatal';
                } elseif (str_contains($normalizedCheckup, 'immun')) {
                    $type = 'immunization';
                } elseif (str_contains($normalizedCheckup, 'follow')) {
                    $type = 'follow-up';
                } else {
                    $type = 'checkup';
                }

                return [
                    'date' => $consultation->scheduled_at?->format('Y-m-d'),
                    'time' => $consultation->scheduled_at?->format('h:i A'),
                    'type' => $type,
                    'label' => $checkup,
                    'resident' => $consultation->residentRequest?->full_name ?: 'Resident',
                    'note' => $consultation->note ?: 'Scheduled consultation.',
                ];
            })
            ->values();

        return view('bhw.schedule', [
            'scheduleEvents' => $scheduleEvents,
        ]);
    })->name('bhw.schedule');

    Route::get('/bhw/settings', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        return view('bhw.settings', [
            'user' => Auth::user(),
        ]);
    })->name('bhw.settings');

    Route::post('/bhw/settings', function (Request $request) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $data = $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'barangay' => ['nullable', 'string', 'max:255'],
            'household_no' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        $profilePicturePath = $user->profile_picture_path;

        if ($request->hasFile('profile_picture')) {
            if ($profilePicturePath && Storage::disk('public')->exists($profilePicturePath)) {
                Storage::disk('public')->delete($profilePicturePath);
            }

            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $user->update([
            'name' => $data['full_name'] ?: $user->name,
            'full_name' => $data['full_name'] ?: null,
            'gender' => $data['gender'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'civil_status' => $data['civil_status'] ?: null,
            'contact_number' => $data['contact_number'] ?: null,
            'email' => $data['email'],
            'barangay' => $data['barangay'] ?: null,
            'household_no' => $data['household_no'] ?: null,
            'profile_picture_path' => $profilePicturePath,
        ]);

        return redirect()->route('bhw.settings')->with('status', 'Profile updated successfully.');
    })->name('bhw.settings.update');
});
