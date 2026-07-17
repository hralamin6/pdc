<!DOCTYPE html>
<html
{{--    data-theme="light"--}}
    {{-- data-theme="dark" --}}
      lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

      @PwaHead
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
      <meta property="og:url" content="@yield('url', config('app.url'))" />
      <meta property="og:site_name" content="{{ setting('app.name', 'starter') }}" />

      @php(isset($title)?:$title=Str::title(str_replace(['.', '_'], ' ',  request()->route()->getName())))
      {{-- @dd($title); --}}
      <title>@yield('title', $title) - {{ setting('app.name', 'starter') }}</title>
      <meta property="og:title" content="@yield('title', $title) - {{ setting('app.name', 'starter') }}" />
      <meta name="twitter:title" content="@yield('title', $title) - {{ setting('app.name', 'starter') }}" />

      <meta name="description" content="@yield('description', setting('app.details', 'starter'))">
      <meta property="og:description" content="@yield('description', setting('app.details', 'starter'))" />
      <meta name="twitter:description" content="@yield('description', setting('app.details', 'starter'))" />

      <meta property="og:image:width" content="1536" />
      <meta property="og:image:height" content="1024" />
      <meta name="twitter:card" content="summary" />
      {{-- <link rel="shortcut icon" href="{{ url(asset('logo.png')) }}"> --}}
      <link rel="shortcut icon" href="@yield('image', getSettingImage('iconImage', 'icon'))">
      <meta property="og:image" content="@yield('image', getSettingImage('iconImage', 'icon'))" />
      <meta property="og:image:secure_url" content="@yield('image', getSettingImage('iconImage', 'icon'))" />
      <meta name="twitter:image" content="@yield('image', getSettingImage('iconImage', 'icon'))" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.1/dist/cropper.min.css">
        <script src="https://unpkg.com/cropperjs@1.6.1/dist/cropper.min.js"></script>
      @stack('styles')
      @stack('scripts')

      {{-- ========================================== --}}
      @if(auth()->check())
        <script>
          window.GlobalChatListener = window.GlobalChatListener || {
            channels: {},
            conversationIds: @json(auth()->user()->conversations->pluck('id')),
            initialized: false,

            init() {
              // Don't run on chat page (chat component handles its own listeners)
              if (this.isChatPage()) {
                console.log('⏭️ Skipping global listeners (on chat page)');
                this.cleanup();
                return;
              }

              // Prevent duplicate initialization
              if (this.initialized && Object.keys(this.channels).length > 0) {
                console.log('ℹ️ Global listeners already active');
                return;
              }

              console.log('🔌 Setting up global chat listeners');
              this.cleanup();
              this.subscribe();
              this.initialized = true;
            },

            isChatPage() {
              return window.location.pathname.includes('/chat');
            },

            cleanup() {
              Object.keys(this.channels).forEach(convId => {
                Echo.leave(`chat.${convId}`);
              });
              this.channels = {};
              this.initialized = false;
            },

            subscribe() {
              if (!window.Echo) {
                console.warn('⚠️ Echo not available');
                return;
              }

              this.conversationIds.forEach(convId => {
                this.channels[convId] = Echo.private(`chat.${convId}`)
                  .listenForWhisper('new-message', (e) => {
                    console.log(`📨 New message in conversation ${convId}`);
                    Livewire.dispatch('message-received');
                  });
              });
              console.log(`✅ Listening to ${this.conversationIds.length} conversations`);
            }
          };

          // Initialize on page load (once)
          if (!window.GlobalChatListener._listenersAttached) {
            document.addEventListener('livewire:initialized', () => {
              window.GlobalChatListener.init();
            });

            // Re-initialize after Livewire navigation
            document.addEventListener('livewire:navigated', () => {
              window.GlobalChatListener.init();
            });

            window.GlobalChatListener._listenersAttached = true;
          } else {
            // Already attached, just re-init
            window.GlobalChatListener.init();
          }
        </script>

      @endif

    </head>

    <body
    {{-- x-cloak x-data="{  theme: $persist(null)   }" --}}
        {{-- :data-theme="theme" --}}
        >
        @yield('body')
        <x-toast />
        
        {{-- Global AI Image Generator Drawer --}}
        <livewire:global-ai-image-generator />

        @RegisterServiceWorkerScript
    </body>

</html>
