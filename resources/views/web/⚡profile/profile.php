<?php

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Title('My Profile')]
#[Layout('layouts.web')]
class extends Component
{
    use Toast;
    use WithFileUploads;
    use WithPagination;

    // ==========================================
    // UI STATE
    // ==========================================
    public ?string $avatarUrl = null;
    public ?string $bannerUrl = null;

    // ==========================================
    // FORM STATE - GENERAL
    // ==========================================
    public string $name = '';
    public string $email = '';

    // ==========================================
    // FORM STATE - PASSWORD
    // ==========================================
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // ==========================================
    // FORM STATE - IMAGE
    // ==========================================
    public $photo; // TemporaryUploadedFile|null
    public string $image_url = '';
    public $banner_photo; // TemporaryUploadedFile|null
    public string $banner_url = '';

    // ==========================================
    // FORM STATE - DETAILS
    // ==========================================
    public string $phone = '';
    public string $date_of_birth = '';
    public string $gender = '';
    public string $address = '';
    public string $postal_code = '';
    public string $occupation = '';
    public string $bio = '';

    // ==========================================
    // FORM STATE - SOCIAL MEDIA
    // ==========================================
    public string $website = '';
    public string $facebook = '';
    public string $twitter = '';
    public string $instagram = '';
    public string $linkedin = '';
    public string $youtube = '';
    public string $github = '';

    // ==========================================
    // FORM STATE - ADDRESS
    // ==========================================
    public ?int $division_id = null;
    public ?int $district_id = null;
    public ?int $upazila_id = null;
    public ?int $union_id = null;

    public array $divisions = [];
    public array $districts = [];
    public array $upazilas = [];
    public array $unions = [];

