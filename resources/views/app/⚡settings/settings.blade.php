<div class="space-y-6">
  <x-header :title="__('Settings')" :subtitle="__('Configure your application, integrations, and branding.')" separator />

  <div class="grid lg:grid-cols-4 gap-6 px-0 mx-0">

    <div class="lg:col-span-1 space-y-6">
      <x-card class="p-5">
        <div class="flex items-center gap-4"> 
          @php($logo = $logo_url ?: getSettingImage('iconImage', 'icon'))
          <x-avatar :image="$logo" alt="App" class="w-16 h-16 ring-2 ring-primary/20" />
          <div>
            <div class="font-semibold text-base-content/90">{{ $appName ?: $name ?: config('app.name') }}</div>
            <div class="text-sm text-base-content/60">{{ $appEnv ?: config('app.env') }}</div>
          </div>
        </div>
      </x-card>

      <x-menu class="bg-base-100 rounded-lg shadow">
        <x-menu-item :title="__('General')" icon="o-cog-6-tooth" wire:click="$set('tab', 'general')" :active="$tab === 'general'" />
        <x-menu-item :title="__('Mail')" icon="o-envelope" wire:click="$set('tab', 'mail')" :active="$tab === 'mail'" />
        <x-menu-item :title="__('OAuth')" icon="o-shield-check" wire:click="$set('tab', 'oauth')" :active="$tab === 'oauth'" />
        <x-menu-item :title="__('Pusher')" icon="o-bell" wire:click="$set('tab', 'pusher')" :active="$tab === 'pusher'" />
        <x-menu-item :title="__('AI')" icon="o-sparkles" wire:click="$set('tab', 'ai')" :active="$tab === 'ai'" />
        <x-menu-item :title="__('Image & Branding')" icon="o-photo" wire:click="$set('tab', 'image')" :active="$tab === 'image'" />
        <x-menu-item :title="__('App')" icon="o-wrench-screwdriver" wire:click="$set('tab', 'app')" :active="$tab === 'app'" />
      </x-menu>
    </div>

    <div class="lg:col-span-3 space-y-6">
      @if($tab === 'general')
        <x-card>
          <x-header :title="__('General')" :subtitle="__('Basic organization and display settings.')" class="mb-5" />
          <form wire:submit="saveGeneral" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('App/Org Name')" icon="o-building-office" wire:model.defer="name" required :placeholder="__('My Company')" />
              <x-input :label="__('Contact Email')" icon="o-envelope" wire:model.defer="email" type="email" :placeholder="__('support@example.com')" />
              <x-input :label="__('Phone')" icon="o-phone" wire:model.defer="phone" :placeholder="__('+1 555 000 111')" />
              <x-input :label="__('Website/App URL')" icon="o-link" wire:model.defer="appUrl" type="url" :placeholder="__('https://example.com')" />
            </div>

            <x-input :label="__('Hero/Image URL')" icon="o-photo" wire:model.defer="image_url" type="url" :placeholder="__('https://.../cover.jpg')" />

            <x-textarea :label="__('Address')" wire:model.defer="address" rows="3" :placeholder="__('Street, City, Country')" />
            <x-editor wire:model.defer="details" :label="__('Description')" :hint="__('The full description')" :config="config('editor.default')" />
            <x-textarea :label="__('Placeholder Text')" wire:model.defer="placeHolder" rows="2" :placeholder="__('Shown as placeholder across UI')" />

            @can('settings.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="saveGeneral" class="btn-primary" icon="o-check">{{ __('Save') }}</x-button>
                <x-button type="button" class="btn-ghost" icon="o-arrow-path" wire:click="$refresh">{{ __('Reset') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'mail')
        <x-card>
          <x-header :title="__('Mail')" :subtitle="__('Outgoing email settings.')" class="mb-5" />
          <form wire:submit="saveMail" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('Mailer')" wire:model.defer="mailMailer" :placeholder="__('smtp')" />
              <x-input :label="__('Host')" wire:model.defer="mailHost" :placeholder="__('smtp.mailtrap.io')" />
              <x-input :label="__('Port')" wire:model.defer="mailPort" :placeholder="__('587')" />
              <x-input :label="__('Username')" wire:model.defer="mailUsername" />
              <x-input :label="__('Password')" wire:model.defer="mailPassword" type="password" />
              <x-input :label="__('Encryption')" wire:model.defer="mailEncryption" :placeholder="__('tls')" />
              <x-input :label="__('From Address')" wire:model.defer="mailFromAddress" type="email" />
              <x-input :label="__('From Name')" wire:model.defer="mailFromName" />
            </div>
            @can('settings.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="saveMail" class="btn-primary" icon="o-check">{{ __('Save') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'oauth')
        <x-card>
          <x-header :title="__('OAuth')" :subtitle="__('Social providers credentials.')" class="mb-5" />
          <form wire:submit="saveOauth" class="space-y-5">
            <x-header :title="__('GitHub')" level="3" class="mb-2" />
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('Client ID')" wire:model.defer="githubClientId" />
              <x-input :label="__('Client Secret')" wire:model.defer="githubClientSecret" type="password" />
            </div>
            <x-hr class="my-4" />
            <x-header :title="__('Google')" level="3" class="mb-2" />
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('Client ID')" wire:model.defer="googleClientId" />
              <x-input :label="__('Client Secret')" wire:model.defer="googleClientSecret" type="password" />
            </div>
            @can('settings.update')
              <div class="flex gap-2 mt-4">
                <x-button type="submit" spinner="saveOauth" class="btn-primary" icon="o-check">{{ __('Save') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'pusher')
        <x-card>
          <x-header :title="__('Pusher & WebPush')" :subtitle="__('Realtime broadcasting and push.')" class="mb-5" />
          <form wire:submit="savePusher" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('App ID')" wire:model.defer="pusherAppId" />
              <x-input :label="__('Key')" wire:model.defer="pusherAppKey" />
              <x-input :label="__('Secret')" wire:model.defer="pusherAppSecret" type="password" />
              <x-input :label="__('Cluster')" wire:model.defer="pusherAppCluster" :placeholder="__('mt1')" />
              <x-input :label="__('Host')" wire:model.defer="pusherHost" />
              <x-input :label="__('Port')" wire:model.defer="pusherPort" />
              <x-input :label="__('Scheme')" wire:model.defer="pusherScheme" :placeholder="__('https')" />
            </div>
            <x-hr class="my-4" />
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('VAPID Public Key')" wire:model.defer="vapidPublicKey" />
              <x-input :label="__('VAPID Private Key')" wire:model.defer="vapidPrivateKey" type="password" />
            </div>
            @can('settings.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="savePusher" class="btn-primary" icon="o-check">{{ __('Save') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'ai')
        <x-card>
          <x-header :title="__('AI Providers')" :subtitle="__('API credentials for AI services.')" class="mb-5" />
          <form wire:submit="saveAi" class="space-y-6">
            <div>
              <x-header :title="__('OpenRouter')" level="3" class="mb-2" />
              <div class="grid md:grid-cols-2 gap-4">
                <x-input :label="__('API Key')" wire:model.defer="openrouterApiKey" type="password" />
                <x-input :label="__('Base URL')" wire:model.defer="openrouterBaseUrl" :placeholder="__('https://openrouter.ai/api/v1')" />
              </div>
            </div>
            <div>
              <x-header :title="__('Gemini')" level="3" class="mb-2" />
              <x-input :label="__('API Key')" wire:model.defer="geminiApiKey" type="password" />
            </div>
            <div>
              <x-header :title="__('Pollination')" level="3" class="mb-2" />
              <x-input :label="__('API Key')" wire:model.defer="pollinationApiKey" type="password" />
            </div>
            <div>
              <x-header :title="__('OpenAI')" level="3" class="mb-2" />
              <div class="grid md:grid-cols-2 gap-4">
                <x-input :label="__('API Key')" wire:model.defer="openaiApiKey" type="password" />
                <x-input :label="__('Organization')" wire:model.defer="openaiOrg" />
                <x-input :label="__('Base URL')" wire:model.defer="openaiBaseUrl" :placeholder="__('https://api.openai.com/v1')" class="md:col-span-2" />
              </div>
            </div>
            @can('settings.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="saveAi" class="btn-primary" icon="o-check">{{ __('Save') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

      @if($tab === 'image')
        <x-card class="relative">
          <div wire:loading.flex wire:target="logoImage,iconImage,saveBranding" class="absolute inset-0 z-10 bg-base-100/70 backdrop-blur-sm items-center justify-center rounded-lg">
            <div class="flex items-center gap-3">
              <x-loading class="loading-spinner loading-lg text-primary" />
              <span class="text-sm">{{ __('Processing images...') }}</span>
            </div>
          </div>

          <x-header :title="__('Branding')" :subtitle="__('Logo and app icon.')" class="mb-5" />

          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <x-file :label="__('Upload Logo')" wire:model="logoImage" accept="image/*" crop-after-change>
                <x-avatar :image="$logoImage?->temporaryUrl() ?? getSettingImage('logoImage', 'logo')" alt="Logo" class="w-24 h-24 ring-4 ring-primary/20" />
              </x-file>
              <x-input :label="__('Logo URL')" wire:model.defer="logoImageUrl" type="url" :placeholder="__('https://.../logo.png')" />
            </div>
            <div class="space-y-3">
              <x-file :label="__('Upload Icon')" wire:model="iconImage" accept="image/*" crop-after-change>
                <x-avatar :image="$iconImage?->temporaryUrl() ?? getSettingImage('iconImage', 'icon')" alt="Icon" class="w-24 h-24 ring-4 ring-primary/20" />
              </x-file>
              <x-input :label="__('Icon URL')" wire:model.defer="iconImageUrl" type="url" :placeholder="__('https://.../icon.png')" />
            </div>
          </div>

          @can('settings.update')
            <div class="mt-4 flex flex-wrap gap-2">
              <x-button class="btn-primary" icon="o-check" wire:click="saveBranding" spinner="saveBranding">{{ __('Save images') }}</x-button>
              <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('logoImage', null); $set('iconImage', 'icon', null); $set('logoImageUrl', ''); $set('iconImageUrl', '')">{{ __('Clear') }}</x-button>
            </div>
          @endcan
        </x-card>
      @endif

      @if($tab === 'app')
        <x-card>
          <x-header :title="__('Application')" :subtitle="__('Core app configuration (stored in DB).')" class="mb-5" />
          <form wire:submit="saveApp" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input :label="__('App Name')" wire:model.defer="appName" />
              <x-input :label="__('Environment')" wire:model.defer="appEnv" :placeholder="__('production')" />
              <x-select :label="__('Debug')" wire:model.defer="appDebug" :options="[['id'=>'true','name'=>'true'],['id'=>'false','name'=>'false']]" :placeholder="__('Select')" />
              <x-input :label="__('App URL')" wire:model.defer="appUrl" type="url" />
              <x-input :label="__('Locale')" wire:model.defer="appLocale" :placeholder="__('en')" />
              <x-input :label="__('Timezone')" wire:model.defer="appTimezone" :placeholder="__('UTC')" />
              <x-input :label="__('Queue Connection')" wire:model.defer="queueConnection" :placeholder="__('sync')" />
            </div>
            @can('settings.update')
              <div class="flex gap-2">
                <x-button type="submit" spinner="saveApp" class="btn-primary" icon="o-check">{{ __('Save') }}</x-button>
              </div>
            @endcan
          </form>
        </x-card>
      @endif

    </div>

  </div>

  @can('settings.run-commands')
    <div class="space-y-6">
      <x-card :title="__('⚙️ Artisan Command Runner')" class="shadow-xl">

        <form wire:submit="run" class="space-y-4">

          <div class="flex flex-col md:flex-row md:items-end gap-3">

            <div class="flex-grow">
              <x-select
                :label="__('Select Command')"
                wire:model.live="selectedCommand"
                :options="$availableCommands"
                :placeholder="__('Choose a command to execute...')"
              />
            </div>

            <div>
              <x-button
                type="submit"
                spinner="run"
                class="w-full md:w-auto"
                icon-right="o-play"
                :disabled="!$selectedCommand"
              >
                {{ __('Run Command') }}
              </x-button>
            </div>
          </div>
        </form>

        <x-hr />

        <div>
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-sm font-semibold">{{ __('Command Output') }}</h3>
            <x-button
              wire:click="$set('output', null)"
              icon="o-x-mark"
              class="btn-xs btn-ghost"
              :disabled="!$output"
            />
          </div>

          <div
            @class([
                'p-4 rounded-lg font-mono text-sm whitespace-pre-wrap min-h-[150px] transition-all',
                'bg-gray-800 text-gray-400' => !$output,
                'bg-gray-900 text-green-400' => $output,
            ])
          >
            <div wire:loading.flex wire:target="run" class="items-center opacity-75">
              <x-loading class="loading-spinner loading-xs mr-2" />
              {{ __('Executing') }} `{{ $selectedCommand }}`...
            </div>

            <div wire:loading.remove wire:target="run">
              @if ($output)
                {!! nl2br(e($output)) !!}
              @else
                <span class="opacity-50">$ {{ __('Output will appear here...') }}</span>
              @endif
            </div>
          </div>
        </div>
      </x-card>
    </div>
  @endcan
  @assets
        <script src="{{ asset('tiny.js') }}"></script>
       <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.1/dist/cropper.min.css">
       <script defer src="https://unpkg.com/cropperjs@1.6.1/dist/cropper.min.js"></script>
     {{-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script> --}}
  @endassets
</div>

