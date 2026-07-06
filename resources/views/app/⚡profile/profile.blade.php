<div x-data="profile()">
    {{-- ========================================== --}}
    {{-- HEADER --}}
    {{-- ========================================== --}}
    <x-header :title="__('Profile')" :subtitle="__('Manage your personal info, password, and photo.')" separator>
    </x-header>

    {{-- ========================================== --}}
    {{-- MAIN LAYOUT --}}
    {{-- ========================================== --}}
    <div class="grid lg:grid-cols-4 gap-6 px-0 mx-0 mt-6">

        {{-- ========================================== --}}
        {{-- SIDEBAR --}}
        {{-- ========================================== --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- User Info Card --}}
            <x-card class="p-5">
                <div class="flex items-center gap-4">
                    <x-avatar :image="$avatarUrl" alt="{{ auth()->user()->name }}" class="w-16 h-16 ring-2 ring-primary/20" />
                    <div>
                        <div class="font-semibold text-base-content/90">{{ $name }}</div>
                        <div class="text-sm text-base-content/60">{{ $email }}</div>
                    </div>
                </div>
            </x-card>

            <x-menu class="bg-base-100 rounded-lg shadow">
                <x-menu-item
                    :title="__('View Profile')"
                    icon="o-eye"
                    @click="activeTab = 'view'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'view' }"
                />
                <x-menu-item
                    :title="__('General')"
                    icon="o-user"
                    @click="activeTab = 'general'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'general' }"
                />
                <x-menu-item
                    :title="__('Details')"
                    icon="o-identification"
                    @click="activeTab = 'details'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'details' }"
                />
                <x-menu-item
                    :title="__('Social Media')"
                    icon="o-share"
                    @click="activeTab = 'social'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'social' }"
                />
                <x-menu-item
                    :title="__('Address')"
                    icon="o-map-pin"
                    @click="activeTab = 'address'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'address' }"
                />
                <x-menu-item
                    :title="__('Password')"
                    icon="o-lock-closed"
                    @click="activeTab = 'password'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'password' }"
                />
                <x-menu-item
                    :title="__('Image')"
                    icon="o-photo"
                    @click="activeTab = 'image'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'image' }"
                />
                <x-menu-item
                    :title="__('Sessions')"
                    icon="o-computer-desktop"
                    @click="activeTab = 'sessions'"
                    x-bind:class="{ 'bg-base-300': activeTab === 'sessions' }"
                />
            </x-menu>
        </div>

        {{-- ========================================== --}}
        {{-- CONTENT AREA --}}
        {{-- ========================================== --}}
        <div class="lg:col-span-3">

            {{-- ========================================== --}}
            {{-- VIEW PROFILE TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'view'" x-cloak>
                {{-- Banner Section --}}
                @if($bannerUrl)
                    <div class="relative w-full h-48 md:h-64 rounded-t-lg overflow-hidden bg-gradient-to-r from-primary/20 to-secondary/20">
                        <img src="{{ $bannerUrl }}" alt="Banner" class="w-full h-full object-cover" />
                    </div>
                @else
                    <div class="relative w-full h-48 md:h-64 rounded-t-lg overflow-hidden bg-gradient-to-r from-primary/20 to-secondary/20"></div>
                @endif

                {{-- Profile Header Card --}}
                <x-card class="-mt-16 relative">
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-6 mb-8">
                        <x-avatar :image="$avatarUrl" alt="{{ $name }}" class="w-32 h-32 ring-4 ring-base-100 shadow-xl" />
                        <div class="text-center md:text-left flex-1">
                            <h1 class="text-3xl font-bold text-base-content">{{ $name }}</h1>
                            <p class="text-base-content/60 flex items-center justify-center md:justify-start gap-2 mt-1">
                                <x-icon name="o-envelope" class="w-4 h-4" />
                                {{ $email }}
                            </p>
                            @if($occupation)
                                <p class="text-base-content/80 font-medium mt-2">{{ $occupation }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Bio Section --}}
                    @if($bio)
                        <div class="mb-8 p-4 bg-base-200 rounded-lg">
                            <h3 class="font-semibold text-lg mb-2 flex items-center gap-2">
                                <x-icon name="o-document-text" class="w-5 h-5" />
                                {{ __('About') }}
                            </h3>
                            <p class="text-base-content/80 whitespace-pre-line">{{ $bio }}</p>
                        </div>
                    @endif

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Personal Information --}}
                        <div>
                            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                                <x-icon name="o-user" class="w-5 h-5" />
                                {{ __('Personal Information') }}
                            </h3>
                            <div class="space-y-3">
                                @if($phone)
                                    <div class="flex items-start gap-3">
                                        <x-icon name="o-phone" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                        <div>
                                            <p class="text-sm text-base-content/60">{{ __('Phone') }}</p>
                                            <p class="font-medium">{{ $phone }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($date_of_birth)
                                    <div class="flex items-start gap-3">
                                        <x-icon name="o-cake" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                        <div>
                                            <p class="text-sm text-base-content/60">{{ __('Date of Birth') }}</p>
                                            <p class="font-medium">{{ \Carbon\Carbon::parse($date_of_birth)->format('F d, Y') }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($gender)
                                    <div class="flex items-start gap-3">
                                        <x-icon name="o-user-circle" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                        <div>
                                            <p class="text-sm text-base-content/60">{{ __('Gender') }}</p>
                                            <p class="font-medium capitalize">{{ $gender }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($website)
                                    <div class="flex items-start gap-3">
                                        <x-icon name="o-globe-alt" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                        <div>
                                            <p class="text-sm text-base-content/60">{{ __('Website') }}</p>
                                            <a href="{{ $website }}" target="_blank" class="font-medium text-primary hover:underline">
                                                {{ $website }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Address Information --}}
                        <div>
                            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                                <x-icon name="o-map-pin" class="w-5 h-5" />
                                {{ __('Address') }}
                            </h3>
                            <div class="space-y-3">
                                @php
                                    $user = Auth::user();
                                    $hasAddress = $division_id || $district_id || $upazila_id || $union_id || $address || $postal_code;
                                @endphp

                                @if($hasAddress)
                                    @if($division_id)
                                        <div class="flex items-start gap-3">
                                            <x-icon name="o-map" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                            <div>
                                                <p class="text-sm text-base-content/60">{{ __('Division') }}</p>
                                                <p class="font-medium">{{ $user->detail->division->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($district_id)
                                        <div class="flex items-start gap-3">
                                            <x-icon name="o-map" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                            <div>
                                                <p class="text-sm text-base-content/60">{{ __('District') }}</p>
                                                <p class="font-medium">{{ $user->detail->district->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($upazila_id)
                                        <div class="flex items-start gap-3">
                                            <x-icon name="o-map" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                            <div>
                                                <p class="text-sm text-base-content/60">{{ __('Upazila') }}</p>
                                                <p class="font-medium">{{ $user->detail->upazila->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($union_id)
                                        <div class="flex items-start gap-3">
                                            <x-icon name="o-map" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                            <div>
                                                <p class="text-sm text-base-content/60">{{ __('Union') }}</p>
                                                <p class="font-medium">{{ $user->detail->union->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($address)
                                        <div class="flex items-start gap-3">
                                            <x-icon name="o-home" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                            <div>
                                                <p class="text-sm text-base-content/60">{{ __('Street Address') }}</p>
                                                <p class="font-medium">{{ $address }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($postal_code)
                                        <div class="flex items-start gap-3">
                                            <x-icon name="o-envelope" class="w-5 h-5 text-base-content/60 mt-0.5" />
                                            <div>
                                                <p class="text-sm text-base-content/60">{{ __('Postal Code') }}</p>
                                                <p class="font-medium">{{ $postal_code }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-base-content/60 text-sm">{{ __('No address information available.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Social Media Links --}}
                    @php
                        $hasSocial = $facebook || $twitter || $instagram || $linkedin || $youtube || $github;
                    @endphp

                    @if($hasSocial)
                        <div class="mt-8 pt-6 border-t dark:border-gray-700">
                            <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                                <x-icon name="o-share" class="w-5 h-5" />
                                {{ __('Social Media') }}
                            </h3>
                            <div class="flex flex-wrap gap-3">
                                @if($facebook)
                                    <a href="{{ $facebook }}" target="_blank" class="btn btn-sm btn-outline gap-2">
                                        <x-icon name="o-share" class="w-4 h-4" />
                                        Facebook
                                    </a>
                                @endif

                                @if($twitter)
                                    <a href="{{ $twitter }}" target="_blank" class="btn btn-sm btn-outline gap-2">
                                        <x-icon name="o-share" class="w-4 h-4" />
                                        Twitter
                                    </a>
                                @endif

                                @if($instagram)
                                    <a href="{{ $instagram }}" target="_blank" class="btn btn-sm btn-outline gap-2">
                                        <x-icon name="o-share" class="w-4 h-4" />
                                        Instagram
                                    </a>
                                @endif

                                @if($linkedin)
                                    <a href="{{ $linkedin }}" target="_blank" class="btn btn-sm btn-outline gap-2">
                                        <x-icon name="o-share" class="w-4 h-4" />
                                        LinkedIn
                                    </a>
                                @endif

                                @if($youtube)
                                    <a href="{{ $youtube }}" target="_blank" class="btn btn-sm btn-outline gap-2">
                                        <x-icon name="o-share" class="w-4 h-4" />
                                        YouTube
                                    </a>
                                @endif

                                @if($github)
                                    <a href="{{ $github }}" target="_blank" class="btn btn-sm btn-outline gap-2">
                                        <x-icon name="o-share" class="w-4 h-4" />
                                        GitHub
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- GENERAL INFORMATION TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'general'" x-cloak>
                <x-card>
                    <x-header :title="__('General Information')" :subtitle="__('Update your name and email address.')" class="mb-5" />
                    <form wire:submit="saveGeneral" class="space-y-5">
                        <x-input
                            :label="__('Full name')"
                            icon="o-user"
                            wire:model.defer="name"
                            required
                            :placeholder="__('Your name')"
                        />
                        <x-input
                            :label="__('Email')"
                            icon="o-envelope"
                            wire:model.defer="email"
                            type="email"
                            required
                            :placeholder="__('you@example.com')"
                        />

                        @can('profile.update')
                            <div class="flex gap-2">
                                <x-button
                                    type="submit"
                                    spinner="saveGeneral"
                                    class="btn-primary"
                                    icon="o-check"
                                >
                                    {{ __('Save changes') }}
                                </x-button>
                                <x-button
                                    type="button"
                                    class="btn-ghost"
                                    icon="o-arrow-path"
                                    wire:click="$refresh"
                                >
                                    {{ __('Reset') }}
                                </x-button>
                            </div>
                        @endcan
                    </form>
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- DETAILS TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'details'" x-cloak>
                <x-card>
                    <x-header :title="__('Personal Details')" :subtitle="__('Update your personal information.')" class="mb-5" />
                    <form wire:submit="saveDetails" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-input
                                :label="__('Phone')"
                                icon="o-phone"
                                wire:model.defer="phone"
                                type="tel"
                                :placeholder="__('Your phone number')"
                            />
                            <x-input
                                :label="__('Date of Birth')"
                                icon="o-calendar"
                                wire:model.defer="date_of_birth"
                                type="date"
                            />
                        </div>

                        <x-select
                            :label="__('Gender')"
                            icon="o-user"
                            wire:model.defer="gender"
                            :options="[
                                ['id' => '', 'name' => __('Select Gender')],
                                ['id' => 'male', 'name' => __('Male')],
                                ['id' => 'female', 'name' => __('Female')],
                                ['id' => 'other', 'name' => __('Other')]
                            ]"
                        />

                        <x-input
                            :label="__('Occupation')"
                            icon="o-briefcase"
                            wire:model.defer="occupation"
                            :placeholder="__('Your occupation')"
                        />

                        <x-textarea
                            :label="__('Address')"
                            icon="o-map-pin"
                            wire:model.defer="address"
                            rows="2"
                            :placeholder="__('Your address')"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-input
                                :label="__('Postal Code')"
                                icon="o-map"
                                wire:model.defer="postal_code"
                                :placeholder="__('Postal code')"
                            />
                        </div>

                        <x-textarea
                            :label="__('Bio')"
                            icon="o-document-text"
                            wire:model.defer="bio"
                            rows="4"
                            :placeholder="__('Tell us about yourself...')"
                            :hint="__('Maximum 1000 characters')"
                        />

                        @can('profile.update')
                            <div class="flex gap-2">
                                <x-button
                                    type="submit"
                                    spinner="saveDetails"
                                    class="btn-primary"
                                    icon="o-check"
                                >
                                    {{ __('Save Details') }}
                                </x-button>
                                <x-button
                                    type="button"
                                    class="btn-ghost"
                                    icon="o-arrow-path"
                                    wire:click="$refresh"
                                >
                                    {{ __('Reset') }}
                                </x-button>
                            </div>
                        @endcan
                    </form>
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- SOCIAL MEDIA TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'social'" x-cloak>
                <x-card>
                    <x-header :title="__('Social Media Links')" :subtitle="__('Connect your social media profiles.')" class="mb-5" />
                    <form wire:submit="saveSocialMedia" class="space-y-5">
                        <x-input
                            :label="__('Website')"
                            icon="o-globe-alt"
                            wire:model.defer="website"
                            type="url"
                            :placeholder="__('https://yourwebsite.com')"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-input
                                :label="__('Facebook')"
                                icon="o-link"
                                wire:model.defer="facebook"
                                type="url"
                                :placeholder="__('https://facebook.com/username')"
                            />
                            <x-input
                                :label="__('Twitter')"
                                icon="o-link"
                                wire:model.defer="twitter"
                                type="url"
                                :placeholder="__('https://twitter.com/username')"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-input
                                :label="__('Instagram')"
                                icon="o-link"
                                wire:model.defer="instagram"
                                type="url"
                                :placeholder="__('https://instagram.com/username')"
                            />
                            <x-input
                                :label="__('LinkedIn')"
                                icon="o-link"
                                wire:model.defer="linkedin"
                                type="url"
                                :placeholder="__('https://linkedin.com/in/username')"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-input
                                :label="__('YouTube')"
                                icon="o-link"
                                wire:model.defer="youtube"
                                type="url"
                                :placeholder="__('https://youtube.com/@username')"
                            />
                            <x-input
                                :label="__('GitHub')"
                                icon="o-link"
                                wire:model.defer="github"
                                type="url"
                                :placeholder="__('https://github.com/username')"
                            />
                        </div>

                        @can('profile.update')
                            <div class="flex gap-2">
                                <x-button
                                    type="submit"
                                    spinner="saveSocialMedia"
                                    class="btn-primary"
                                    icon="o-check"
                                >
                                    {{ __('Save Social Media') }}
                                </x-button>
                                <x-button
                                    type="button"
                                    class="btn-ghost"
                                    icon="o-arrow-path"
                                    wire:click="$refresh"
                                >
                                    {{ __('Reset') }}
                                </x-button>
                            </div>
                        @endcan
                    </form>
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- ADDRESS TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'address'" x-cloak>
                <x-card>
                    <x-header :title="__('Address Information')" :subtitle="__('Update your address details.')" class="mb-5" />
                    <form wire:submit="saveAddress" class="space-y-5">
                        <x-select
                            :label="__('Division')"
                            icon="o-map"
                            wire:model.live="division_id"
                            :options="$divisions"
                            placeholder="Select Division"
                        />

                        @if(count($districts) > 0)
                            <x-select
                                :label="__('District')"
                                icon="o-map"
                                wire:model.live="district_id"
                                :options="$districts"
                                placeholder="Select District"
                            />
                        @endif

                        @if(count($upazilas) > 0)
                            <x-select
                                :label="__('Upazila')"
                                icon="o-map"
                                wire:model.live="upazila_id"
                                :options="$upazilas"
                                placeholder="Select Upazila"
                            />
                        @endif

                        @if(count($unions) > 0)
                            <x-select
                                :label="__('Union')"
                                icon="o-map"
                                wire:model.live="union_id"
                                :options="$unions"
                                placeholder="Select Union"
                            />
                        @endif

                        @can('profile.update')
                            <div class="flex gap-2">
                                <x-button
                                    type="submit"
                                    spinner="saveAddress"
                                    class="btn-primary"
                                    icon="o-check"
                                >
                                    {{ __('Save Address') }}
                                </x-button>
                                <x-button
                                    type="button"
                                    class="btn-ghost"
                                    icon="o-arrow-path"
                                    wire:click="$refresh"
                                >
                                    {{ __('Reset') }}
                                </x-button>
                            </div>
                        @endcan
                    </form>
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- PASSWORD TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'password'" x-cloak>
                <x-card>
                    <x-header :title="__('Security')" :subtitle="__('Change your password.')" class="mb-5" />
                    <form wire:submit="savePassword" class="space-y-5">
                        <x-input
                            :label="__('Current password')"
                            icon="o-lock-closed"
                            wire:model.defer="current_password"
                            type="password"
                            required
                        />
                        <x-input
                            :label="__('New password')"
                            icon="o-key"
                            wire:model.defer="password"
                            type="password"
                            required
                        />
                        <x-input
                            :label="__('Confirm new password')"
                            icon="o-key"
                            wire:model.defer="password_confirmation"
                            type="password"
                            required
                        />

                        @can('profile.update')
                            <div class="flex gap-2">
                                <x-button
                                    type="submit"
                                    spinner="savePassword"
                                    class="btn-primary"
                                    icon="o-check"
                                >
                                    {{ __('Change password') }}
                                </x-button>
                            </div>
                        @endcan
                    </form>
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- IMAGE TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'image'" x-cloak>
                <x-card>
                    <x-header :title="__('Profile Image')" :subtitle="__('Manage your avatar.')" class="mb-5" />

                    {{-- Upload Section Accordion --}}
                    <div x-data="{ uploadOpen: true }" class="border rounded-lg dark:border-gray-700">
                        <button
                            @click="uploadOpen = !uploadOpen"
                            type="button"
                            class="flex items-center justify-between w-full p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <div class="flex items-center gap-2">
                                <x-icon name="o-arrow-up-tray" class="w-5 h-5" />
                                <span class="font-medium">{{ __('Upload Image') }}</span>
                            </div>
                            <x-icon
                                name="o-chevron-down"
                                class="w-5 h-5 transition-transform"
                                x-bind:class="{ 'rotate-180': uploadOpen }"
                            />
                        </button>

                        <div
                            x-show="uploadOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-cloak
                            class="p-4 space-y-4 border-t dark:border-gray-700"
                        >
                            {{-- Image URL Input --}}
                            <x-input
                                wire:model="image_url"
                                type="url"
                                :label="__('Image URL')"
                                icon="o-link"
                                :placeholder="__('https://example.com/avatar.jpg')"
                            />

                            <div class="text-sm text-center text-gray-500">{{ __('OR') }}</div>

                            {{-- File Upload --}}
                            <x-avatar-upload
                                :label="__('Upload from Device')"
                                model="photo"
                                :image="$photo ? $photo->temporaryUrl() : $avatarUrl"
                                :hint="__('PNG, JPG, WEBP up to 10MB')"
                            />

                            {{-- Save Button --}}
                            @can('profile.update')
                                <div class="flex gap-2 pt-2">
                                    <x-button
                                        wire:click="savePhoto"
                                        wire:loading.attr="disabled"
                                        class="btn-primary"
                                        icon="o-check"
                                    >
                                        <span wire:loading.remove wire:target="savePhoto">
                                            {{ __('Save Image') }}
                                        </span>
                                        <span wire:loading wire:target="savePhoto">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            {{ __('Saving...') }}
                                        </span>
                                    </x-button>
                                    <x-button
                                        wire:click="$set('photo', null); $set('image_url', '')"
                                        class="btn-ghost"
                                        icon="o-x-mark"
                                    >
                                        {{ __('Clear') }}
                                    </x-button>
                                </div>
                            @endcan
                        </div>
                    </div>

                    {{-- Remove Image Section --}}
                    @can('profile.update')
                        <div class="mt-6 p-4 border border-error/20 rounded-lg bg-error/5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-medium text-error">{{ __('Remove Image') }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ __('Revert to the default avatar.') }}
                                    </p>
                                </div>
                                <x-button
                                    wire:click="removePhoto"
                                    wire:loading.attr="disabled"
                                    class="btn-error btn-sm"
                                    icon="o-trash"
                                    :confirm="__('Are you sure you want to remove your avatar?')"
                                >
                                    {{ __('Remove') }}
                                </x-button>
                            </div>
                        </div>
                    @endcan
                </x-card>

                {{-- Banner Image Card --}}
                <x-card class="mt-6">
                    <x-header :title="__('Banner Image')" :subtitle="__('Upload a banner image for your profile.')" class="mb-5" />

                    {{-- Current Banner Preview --}}
                    @if($bannerUrl)
                        <div class="mb-6">
                            <p class="text-sm font-medium mb-2">{{ __('Current Banner') }}</p>
                            <div class="relative w-full h-48 rounded-lg overflow-hidden border dark:border-gray-700">
                                <img src="{{ $bannerUrl }}" alt="Banner" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    @endif

                    {{-- Upload Banner Section --}}
                    <div x-data="{ bannerUploadOpen: true }" class="border rounded-lg dark:border-gray-700">
                        <button
                            @click="bannerUploadOpen = !bannerUploadOpen"
                            type="button"
                            class="flex items-center justify-between w-full p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <div class="flex items-center gap-2">
                                <x-icon name="o-arrow-up-tray" class="w-5 h-5" />
                                <span class="font-medium">{{ __('Upload Banner') }}</span>
                            </div>
                            <x-icon
                                name="o-chevron-down"
                                class="w-5 h-5 transition-transform"
                                x-bind:class="{ 'rotate-180': bannerUploadOpen }"
                            />
                        </button>

                        <div
                            x-show="bannerUploadOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-cloak
                            class="p-4 space-y-4 border-t dark:border-gray-700"
                        >
                            {{-- Banner URL Input --}}
                            <x-input
                                wire:model="banner_url"
                                type="url"
                                :label="__('Banner URL')"
                                icon="o-link"
                                :placeholder="__('https://example.com/banner.jpg')"
                            />

                            <div class="text-sm text-center text-gray-500">{{ __('OR') }}</div>

                            {{-- File Upload --}}
                            <x-file
                                wire:model="banner_photo"
                                :label="__('Upload from Device')"
                                accept="image/*"
                                :hint="__('PNG, JPG, WEBP up to 10MB. Recommended size: 1500x500px')"
                            />

                            @if($banner_photo)
                                <div class="mt-2">
                                    <p class="text-sm font-medium mb-2">{{ __('Preview') }}</p>
                                    <div class="relative w-full h-48 rounded-lg overflow-hidden border dark:border-gray-700">
                                        <img src="{{ $banner_photo->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover" />
                                    </div>
                                </div>
                            @endif

                            {{-- Save Button --}}
                            @can('profile.update')
                                <div class="flex gap-2 pt-2">
                                    <x-button
                                        wire:click="saveBanner"
                                        wire:loading.attr="disabled"
                                        class="btn-primary"
                                        icon="o-check"
                                    >
                                        <span wire:loading.remove wire:target="saveBanner">
                                            {{ __('Save Banner') }}
                                        </span>
                                        <span wire:loading wire:target="saveBanner">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            {{ __('Saving...') }}
                                        </span>
                                    </x-button>
                                    <x-button
                                        wire:click="$set('banner_photo', null); $set('banner_url', '')"
                                        class="btn-ghost"
                                        icon="o-x-mark"
                                    >
                                        {{ __('Clear') }}
                                    </x-button>
                                </div>
                            @endcan
                        </div>
                    </div>

                    {{-- Remove Banner Section --}}
                    @can('profile.update')
                        @if($bannerUrl)
                            <div class="mt-6 p-4 border border-error/20 rounded-lg bg-error/5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-medium text-error">{{ __('Remove Banner') }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ __('Remove your banner image.') }}
                                        </p>
                                    </div>
                                    <x-button
                                        wire:click="removeBanner"
                                        wire:loading.attr="disabled"
                                        class="btn-error btn-sm"
                                        icon="o-trash"
                                        :confirm="__('Are you sure you want to remove your banner?')"
                                    >
                                        {{ __('Remove') }}
                                    </x-button>
                                </div>
                            </div>
                        @endif
                    @endcan
                </x-card>
            </div>

            {{-- ========================================== --}}
            {{-- SESSIONS TAB --}}
            {{-- ========================================== --}}
            <div x-show="activeTab === 'sessions'" x-cloak>
                <x-card>
                    <x-header :title="__('Active Sessions')" :subtitle="__('Manage your browser sessions across devices.')" class="mb-5" />

                    <p class="text-sm text-base-content/60 mb-6">
                        {{ __('If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.') }}
                    </p>

                    {{-- Sessions List --}}
                    <div class="space-y-4 mb-6">
                        @forelse($this->sessions as $session)
                            <div class="flex items-start gap-4 p-4 border rounded-lg {{ $session->is_current ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                                {{-- Device Icon --}}
                                <div class="flex-shrink-0">
                                    @php
                                        $agent = strtolower($session->user_agent ?? '');
                                        $isMobile = str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone');
                                        $isTablet = str_contains($agent, 'tablet') || str_contains($agent, 'ipad');
                                    @endphp
                                    <div class="w-12 h-12 rounded-full {{ $session->is_current ? 'bg-primary' : 'bg-base-300' }} flex items-center justify-center">
                                        @if($isMobile)
                                            <x-icon name="o-device-phone-mobile" class="w-6 h-6 {{ $session->is_current ? 'text-white' : 'text-base-content' }}" />
                                        @elseif($isTablet)
                                            <x-icon name="o-device-tablet" class="w-6 h-6 {{ $session->is_current ? 'text-white' : 'text-base-content' }}" />
                                        @else
                                            <x-icon name="o-computer-desktop" class="w-6 h-6 {{ $session->is_current ? 'text-white' : 'text-base-content' }}" />
                                        @endif
                                    </div>
                                </div>

                                {{-- Session Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            @if($session->is_current)
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-semibold text-base-content">{{ __('This Device') }}</h4>
                                                    <span class="badge badge-primary badge-sm">{{ __('Current') }}</span>
                                                </div>
                                            @else
                                                <h4 class="font-semibold text-base-content mb-1">{{ __('Browser Session') }}</h4>
                                            @endif

                                            <div class="space-y-1 text-sm text-base-content/60">
                                                @if($session->ip_address)
                                                    <div class="flex items-center gap-2">
                                                        <x-icon name="o-globe-alt" class="w-4 h-4" />
                                                        <span>{{ $session->ip_address }}</span>
                                                    </div>
                                                @endif

                                                @if($session->user_agent)
                                                    <div class="flex items-center gap-2">
                                                        <x-icon name="o-information-circle" class="w-4 h-4" />
                                                        <span class="truncate">{{ Str::limit($session->user_agent, 60) }}</span>
                                                    </div>
                                                @endif

                                                <div class="flex items-center gap-2">
                                                    <x-icon name="o-clock" class="w-4 h-4" />
                                                    <span>{{ __('Last active') }}: {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Logout Button --}}
                                        @if(!$session->is_current)
                                            @can('profile.update')
                                                <x-button
                                                    wire:click="logoutSession('{{ $session->id }}')"
                                                    wire:loading.attr="disabled"
                                                    class="btn-ghost btn-sm"
                                                    icon="o-x-mark"
                                                    :confirm="__('Are you sure you want to log out this session?')"
                                                >
                                                    {{ __('Logout') }}
                                                </x-button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-base-content/60">
                                <x-icon name="o-computer-desktop" class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                <p>{{ __('No active sessions found') }}</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Logout All Other Sessions --}}
                    @if(count($this->sessions) > 1)
                        @can('profile.update')
                            <div class="pt-6 border-t border-base-300">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="font-medium text-base-content">{{ __('Logout All Other Sessions') }}</h3>
                                        <p class="text-sm text-base-content/60 mt-1">
                                            {{ __('This will log you out of all other browser sessions. Your current session will remain active.') }}
                                        </p>
                                    </div>
                                    <x-button
                                        wire:click="logoutAllOtherSessions"
                                        wire:loading.attr="disabled"
                                        class="btn-error"
                                        icon="o-arrow-right-on-rectangle"
                                        :confirm="__('Are you sure you want to log out all other sessions?')"
                                    >
                                        <span wire:loading.remove wire:target="logoutAllOtherSessions">
                                            {{ __('Logout All Others') }}
                                        </span>
                                        <span wire:loading wire:target="logoutAllOtherSessions">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            {{ __('Logging out...') }}
                                        </span>
                                    </x-button>
                                </div>
                            </div>
                        @endcan
                    @endif
                </x-card>
            </div>

        </div>
    </div>

{{-- ========================================== --}}
{{-- ALPINE.JS COMPONENT --}}
{{-- ========================================== --}}
@script
<script>
    Alpine.data('profile', () => ({
        // Tab state (using URL hash for persistence)
        activeTab: window.location.hash.slice(1) || 'view',

        init() {
            // Update URL hash when tab changes
            this.$watch('activeTab', (value) => {
                window.location.hash = value;
            });

            // Listen for hash changes (browser back/forward)
            window.addEventListener('hashchange', () => {
                const hash = window.location.hash.slice(1);
                if (hash && ['view', 'general', 'details', 'social', 'address', 'password', 'image', 'sessions'].includes(hash)) {
                    this.activeTab = hash;
                }
            });
        }
    }));
</script>
@endscript
</div>
