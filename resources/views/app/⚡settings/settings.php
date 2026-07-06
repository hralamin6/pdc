<?php


use App\Models\Setting as SettingModel;
use EragLaravelPwa\Facades\PWA;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new
#[Title('Settings')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast;
    use WithFileUploads;

    // Tabs
    #[Url]
    public string $tab = 'general';

    // General
    public $photo; // TemporaryUploadedFile|null (optional site image)
    public string $image_url = '';
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $details = '';
    public string $placeHolder = '';

    // Mail
    public string $mailMailer = '';
    public string $mailHost = '';
    public string $mailPort = '';
    public string $mailUsername = '';
    public string $mailPassword = '';
    public string $mailEncryption = '';
    public string $mailFromAddress = '';
    public string $mailFromName = '';

    // Branding (logo/icon)
    public string $logo_url = '';
    public $logoImage; // TemporaryUploadedFile|null
    public string $logoImageUrl = '';
    public string $icon_url = '';
    public $iconImage; // TemporaryUploadedFile|null
    public string $iconImageUrl = '';

    // OAuth
    public string $githubClientId = '';
    public string $githubClientSecret = '';
    public string $googleClientId = '';
    public string $googleClientSecret = '';

    // App config
    public string $appName = '';
    public string $appEnv = '';
    public string $appDebug = '';
    public string $appUrl = '';
    public string $appLocale = '';
    public string $queueConnection = '';
    public string $appTimezone = '';

    // Pusher / WebPush
    public string $pusherAppId = '';
    public string $pusherAppKey = '';
    public string $pusherAppSecret = '';
    public string $pusherAppCluster = '';
    public string $pusherHost = '';
    public string $pusherPort = '';
    public string $pusherScheme = '';
    public string $vapidPublicKey = '';
    public string $vapidPrivateKey = '';

    // AI providers
    public string $openrouterApiKey = '';
    public string $openrouterBaseUrl = '';
    public string $geminiApiKey = '';
    public string $pollinationApiKey = '';
    public string $openaiApiKey = '';
    public string $openaiOrg = '';
    public string $openaiBaseUrl = '';
  public $output = '';
  public $selectedCommand = '';
  public $availableCommands = [];

  public function run()
  {
    $this->authorize('settings.run-commands');

    try {
      Artisan::call($this->selectedCommand);
      $this->output = Artisan::output();
      Log::info("Artisan command executed: {$this->selectedCommand}");
    } catch (\Throwable $e) {
      $this->output = 'Error: ' . $e->getMessage();
      Log::error('Artisan command failed', ['command' => $this->selectedCommand, 'error' => $e->getMessage()]);
    }
  }
    public function mount(): void
    {
      $this->authorize('settings.view');

      $this->availableCommands = collect([
        'cache:clear' => 'Clear Cache',
        'config:clear' => 'Clear Config Cache',
        'route:clear' => 'Clear Route Cache',
        'view:clear' => 'Clear Compiled Views',
        'optimize:clear' => 'Optimize Clear',
        'optimize' => 'Optimize Application',
        'migrate' => 'Run Migrations',
        'migrate:fresh' => 'Run Fresh Migrations',
        'migrate:fresh --seed' => 'Run Fresh Migrations with seed',
        'db:seed' => 'Run Seeder',
        'up' => 'Run Up',
        'down' => 'Run Down',
        'queue:restart' => 'Restart Queue Workers',
        'schedule:run' => 'Run Scheduled Tasks',
      ])->map(fn ($label, $key) => [
        'id' => $key,
        'name' => $label,
      ])->values()->toArray();
        // Load existing settings (falling back to .env values)
        $this->name = (string) (setting('app.name', env('APP_NAME', '')) ?? '');
        $this->email = (string) (setting('app.email', env('APP_EMAIL', '')) ?? '');
        $this->phone = (string) (setting('app.phone', env('APP_PHONE', '')) ?? '');
        $this->address = (string) (setting('app.address', env('APP_ADDRESS', '')) ?? '');
        $this->details = (string) (setting('app.details', env('APP_DETAILS', '')) ?? '');
        $this->placeHolder = (string) (setting('app.placeholder', env('APP_PLACEHOLDER', '')) ?? '');
        $this->image_url = (string) (setting('app.image_url', env('APP_IMAGE_URL', '')) ?? '');

        $this->mailMailer = (string) (setting('mail.mailer', env('MAIL_MAILER', '')) ?? '');
        $this->mailHost = (string) (setting('mail.host', env('MAIL_HOST', '')) ?? '');
        $this->mailPort = (string) (setting('mail.port', (string) env('MAIL_PORT', '')) ?? '');
        $this->mailUsername = (string) (setting('mail.username', env('MAIL_USERNAME', '')) ?? '');
        $this->mailPassword = (string) (setting('mail.password', env('MAIL_PASSWORD', '')) ?? '');
        $this->mailEncryption = (string) (setting('mail.encryption', (string) env('MAIL_ENCRYPTION', '')) ?? '');
        $this->mailFromAddress = (string) (setting('mail.from.address', env('MAIL_FROM_ADDRESS', '')) ?? '');
        $this->mailFromName = (string) (setting('mail.from.name', env('MAIL_FROM_NAME', '')) ?? '');

        $this->logo_url = (string) (setting('branding.logo_url', env('BRANDING_LOGO_URL', '')) ?? '');
        $this->icon_url = (string) (setting('branding.icon_url', env('BRANDING_ICON_URL', '')) ?? '');

        $this->githubClientId = (string) (setting('oauth.github.client_id', env('GITHUB_CLIENT_ID', '')) ?? '');
        $this->githubClientSecret = (string) (setting('oauth.github.client_secret', env('GITHUB_CLIENT_SECRET', '')) ?? '');
        $this->googleClientId = (string) (setting('oauth.google.client_id', env('GOOGLE_CLIENT_ID', '')) ?? '');
        $this->googleClientSecret = (string) (setting('oauth.google.client_secret', env('GOOGLE_CLIENT_SECRET', '')) ?? '');

        $this->appName = (string) (setting('app.name', env('APP_NAME', '')) ?? '');
        $this->appEnv = (string) (setting('app.env', env('APP_ENV', '')) ?? '');
        $this->appDebug = (string) (setting('app.debug', (string) (env('APP_DEBUG') ? 'true' : 'false')) ?? '');
        $this->appUrl = (string) (setting('app.url', env('APP_URL', '')) ?? '');
        $this->appLocale = (string) (setting('app.locale', env('APP_LOCALE', '')) ?? '');
        $this->queueConnection = (string) (setting('queue.connection', env('QUEUE_CONNECTION', '')) ?? '');
        $this->appTimezone = (string) (setting('app.timezone', env('APP_TIMEZONE', '')) ?? '');

        $this->pusherAppId = (string) (setting('pusher.app_id', env('PUSHER_APP_ID', '')) ?? '');
        $this->pusherAppKey = (string) (setting('pusher.key', env('PUSHER_APP_KEY', '')) ?? '');
        $this->pusherAppSecret = (string) (setting('pusher.secret', env('PUSHER_APP_SECRET', '')) ?? '');
        $this->pusherAppCluster = (string) (setting('pusher.cluster', env('PUSHER_APP_CLUSTER', '')) ?? '');
        $this->pusherHost = (string) (setting('pusher.host', env('PUSHER_HOST', '')) ?? '');
        $this->pusherPort = (string) (setting('pusher.port', (string) env('PUSHER_PORT', '')) ?? '');
        $this->pusherScheme = (string) (setting('pusher.scheme', env('PUSHER_SCHEME', '')) ?? '');
        $this->vapidPublicKey = (string) (setting('webpush.vapid.public_key', env('VAPID_PUBLIC_KEY', '')) ?? '');
        $this->vapidPrivateKey = (string) (setting('webpush.vapid.private_key', env('VAPID_PRIVATE_KEY', '')) ?? '');

        $this->openrouterApiKey = (string) (setting('ai.openrouter.api_key', env('OPENROUTER_API_KEY', '')) ?? '');
        $this->openrouterBaseUrl = (string) (setting('ai.openrouter.base_url', env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1')) ?? '');
        $this->geminiApiKey = (string) (setting('ai.gemini.api_key', env('GEMINI_API_KEY', '')) ?? '');
        $this->pollinationApiKey = (string) (setting('ai.pollination.api_key', env('POLLINATIONS_API_TOKEN', '')) ?? '');
        $this->openaiApiKey = (string) (setting('ai.openai.api_key', env('OPENAI_API_KEY', '')) ?? '');
        $this->openaiOrg = (string) (setting('ai.openai.org', env('OPENAI_ORG', '')) ?? '');
        $this->openaiBaseUrl = (string) (setting('ai.openai.base_url', env('OPENAI_BASE_URL', 'https://api.openai.com/v1')) ?? '');
    }

    public function saveGeneral(): void
    {
        $this->authorize('settings.update');

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'placeHolder' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
        ]);

        SettingModel::set('app.name', $this->name);
        SettingModel::set('app.email', $this->email);
        SettingModel::set('app.phone', $this->phone);
        SettingModel::set('app.address', $this->address);
        SettingModel::set('app.details', $this->details);
        SettingModel::set('app.placeholder', $this->placeHolder);
        SettingModel::set('app.image_url', $this->image_url);



        $this->success('General settings saved.', position: 'toast-bottom');
    }

    public function saveMail(): void
    {
        $this->authorize('settings.update');

        $this->validate([
            'mailMailer' => ['required', 'string', 'max:255'],
            'mailHost' => ['nullable', 'string', 'max:255'],
            'mailPort' => ['nullable', 'string', 'max:10'],
            'mailUsername' => ['nullable', 'string', 'max:255'],
            'mailPassword' => ['nullable', 'string', 'max:255'],
            'mailEncryption' => ['nullable', 'string', 'max:20'],
            'mailFromAddress' => ['nullable', 'email'],
            'mailFromName' => ['nullable', 'string', 'max:255'],
        ]);

        SettingModel::set('mail.mailer', $this->mailMailer);
        SettingModel::set('mail.host', $this->mailHost);
        SettingModel::set('mail.port', $this->mailPort);
        SettingModel::set('mail.username', $this->mailUsername);
        SettingModel::set('mail.password', $this->mailPassword);
        SettingModel::set('mail.encryption', $this->mailEncryption);
        SettingModel::set('mail.from.address', $this->mailFromAddress);
        SettingModel::set('mail.from.name', $this->mailFromName);

        // Persist to .env
        $this->writeEnv([
            'MAIL_MAILER' => $this->mailMailer,
            'MAIL_HOST' => $this->mailHost,
            'MAIL_PORT' => $this->mailPort,
            'MAIL_USERNAME' => $this->mailUsername,
            'MAIL_PASSWORD' => $this->mailPassword,
            'MAIL_ENCRYPTION' => $this->mailEncryption,
            'MAIL_FROM_ADDRESS' => $this->mailFromAddress,
            'MAIL_FROM_NAME' => $this->mailFromName,
        ]);

        $this->success('Mail settings saved.', position: 'toast-bottom');
    }

    public function saveOauth(): void
    {
        $this->authorize('settings.update');

        $this->validate([
            'githubClientId' => ['nullable', 'string', 'max:255'],
            'githubClientSecret' => ['nullable', 'string', 'max:255'],
            'googleClientId' => ['nullable', 'string', 'max:255'],
            'googleClientSecret' => ['nullable', 'string', 'max:255'],
        ]);

        SettingModel::set('oauth.github.client_id', $this->githubClientId);
        SettingModel::set('oauth.github.client_secret', $this->githubClientSecret);
        SettingModel::set('oauth.google.client_id', $this->googleClientId);
        SettingModel::set('oauth.google.client_secret', $this->googleClientSecret);

        // Persist to .env
        $this->writeEnv([
            'GITHUB_CLIENT_ID' => $this->githubClientId,
            'GITHUB_CLIENT_SECRET' => $this->githubClientSecret,
            'GOOGLE_CLIENT_ID' => $this->googleClientId,
            'GOOGLE_CLIENT_SECRET' => $this->googleClientSecret,
        ]);

        $this->success('OAuth settings saved.', position: 'toast-bottom');
    }

    public function savePusher(): void
    {
        $this->authorize('settings.update');

        $this->validate([
            'pusherAppId' => ['nullable', 'string', 'max:255'],
            'pusherAppKey' => ['nullable', 'string', 'max:255'],
            'pusherAppSecret' => ['nullable', 'string', 'max:255'],
            'pusherAppCluster' => ['nullable', 'string', 'max:255'],
            'pusherHost' => ['nullable', 'string', 'max:255'],
            'pusherPort' => ['nullable', 'string', 'max:10'],
            'pusherScheme' => ['nullable', 'string', 'max:10'],
            'vapidPublicKey' => ['nullable', 'string'],
            'vapidPrivateKey' => ['nullable', 'string'],
        ]);

        SettingModel::set('pusher.app_id', $this->pusherAppId);
        SettingModel::set('pusher.key', $this->pusherAppKey);
        SettingModel::set('pusher.secret', $this->pusherAppSecret);
        SettingModel::set('pusher.cluster', $this->pusherAppCluster);
        SettingModel::set('pusher.host', $this->pusherHost);
        SettingModel::set('pusher.port', $this->pusherPort);
        SettingModel::set('pusher.scheme', $this->pusherScheme);
        SettingModel::set('webpush.vapid.public_key', $this->vapidPublicKey);
        SettingModel::set('webpush.vapid.private_key', $this->vapidPrivateKey);

        // Persist to .env
        $this->writeEnv([
            'PUSHER_APP_ID' => $this->pusherAppId,
            'PUSHER_APP_KEY' => $this->pusherAppKey,
            'PUSHER_APP_SECRET' => $this->pusherAppSecret,
            'PUSHER_APP_CLUSTER' => $this->pusherAppCluster,
            'PUSHER_HOST' => $this->pusherHost,
            'PUSHER_PORT' => $this->pusherPort,
            'PUSHER_SCHEME' => $this->pusherScheme,
            'VAPID_PUBLIC_KEY' => $this->vapidPublicKey,
            'VAPID_PRIVATE_KEY' => $this->vapidPrivateKey,
        ]);

        $this->success('Pusher/WebPush settings saved.', position: 'toast-bottom');
    }

    public function saveAi(): void
    {
        $this->authorize('settings.update');

        $this->validate([
            'openrouterApiKey' => ['nullable', 'string'],
            'openrouterBaseUrl' => ['nullable', 'url'],
            'geminiApiKey' => ['nullable', 'string'],
            'pollinationApiKey' => ['nullable', 'string'],
            'openaiApiKey' => ['nullable', 'string'],
            'openaiOrg' => ['nullable', 'string'],
            'openaiBaseUrl' => ['nullable', 'url'],
        ]);

        SettingModel::set('ai.openrouter.api_key', $this->openrouterApiKey);
        SettingModel::set('ai.openrouter.base_url', $this->openrouterBaseUrl);
        SettingModel::set('ai.gemini.api_key', $this->geminiApiKey);
        SettingModel::set('ai.pollination.api_key', $this->pollinationApiKey);
        SettingModel::set('ai.openai.api_key', $this->openaiApiKey);
        SettingModel::set('ai.openai.org', $this->openaiOrg);
        SettingModel::set('ai.openai.base_url', $this->openaiBaseUrl);

        // Persist to .env
        $this->writeEnv([
            'OPENROUTER_API_KEY' => $this->openrouterApiKey,
            'OPENROUTER_BASE_URL' => $this->openrouterBaseUrl,
            'GEMINI_API_KEY' => $this->geminiApiKey,
            'POLLINATIONS_API_TOKEN' => $this->pollinationApiKey,
            'OPENAI_API_KEY' => $this->openaiApiKey,
            'OPENAI_ORG' => $this->openaiOrg,
            'OPENAI_BASE_URL' => $this->openaiBaseUrl,
        ]);

        $this->success('AI settings saved.', position: 'toast-bottom');
    }

    public function saveBranding(): void
    {
        $this->authorize('settings.update');

        // Upload optional images, or save URLs if provided
        $this->validate([
            'logoImage' => ['nullable', 'image', 'max:10240'],
            'iconImage' => ['nullable', 'image', 'max:5120'],
            'logoImageUrl' => ['nullable', 'url'],
            'iconImageUrl' => ['nullable', 'url'],
        ]);


        $logo = SettingModel::set('logoImage', 'logo_url');
        $icon = SettingModel::set('iconImage', 'icon_url');

        // Handle media uploads if images are present
        if ($this->logoImage) {
            $extension = pathinfo(parse_url($this->logoImage, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $logo->clearMediaCollection('logo');
            $logo->addMedia($this->logoImage->getRealPath())
                ->usingFileName($logo->key . now()->timestamp .'.' . $extension)
                ->toMediaCollection('logo');
        }elseif($this->logoImageUrl){
            if(checkImageUrl($this->logoImageUrl)){
                
$extension = pathinfo(parse_url($this->logoImageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $icon->clearMediaCollection('logo');

                $logo->addMediaFromUrl($this->logoImageUrl)
                    ->usingFileName($logo->key . now()->timestamp . '.' . $extension)
                    ->toMediaCollection('logo');
            }else{
                        $this->error('Invalid image url.', position: 'toast-bottom');
            }

        }

        if ($this->iconImage) {
            $extension = pathinfo(parse_url($this->iconImage, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

            copy($this->iconImage->getRealPath(), public_path('logo.png'));
            $icon->clearMediaCollection('icon');
            $icon->addMedia($this->iconImage->getRealPath())
                ->usingFileName($icon->key . now()->timestamp . '.' . $extension)
                ->toMediaCollection('icon');

        }elseif($this->iconImageUrl){
            if(checkImageUrl($this->iconImageUrl)){
                $extension = pathinfo(parse_url($this->iconImage, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                copy($this->iconImageUrl, public_path('logo.png'));
                $icon->clearMediaCollection('icon');
                $icon->addMediaFromUrl($this->iconImageUrl)
                    ->usingFileName($icon->key . now()->timestamp . '.' . $extension)
                    ->toMediaCollection('icon');
            }else{
                        $this->error('Invalid image url.', position: 'toast-bottom');
                        return;
            }
        }

        // clear file inputs

        // Update PWA manifest with new branding
        if($this->iconImage || checkImageUrl($this->iconImageUrl)){
        PWA::update([
            'name' => setting('app.name', 'Laravel PWA'),
            'short_name' => substr(setting('app.name', 'LP'), 0, 12),
            'background_color' => '#6777ef',
            'display' => 'fullscreen',
            'description' => setting('app.details', 'A Progressive Web Application setup for Laravel projects.'),
            'theme_color' => '#6777ef',
            'icons' => [
                [
                    'src' => getSettingImage('iconImage', 'icon', 'icon'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ]);
        }
                $this->reset(['logoImage', 'iconImage',  'logoImageUrl', 'iconImageUrl']);

        $this->success('Branding images saved.', position: 'toast-bottom');
    }

    public function saveApp(): void
    {
        $this->authorize('settings.update');

        $this->validate([
            'appName' => ['required', 'string', 'max:255'],
            'appEnv' => ['nullable', 'string', 'max:50'],
            'appDebug' => ['nullable', 'string', 'in:true,false,0,1'],
            'appUrl' => ['nullable', 'url'],
            'appLocale' => ['nullable', 'string', 'max:10'],
            'queueConnection' => ['nullable', 'string', 'max:50'],
            'appTimezone' => ['nullable', 'string', 'max:64'],
        ]);

        SettingModel::set('app.name', $this->appName);
        SettingModel::set('app.env', $this->appEnv);
        SettingModel::set('app.debug', $this->appDebug);
        SettingModel::set('app.url', $this->appUrl);
        SettingModel::set('app.locale', $this->appLocale);
        SettingModel::set('queue.connection', $this->queueConnection);
        SettingModel::set('app.timezone', $this->appTimezone);

        // Persist to .env
        $this->writeEnv([
            'APP_NAME' => $this->appName,
            'APP_ENV' => $this->appEnv,
            'APP_DEBUG' => $this->appDebug,
            'APP_URL' => $this->appUrl,
            'APP_LOCALE' => $this->appLocale,
            'QUEUE_CONNECTION' => $this->queueConnection,
            'APP_TIMEZONE' => $this->appTimezone,
        ]);

        $this->success('App settings saved.', position: 'toast-bottom');
    }

    /**
     * Safely write/update keys in the .env file.
     */
    private function writeEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        if (!is_file($envPath) || !is_readable($envPath) || !is_writable($envPath)) {
            return;
        }

        $contents = file_get_contents($envPath) ?: '';

        foreach ($pairs as $key => $value) {
            $key = strtoupper($key);
            $formatted = $this->formatEnvValue($value);

            $pattern = '/^' . preg_quote($key, '/') . '\s*=.*$/m';
            $line = $key . '=' . $formatted;

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $line, $contents);
            } else {
                $contents = rtrim($contents, "\n") . PHP_EOL . $line . PHP_EOL;
            }
        }

        file_put_contents($envPath, $contents);
    }

    private function formatEnvValue($value): string
    {
        $value = (string) ($value ?? '');
        if ($value === '' || preg_match('/\s|["\'#=]/', $value)) {
            $value = str_replace('"', '\"', $value);
            return "\"{$value}\"";
        }
        return $value;
    }
    
};
