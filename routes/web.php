<?php

use App\Mail\PasswordResetOtpMail;
use App\Models\Barangay;
use App\Models\Consultation;
use App\Models\Event;
use App\Models\InboxMessage;
use App\Models\MedicalRecord;
use App\Models\Purok;
use App\Models\ResidentRequest;
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

        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'You are signed in.');
    })->name('login.perform');

    Route::post('/register', function (Request $request) {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => strstr($data['email'], '@', true) ?: $data['email'],
            'email' => $data['email'],
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

    Route::get('/dashboard', function () {
        $role = Auth::user()->role;

        return match ($role) {
            'LGU' => redirect()->route('lgu.main'),
            'BrgCaptain' => redirect()->route('barangay-captain.main'),
            'BNS' => redirect()->route('bns.main'),
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
        $residentRequest = ResidentRequest::query()
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->first();
        $verificationStatus = $residentRequest->status ?? 'unverified';
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

        // Fetch events for the user's barangay
        $barangayName = $residentRequest->barangay ?? null;
        $barangay = $barangayName ? Barangay::where('name', $barangayName)->first() : null;
        
        $events = $verificationStatus === 'accepted' && $barangay
            ? Event::query()
                ->where('barangay_id', $barangay->id)
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

        return $hasRequest
            ? view('users.main', compact('unreadInboxCount', 'verificationStatus', 'upcomingConsultations', 'nextCheckupLabel', 'lastCheckupLabel', 'medicalSummaryRecords', 'events', 'announcements'))
            : redirect()->route('users.verification');
    })->name('users.main');

    Route::get('/users/events', function () {
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

        $verificationStatus = $residentRequest->status ?? 'unverified';

        $barangayName = $residentRequest->barangay ?? null;
        $barangay = $barangayName ? Barangay::where('name', $barangayName)->first() : null;

        $events = $verificationStatus === 'accepted' && $barangay
            ? tap(Event::query(), function ($q) use ($barangay) {
                $today = today()->toDateString();
                $nowTime = now()->format('H:i:s');

                $q->select('events.*')
                    ->selectSub(function ($subQuery) {
                        $subQuery->from('event_registrations')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('event_registrations.event_id', 'events.id');
                    }, 'registration_count')
                    ->where('barangay_id', $barangay->id)
                    ->whereNull('disabled_at')
                    ->where(function ($sub) use ($today, $nowTime) {
                        $sub->where('event_date', '>', $today)
                            ->orWhere(function ($s2) use ($today, $nowTime) {
                                $s2->where('event_date', $today)
                                   ->where(function ($s3) use ($nowTime) {
                                       $s3->whereNull('end_time')
                                          ->orWhere('end_time', '>=', $nowTime);
                                   });
                            });
                    })
                    ->orderByDesc('event_date')
                    ->orderByDesc('start_time');
            })->get()
            : collect();

        $registeredEventIds = DB::table('event_registrations')
            ->where('user_id', Auth::id())
            ->pluck('event_id')
            ->all();

        return view('users.events', compact('events', 'barangayName', 'unreadInboxCount', 'registeredEventIds', 'verificationStatus'));
    })->name('users.events');

    Route::post('/users/events/{event}/register', function (Request $request, Event $event) {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $residentRequest = ResidentRequest::query()
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->first();

        if (! $residentRequest) {
            return redirect()->route('users.verification');
        }

        $barangayName = $residentRequest->barangay ?? null;
        $barangay = $barangayName ? Barangay::where('name', $barangayName)->first() : null;

        abort_unless($barangay && $event->barangay_id === $barangay->id && $event->category === 'event' && is_null($event->disabled_at), 404);

        if ($event->registration_limit !== null) {
            $registrationCount = DB::table('event_registrations')
                ->where('event_id', $event->id)
                ->count();

            if ($registrationCount >= $event->registration_limit) {
                return redirect()->route('users.events')->with('status', 'This event is already full.');
            }
        }

        DB::table('event_registrations')->updateOrInsert(
            [
                'user_id' => Auth::id(),
                'event_id' => $event->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->route('users.events')->with('status', 'Registered for event successfully.');
    })->name('users.events.register');

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

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('users.profile', [
            'user' => Auth::user(),
            'unreadInboxCount' => $unreadInboxCount,
        ]);
    })->name('users.profile');

    Route::get('/users/profile/details', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $unreadInboxCount = InboxMessage::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return view('users.profile-details', [
            'user' => Auth::user(),
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

    Route::post('/users/profile', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $user = User::query()->findOrFail(Auth::id());

        $validator = Validator::make($request->all(), [
            'full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'barangay' => ['nullable', 'string', 'max:255'],
            'household_no' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
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
            'name' => $data['full_name'] ?: $user->name,
            'full_name' => $data['full_name'] ?: null,
            'gender' => $data['gender'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'civil_status' => $data['civil_status'] ?: null,
            'contact_number' => $data['contact_number'] ?: null,
            'email' => $data['email'],
            'barangay' => $data['barangay'] ?: null,
            'household_no' => $data['household_no'] ?: null,
            'municipality' => $data['municipality'] ?: 'Victoria',
            'province' => $data['province'] ?: 'Oriental Mindoro',
            'profile_picture_path' => $profilePicturePath,
        ]);

        return redirect()->route('users.profile.details')->with('status', 'Profile updated successfully.');
    })->name('users.profile.update');

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

        $existingRequest = ResidentRequest::where('user_id', Auth::id())->first();
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

        if ($existingRequest && ! $showSentState) {
            return redirect()->route('users.main');
        }

        return view('users.verification', [
            'requestSent' => (bool) $existingRequest,
            'residentRequest' => $existingRequest,
            'barangayBoundaries' => $barangayBoundaries,
        ]);
    })->name('users.verification');

    Route::post('/users/verification', function (Request $request) {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:1'],
            'barangay' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'birthdate_display' => ['nullable', 'date'],
            'gender_display' => ['nullable', 'in:Male,Female'],
        ]);

        $middleInitial = strtoupper(trim((string) ($data['middle_initial'] ?? '')));
        $middleSegment = $middleInitial !== '' ? $middleInitial.'.' : null;
        $normalizedGender = filled($data['gender_display'] ?? null)
            ? ucfirst(strtolower(trim((string) $data['gender_display'])))
            : null;
        $birthdate = filled($data['birthdate_display'] ?? null)
            ? Carbon::parse((string) $data['birthdate_display'])
            : null;
        $calculatedAge = $birthdate?->age;
        $fullName = trim(implode(' ', array_filter([
            trim((string) $data['first_name']),
            $middleSegment,
            trim((string) $data['last_name']),
        ])));
        $submittedBarangay = trim((string) $data['barangay']);
        $coordinateString = trim((string) ($data['address'] ?? ''));
        $geoPoint = null;

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

        ResidentRequest::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'full_name' => $fullName,
                'barangay' => $submittedBarangay,
                'contact' => $data['contact'] ?? null,
                'address' => $data['address'] ?? null,
                'age' => $calculatedAge,
                'status' => 'pending',
                'reviewed_at' => null,
            ]
        );

        User::query()
            ->whereKey(Auth::id())
            ->update([
                'name' => $fullName,
                'full_name' => $fullName,
                'gender' => $normalizedGender,
                'date_of_birth' => $birthdate?->toDateString(),
                'age' => $calculatedAge,
                'contact_number' => $data['contact'] ?? null,
                'barangay' => $submittedBarangay ?: null,
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

        $data = $request->validate([
            'captain_name' => ['required', 'string', 'max:255'],
            'captain_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'captain_password' => ['required', 'string', 'min:8', 'confirmed'],
            'captain_barangay_id' => ['required', 'integer', 'exists:barangays,id'],
        ]);

        $barangay = Barangay::query()->findOrFail($data['captain_barangay_id']);

        $captain = User::query()->create([
            'name' => $data['captain_name'],
            'full_name' => $data['captain_name'],
            'email' => mb_strtolower(trim($data['captain_email'])),
            'role' => 'BrgCaptain',
            'barangay' => $barangay->name,
            'password' => $data['captain_password'],
        ]);

        $barangay->update([
            'captain_user_id' => $captain->id,
        ]);

        return redirect()->route('lgu.barangays')->with('status', 'Barangay captain created successfully.');
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

    $buildCaptainDashboardPayload = function (?Barangay $assignedBarangay): array {
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

        $acceptedResidents = ResidentRequest::query()
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

    Route::get('/bhw/events', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $barangayName = trim((string) Auth::user()->barangay);
        $assignedBarangay = $barangayName !== ''
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        $events = $assignedBarangay
            ? Event::query()
                ->where('barangay_id', $assignedBarangay->id)
                ->whereHas('createdBy', function ($query) {
                    $query->where('role', 'BHW');
                })
                ->orderByDesc('event_date')
                ->orderByDesc('start_time')
                ->get()
            : collect();

        return view('bhw.events', compact('events'));
    })->name('bhw.events');

    Route::post('/bhw/events', function (Request $request) {
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'registration_limit' => 'nullable|integer|min:1',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:4096',
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('event-images', 'public');
        }

        $event = Event::create([
            'barangay_id' => $assignedBarangay->id,
            'created_by' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'],
            'registration_limit' => $data['registration_limit'] ?? null,
            'category' => 'event',
            'image_url' => $imageUrl,
        ]);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('bhw.events')
                ->with('status', 'Event created successfully.');
        }

        return response()->json([
            'success' => true,
            'event' => $event,
            'image_url' => $imageUrl ? Storage::url($imageUrl) : null,
            'message' => 'Event created successfully',
        ]);
    })->name('bhw.events.store');

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
                ->whereIn('category', ['event', 'announcement'])
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
            'type' => ['required', 'in:event,announcement'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'registration_limit' => ['nullable', 'integer', 'min:1'],
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
            'registration_limit' => $data['registration_limit'] ?? null,
            'category' => $data['type'] === 'announcement' ? 'announcement' : 'event',
            'image_url' => $imageUrl,
        ]);

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
            'type' => ['required', 'in:event,announcement'],
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
            'registration_limit' => $data['registration_limit'] ?? null,
            'category' => $data['type'] === 'announcement' ? 'announcement' : 'event',
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

        if ($assignedBarangay) {
            $bhwWorkers = User::query()
                ->where('role', 'BHW')
                ->where('barangay', $assignedBarangay->name)
                ->orderBy('name')
                ->get();

            $puroks = Purok::query()
                ->where('barangay_id', $assignedBarangay->id)
                ->orderBy('name')
                ->get();
        }

        return view('barangay_captain.bhw_workers', array_merge([
            'assignedBarangay' => $assignedBarangay,
            'assignedBarangayBoundary' => $assignedBarangay?->boundary_geojson,
        ], compact('bhwWorkers', 'puroks')));
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

        $geometryWithinBoundary = null;
        $geometryWithinBoundary = static function (array $geometry, array $boundary) use (&$geometryWithinBoundary, $geometryContainsPoint): bool {
            $geometryType = $geometry['type'] ?? '';

            if ($geometryType === 'FeatureCollection') {
                foreach ($geometry['features'] ?? [] as $feature) {
                    if (! $geometryWithinBoundary($feature, $boundary)) {
                        return false;
                    }
                }

                return true;
            }

            if ($geometryType === 'Feature') {
                return $geometryWithinBoundary($geometry['geometry'] ?? [], $boundary);
            }

            if ($geometryType === 'Polygon') {
                $rings = $geometry['coordinates'] ?? [];
                $outerRing = $rings[0] ?? [];

                foreach ($outerRing as $point) {
                    $lng = (float) ($point[0] ?? 0);
                    $lat = (float) ($point[1] ?? 0);

                    if (! $geometryContainsPoint($boundary, $lat, $lng)) {
                        return false;
                    }
                }

                return true;
            }

            if ($geometryType === 'MultiPolygon') {
                foreach ($geometry['coordinates'] ?? [] as $polygon) {
                    if (! $geometryWithinBoundary(['type' => 'Polygon', 'coordinates' => $polygon], $boundary)) {
                        return false;
                    }
                }

                return true;
            }

            return false;
        };

        if (! empty($assignedBarangay->boundary_geojson) && ! $geometryWithinBoundary($purokBoundary, $assignedBarangay->boundary_geojson)) {
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
            'password' => ['required', 'string', 'min:8'],
        ]);

        $name = strstr($data['email'], '@', true) ?: $data['email'];

        User::query()->create([
            'name' => $name,
            'full_name' => null,
            'email' => mb_strtolower(trim($data['email'])),
            'role' => 'BHW',
            'barangay' => $assignedBarangay->name,
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'BHW worker created successfully.');
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
        ]);

        $worker = User::query()
            ->whereKey($data['worker_id'])
            ->where('role', 'BHW')
            ->where('barangay', $assignedBarangay->name)
            ->first();

        if (! $worker) {
            return back()
                ->withInput()
                ->withErrors([
                    'worker_id' => 'Unable to assign worker to this purok.',
                ]);
        }

        $purokId = $request->input('purok_id');

        if (! filled($purokId)) {
            $worker->update([
                'household_no' => null,
            ]);

            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->with('status', 'BHW worker unassigned from purok successfully.');
        }

        $request->validate([
            'purok_id' => ['required', 'integer', 'exists:puroks,id'],
        ]);

        $purok = Purok::query()
            ->whereKey($purokId)
            ->where('barangay_id', $assignedBarangay->id)
            ->first();

        if (! $purok) {
            return back()
                ->withInput([
                    'worker_id' => $data['worker_id'],
                    'worker_name' => $worker->name,
                    'purok_id' => $request->input('purok_id'),
                    'purok_name' => '',
                ])
                ->withErrors([
                    'purok_id' => 'The purok id field is required.',
                ]);
        }

        $worker->update([
            'household_no' => $purok->name,
        ]);

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'BHW worker assigned to purok successfully.');
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
            ->where('role', 'BHW')
            ->where('barangay', $assignedBarangay->name)
            ->first();

        if (! $worker) {
            return redirect()
                ->route('barangay-captain.bhw-workers')
                ->withErrors([
                    'worker_id' => 'Unable to update this account.',
                ]);
        }

        $worker->update([
            'role' => 'users',
            'household_no' => null,
        ]);

        return redirect()
            ->route('barangay-captain.bhw-workers')
            ->with('status', 'BHW account role removed successfully.');
    })->name('barangay-captain.bhw-workers.kick');

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

        // Unassign all BHW workers from this purok
        User::query()
            ->where('household_no', $purok->name)
            ->where('barangay', $assignedBarangay->name)
            ->update(['household_no' => null]);

        $purok->delete();

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
        return Auth::user()->role === 'BNS'
            ? view('bns.main')
            : redirect()->route('dashboard');
    })->name('bns.main');

    Route::get('/bns/monitoring', function () {
        return Auth::user()->role === 'BNS'
            ? view('bns.monitoring')
            : redirect()->route('dashboard');
    })->name('bns.monitoring');

    Route::get('/bns/household-visits', function () {
        return Auth::user()->role === 'BNS'
            ? view('bns.household_visits')
            : redirect()->route('dashboard');
    })->name('bns.household-visits');

    Route::get('/bns/programs', function () {
        return Auth::user()->role === 'BNS'
            ? view('bns.programs')
            : redirect()->route('dashboard');
    })->name('bns.programs');

    Route::get('/bns/beneficiaries', function () {
        return Auth::user()->role === 'BNS'
            ? view('bns.beneficiaries')
            : redirect()->route('dashboard');
    })->name('bns.beneficiaries');

    Route::get('/bns/reports', function () {
        if (Auth::user()->role !== 'BNS') {
            return redirect()->route('dashboard');
        }

        $fromDate = request('from_date');
        $toDate = request('to_date');

        $childrenQuery = ResidentRequest::query()
            ->where('status', 'accepted')
            ->whereNotNull('age')
            ->where('age', '<=', 5);

        if ($fromDate) {
            $childrenQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $childrenQuery->whereDate('created_at', '<=', $toDate);
        }

        $children = $childrenQuery->get();

        $childCount = $children->count();
        $householdCount = $children
            ->pluck('address')
            ->filter(fn ($address) => filled($address))
            ->unique()
            ->count();

        $reportData = [
            'monthly_nutrition_report' => [
                'total_children' => $childCount,
                'households_covered' => $householdCount,
                'active_monitoring' => $childCount,
            ],
            'malnutrition_case_report' => [
                'underweight_cases' => 0,
                'severely_underweight_cases' => 0,
                'recovered_cases' => 0,
            ],
            'feeding_program_summary' => [
                'enrolled_beneficiaries' => $childCount,
                'attendance_rate' => 0,
                'food_distributions' => 0,
            ],
            'child_growth_progress_report' => [
                'improving' => 0,
                'no_change' => 0,
                'needs_follow_up' => 0,
            ],
        ];

        return view('bns.reports', compact('reportData', 'fromDate', 'toDate'));
    })->name('bns.reports');

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

        return redirect()->route('bns.settings')->with('status', 'Profile updated successfully.');
    })->name('bns.settings.update');

    Route::get('/bhw', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $assignedResidents = ResidentRequest::query()
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

        $acceptedResidents = ResidentRequest::where('status', 'accepted')
            ->with('user')
            ->latest('reviewed_at')
            ->get();

        return view('bhw.residents', compact('acceptedResidents'));
    })->name('bhw.residents');

    Route::post('/bhw/residents', function (Request $request) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:1'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'household_no' => ['nullable', 'string', 'max:255'],
        ]);

        $middleInitial = strtoupper(trim((string) ($data['middle_initial'] ?? '')));
        $middleSegment = $middleInitial !== '' ? $middleInitial.'.' : null;
        $birthdate = filled($data['birthdate'] ?? null) ? Carbon::parse((string) $data['birthdate']) : null;
        $calculatedAge = $birthdate?->age;

        $fullName = trim(implode(' ', array_filter([
            trim((string) $data['first_name']),
            $middleSegment,
            trim((string) $data['last_name']),
        ])));

        ResidentRequest::create([
            'user_id' => Auth::id(),
            'full_name' => $fullName,
            'barangay' => Auth::user()->barangay ?: 'Unknown',
            'contact' => null,
            'address' => null,
            'age' => $calculatedAge,
            'status' => 'accepted',
            'reviewed_at' => now(),
        ]);

        return redirect()->route('bhw.residents')->with('status', 'Resident added successfully.');
    })->name('bhw.residents.store');

    Route::get('/bhw/requests', function (Request $request) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $search = trim((string) $request->query('search', ''));

        $pendingRequests = ResidentRequest::query()
            ->where('status', 'pending')
            ->with('user')
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

        return view('bhw.requests', [
            'pendingRequests' => $pendingRequests,
            'search' => $search,
        ]);
    })->name('bhw.requests');

    Route::get('/bhw/residents/{residentRequest}/profile', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $residentRequest->load([
            'user',
            'medicalRecords' => fn ($query) => $query->latest(),
            'consultations' => fn ($query) => $query->orderByDesc('scheduled_at'),
        ]);

        return view('bhw.profile', [
            'resident' => $residentRequest,
        ]);
    })->name('bhw.residents.profile');

    Route::post('/bhw/residents/{residentRequest}/medical-history', function (Request $request, ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        if ($residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
        }

        $data = $request->validate([
            'record_type' => ['required', 'string', 'max:100'],
            'record_value' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $recordType = trim((string) $data['record_type']);
        $recordValue = trim((string) $data['record_value']);

        MedicalRecord::query()->create([
            'resident_request_id' => $residentRequest->id,
            'user_id' => $residentRequest->user_id,
            'record_type' => $recordType,
            'record_value' => $recordValue,
            'note' => filled($data['note'] ?? null) ? trim((string) $data['note']) : null,
            'recorded_by' => Auth::id(),
        ]);

        $message = $recordType.': '.$recordValue;

        if (filled($data['note'] ?? null)) {
            $message .= '. Note: '.trim((string) $data['note']);
        }

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Medical History Updated',
            'message' => $message,
        ]);

        return redirect()->route('bhw.residents.profile', ['residentRequest' => $residentRequest, 'tab' => 'medical-history'])
            ->with('status', 'Medical record update sent to resident account notifications.');
    })->name('bhw.residents.medical-history.store');

    Route::post('/bhw/medical-records/{medicalRecord}/update', function (Request $request, MedicalRecord $medicalRecord) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $residentRequest = $medicalRecord->residentRequest;

        if (! $residentRequest || $residentRequest->status !== 'accepted') {
            return redirect()->route('bhw.residents')->with('status', 'Resident profile is only available for accepted residents.');
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

    Route::post('/bhw/requests/{residentRequest}/accept', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $residentRequest->update([
            'status' => 'accepted',
            'reviewed_at' => now(),
        ]);

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Resident Request Approved',
            'message' => 'Your resident verification request has been approved. You may now access your resident portal features.',
        ]);

        return redirect()->route('bhw.requests')->with('status', 'Request accepted.');
    })->name('bhw.requests.accept');

    Route::post('/bhw/requests/{residentRequest}/reject', function (ResidentRequest $residentRequest) {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
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

        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        InboxMessage::query()->create([
            'user_id' => $residentRequest->user_id,
            'title' => 'Message from BHW',
            'message' => $data['message'],
        ]);

        return redirect()->route('bhw.requests', ['search' => $request->query('search')])->with('status', 'Message sent to resident inbox.');
    })->name('bhw.requests.message');

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