    // ==========================================
    // VALIDATION RULES
    // ==========================================
    public function rulesGeneral(): array
    {
        $user = Auth::user();
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];
    }

    public function rulesPassword(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function rulesPhoto(): array
    {
        return [
            'photo' => ['nullable', 'image', 'max:10240'], // 10MB
        ];
    }

    public function rulesImageUrl(): array
    {
        return [
            'image_url' => ['nullable', 'url'],
        ];
    }

    public function rulesBannerPhoto(): array
    {
        return [
            'banner_photo' => ['nullable', 'image', 'max:10240'], // 10MB
        ];
    }

    public function rulesBannerUrl(): array
    {
        return [
            'banner_url' => ['nullable', 'url'],
        ];
    }

    public function rulesDetails(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function rulesSocialMedia(): array
    {
        return [
            'website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function rulesAddress(): array
    {
        return [
            'division_id' => ['nullable', 'exists:divisions,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'upazila_id' => ['nullable', 'exists:upazilas,id'],
            'union_id' => ['nullable', 'exists:unions,id'],
        ];
    }

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================
    public function mount(): void
    {
        $this->authorize('profile.update');
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->refreshAvatarUrl();
        $this->refreshBannerUrl();

        // Load user details if exists
        if ($user->detail) {
            $this->phone = (string) ($user->detail->phone ?? '');
            $this->date_of_birth = $user->detail->date_of_birth ? $user->detail->date_of_birth->format('Y-m-d') : '';
            $this->gender = (string) ($user->detail->gender ?? '');
            $this->address = (string) ($user->detail->address ?? '');
            $this->postal_code = (string) ($user->detail->postal_code ?? '');
            $this->occupation = (string) ($user->detail->occupation ?? '');
            $this->bio = (string) ($user->detail->bio ?? '');
            $this->website = (string) ($user->detail->website ?? '');
            $this->facebook = (string) ($user->detail->facebook ?? '');
            $this->twitter = (string) ($user->detail->twitter ?? '');
            $this->instagram = (string) ($user->detail->instagram ?? '');
            $this->linkedin = (string) ($user->detail->linkedin ?? '');
            $this->youtube = (string) ($user->detail->youtube ?? '');
            $this->github = (string) ($user->detail->github ?? '');
            $this->division_id = $user->detail->division_id;
            $this->district_id = $user->detail->district_id;
            $this->upazila_id = $user->detail->upazila_id;
            $this->union_id = $user->detail->union_id;
        }

        // Load divisions
        $this->loadDivisions();

        // Load dependent dropdowns if values exist
        if ($this->division_id) {
            $this->loadDistricts();
        }
        if ($this->district_id) {
            $this->loadUpazilas();
        }
        if ($this->upazila_id) {
            $this->loadUnions();
        }
    }

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================
    #[Computed]
    public function sessions()
    {
        $user = Auth::user();
        return \DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return (object) [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => $session->last_activity,
                    'is_current' => $session->id === session()->getId(),
                ];
            });
    }

    #[Computed]
    public function activities()
    {
        return Activity::query()
            ->with(['subject'])
            ->causedBy(Auth::user())
            ->orderBy('created_at', 'desc')
            ->paginate(8);
    }

    // ==========================================
    // GENERAL INFORMATION ACTIONS
    // ==========================================
    public function saveGeneral(): void
    {
        $user = Auth::user();
        $validated = $this->validate($this->rulesGeneral());
        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
            $this->info(__('Profile updated. Check your inbox to verify the new email.'), position: 'toast-bottom');
        } else {
            $this->success(__('Profile updated.'), position: 'toast-bottom');
        }

        $this->refreshAvatarUrl();
    }

    // ==========================================
    // PASSWORD ACTIONS
    // ==========================================
    public function savePassword(): void
    {
        $user = Auth::user();
        $this->validate($this->rulesPassword());

        $user->password = $this->password;
        $user->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->success(__('Password changed successfully.'), position: 'toast-bottom');
    }

    // ==========================================
    // IMAGE ACTIONS
    // ==========================================
    public function savePhoto(): void
    {
        $user = Auth::user();
        $this->validate(array_merge(
            $this->rulesPhoto(),
            $this->rulesImageUrl()
        ));

        $this->handleMediaUpload($user);
        $this->reset(['photo', 'image_url']);
        $this->refreshAvatarUrl();
        $this->success(__('Profile image updated.'), position: 'toast-bottom');
    }

    public function removePhoto(): void
    {
        $user = Auth::user();
        $user->clearMediaCollection('profile');
        $this->reset(['photo', 'image_url']);
        $this->refreshAvatarUrl();
        $this->warning(__('Profile image removed.'), position: 'toast-bottom');
    }

    public function saveBanner(): void
    {
        $user = Auth::user();
        $this->validate(array_merge(
            $this->rulesBannerPhoto(),
            $this->rulesBannerUrl()
        ));

        $this->handleBannerUpload($user);
        $this->reset(['banner_photo', 'banner_url']);
        $this->refreshBannerUrl();
        $this->success(__('Banner image updated.'), position: 'toast-bottom');
    }

    public function removeBanner(): void
    {
        $user = Auth::user();
        $user->clearMediaCollection('banner');
        $this->reset(['banner_photo', 'banner_url']);
        $this->refreshBannerUrl();
        $this->warning(__('Banner image removed.'), position: 'toast-bottom');
    }

    // ==========================================
    // SESSION ACTIONS
    // ==========================================
    public function logoutSession(string $sessionId): void
    {
        $user = Auth::user();

        if ($sessionId === session()->getId()) {
            $this->warning(__('Cannot logout current session. Use logout button instead.'), position: 'toast-bottom');
            return;
        }

        \DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        $this->success(__('Session logged out successfully.'), position: 'toast-bottom');
    }

    public function logoutAllOtherSessions(): void
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();

        $count = \DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        $this->success(__(':count other sessions logged out successfully.', ['count' => $count]), position: 'toast-bottom');
    }

    // ==========================================
    // DETAILS ACTIONS
    // ==========================================
    public function saveDetails(): void
    {
        $user = Auth::user();
        $validated = $this->validate($this->rulesDetails());

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $this->success(__('Details updated successfully.'), position: 'toast-bottom');
    }

    // ==========================================
    // SOCIAL MEDIA ACTIONS
    // ==========================================
    public function saveSocialMedia(): void
    {
        $user = Auth::user();
        $validated = $this->validate($this->rulesSocialMedia());

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $this->success(__('Social media links updated successfully.'), position: 'toast-bottom');
    }

    // ==========================================
    // ADDRESS ACTIONS
    // ==========================================
    public function saveAddress(): void
    {
        $user = Auth::user();
        $validated = $this->validate($this->rulesAddress());

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $this->success(__('Address updated successfully.'), position: 'toast-bottom');
    }

    public function updatedDivisionId($value): void
    {
        $this->district_id = null;
        $this->upazila_id = null;
        $this->union_id = null;
        $this->districts = [];
        $this->upazilas = [];
        $this->unions = [];

        if ($value) {
            $this->loadDistricts();
        }
    }

    public function updatedDistrictId($value): void
    {
        $this->upazila_id = null;
        $this->union_id = null;
        $this->upazilas = [];
        $this->unions = [];

        if ($value) {
            $this->loadUpazilas();
        }
    }

    public function updatedUpazilaId($value): void
    {
        $this->union_id = null;
        $this->unions = [];

        if ($value) {
            $this->loadUnions();
        }
    }

    protected function loadDivisions(): void
    {
        $this->divisions = \App\Models\Division::orderBy('name')
            ->get()
            ->map(fn($division) => [
                'id' => $division->id,
                'name' => $division->name,
            ])
            ->toArray();
    }

    protected function loadDistricts(): void
    {
        if (!$this->division_id) {
            return;
        }

        $this->districts = \App\Models\District::where('division_id', $this->division_id)
            ->orderBy('name')
            ->get()
            ->map(fn($district) => [
                'id' => $district->id,
                'name' => $district->name,
            ])
            ->toArray();
    }

    protected function loadUpazilas(): void
    {
        if (!$this->district_id) {
            return;
        }

        $this->upazilas = \App\Models\Upazila::where('district_id', $this->district_id)
            ->orderBy('name')
            ->get()
            ->map(fn($upazila) => [
                'id' => $upazila->id,
                'name' => $upazila->name,
            ])
            ->toArray();
    }

    protected function loadUnions(): void
    {
        if (!$this->upazila_id) {
            return;
        }

        $this->unions = \App\Models\Union::where('upazila_id', $this->upazila_id)
            ->orderBy('name')
            ->get()
            ->map(fn($union) => [
                'id' => $union->id,
                'name' => $union->name,
            ])
            ->toArray();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================
    protected function refreshAvatarUrl(): void
    {
        $user = Auth::user();
        $this->avatarUrl = userImage($user);
    }

    protected function refreshBannerUrl(): void
    {
        $user = Auth::user();
        $this->bannerUrl = $user->banner_url;
    }

    protected function handleMediaUpload($user): void
    {
        if ($this->image_url && checkImageUrl($this->image_url)) {
            $extension = pathinfo(parse_url($this->image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $user->addMediaFromUrl($this->image_url)
                ->usingFileName($user->id.'.'.$extension)
                ->toMediaCollection('profile');
        } elseif ($this->photo) {
            $user->addMedia($this->photo->getRealPath())
                ->usingFileName($user->id.'.'.$this->photo->extension())
                ->toMediaCollection('profile');
        }
    }

    protected function handleBannerUpload($user): void
    {
        if ($this->banner_url && checkImageUrl($this->banner_url)) {
            $extension = pathinfo(parse_url($this->banner_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $user->addMediaFromUrl($this->banner_url)
                ->usingFileName('banner_'.$user->id.'.'.$extension)
                ->toMediaCollection('banner');
        } elseif ($this->banner_photo) {
            $user->addMedia($this->banner_photo->getRealPath())
                ->usingFileName('banner_'.$user->id.'.'.$this->banner_photo->extension())
                ->toMediaCollection('banner');
        }
    }
}
