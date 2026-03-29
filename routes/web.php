<?php

use App\Models\Barangay;
use App\Models\Consultation;
use App\Models\InboxMessage;
use App\Models\MedicalRecord;
use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    })->name('login.perform');

    Route::post('/register', function (Request $request) {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => strstr($data['email'], '@', true) ?: $data['email'],
            'email' => $data['email'],
            'role' => 'users',
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    })->name('register.perform');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
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

        $nextConsultation = $upcomingConsultations->first();
        $nextCheckupLabel = $nextConsultation
            ? $nextConsultation->scheduled_at?->format('M d')
            : 'N/A';

        $lastCheckupLabel = $lastCompletedCheckup
            ? $lastCompletedCheckup->scheduled_at?->format('M d, Y')
            : 'N/A';

        return $hasRequest
            ? view('users.main', compact('unreadInboxCount', 'verificationStatus', 'upcomingConsultations', 'nextCheckupLabel', 'lastCheckupLabel', 'medicalSummaryRecords'))
            : redirect()->route('users.verification');
    })->name('users.main');

    Route::get('/users/notifications', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

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

        return view('users.profile', [
            'user' => Auth::user(),
        ]);
    })->name('users.profile');

    Route::post('/users/profile', function (Request $request) {
        if (Auth::user()->role !== 'users') {
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
            'municipality' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
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
            'municipality' => $data['municipality'] ?: null,
            'province' => $data['province'] ?: null,
            'profile_picture_path' => $profilePicturePath,
        ]);

        return redirect()->route('users.profile')->with('status', 'Profile updated successfully.');
    })->name('users.profile.update');

    Route::get('/users/verification', function () {
        if (Auth::user()->role !== 'users') {
            return redirect()->route('dashboard');
        }

        $existingRequest = ResidentRequest::where('user_id', Auth::id())->first();
        $showSentState = session('request_sent_state', false);

        if ($existingRequest && ! $showSentState) {
            return redirect()->route('users.main');
        }

        return view('users.verification', [
            'requestSent' => (bool) $existingRequest,
            'residentRequest' => $existingRequest,
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

        ResidentRequest::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'full_name' => $fullName,
                'barangay' => $data['barangay'],
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
                'barangay' => $data['barangay'] ?? null,
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

        $assignedCaptainIds = Barangay::query()
            ->whereNotNull('captain_user_id')
            ->pluck('captain_user_id');

        $availableCaptains = User::query()
            ->where('role', 'BrgCaptain')
            ->whereNotIn('id', $assignedCaptainIds)
            ->orderBy('name')
            ->get();

        return view('lgu.barangays', compact('barangays', 'availableCaptains'));
    })->name('lgu.barangays');

    Route::post('/lgu/barangays', function (Request $request) {
        if (Auth::user()->role !== 'LGU') {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:barangays,name'],
            'captain_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (! empty($data['captain_user_id'])) {
            $captain = User::query()->find($data['captain_user_id']);

            if (! $captain || $captain->role !== 'BrgCaptain') {
                return back()->withErrors([
                    'captain_user_id' => 'Selected account is not a Barangay Captain.',
                ])->withInput();
            }

            $alreadyAssigned = Barangay::query()
                ->where('captain_user_id', $captain->id)
                ->exists();

            if ($alreadyAssigned) {
                return back()->withErrors([
                    'captain_user_id' => 'This Barangay Captain is already assigned to another barangay.',
                ])->withInput();
            }
        }

        Barangay::create([
            'name' => $data['name'],
            'captain_user_id' => $data['captain_user_id'] ?? null,
        ]);

        return redirect()->route('lgu.barangays')->with('status', 'Barangay added successfully.');
    })->name('lgu.barangays.store');

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
        return Auth::user()->role === 'BrgCaptain'
            ? view('barangay_captain.events')
            : redirect()->route('dashboard');
    })->name('barangay-captain.events');

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

    Route::get('/bhw/reports', function () {
        if (Auth::user()->role !== 'BHW') {
            return redirect()->route('dashboard');
        }

        $fromDate = request('from_date');
        $toDate = request('to_date');

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

        return view('bhw.reports', compact('reportData', 'fromDate', 'toDate'));
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
