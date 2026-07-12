<?php

namespace App\Providers;

use App\Listeners\AuthActivityListener;
use App\Models\User;
use App\Observers\GlobalActivityObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Ai;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Ai\Contracts\Gateway\Gateway;
use App\Ai\Providers\PollinationsProvider;
use Laravel\Ai\Gateway\Prism\PrismGateway;
use Livewire\Blaze\Blaze;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    //     if (app()->environment('production')) {
    //     URL::forceScheme('https');
    // }
        Blaze::optimize()
        ->in(resource_path('views/web'))
        ->in(resource_path('views/app'))
        ->in(resource_path('views/app/⚡ai-chat'), compile: false)
        ->in(resource_path('views/components'))
        // ->in(resource_path('views/layouts'), fold: true)
        ->in(resource_path('views/auth'))
        ->in(resource_path('views/vendor'))
        ;
        Paginator::defaultView('pagination::default');

        Paginator::defaultSimpleView('pagination::simple-default');

      try {
        if (\Schema::hasTable('settings')) {
          config([
            'app.name' => setting('site_name', config('app.name')),
            'mail.from.address' => setting('site_email', config('mail.from.address')),
            'mail.from.name' => setting('site_name', config('mail.from.name')),
          ]);

          // Dynamic AI SDK Config Overrides
          $aiProvidersJson = setting('ai_sdk.providers');
          if ($aiProvidersJson) {
              $storedProviders = json_decode($aiProvidersJson, true);
              if (is_array($storedProviders)) {
                  $formattedProviders = [];
                  foreach ($storedProviders as $key => $provider) {
                      if (!empty($provider['is_enabled'])) {
                          $formattedProviders[$key] = [
                              'driver' => $provider['driver'],
                              'key' => $provider['key'],
                          ];
                          if (!empty($provider['url'])) {
                              $formattedProviders[$key]['url'] = $provider['url'];
                          }
                      }
                  }
                  config(['ai.providers' => array_merge(config('ai.providers', []), $formattedProviders)]);
              }
          }

          $aiDefaultsJson = setting('ai_sdk.defaults');
          if ($aiDefaultsJson) {
              $storedDefaults = json_decode($aiDefaultsJson, true);
              if (is_array($storedDefaults)) {
                  config([
                      'ai.default' => $storedDefaults['default'] ?? config('ai.default'),
                      'ai.default_for_images' => $storedDefaults['default_for_images'] ?? config('ai.default_for_images'),
                      'ai.default_for_audio' => $storedDefaults['default_for_audio'] ?? config('ai.default_for_audio'),
                      'ai.default_for_transcription' => $storedDefaults['default_for_transcription'] ?? config('ai.default_for_transcription'),
                      'ai.default_for_embeddings' => $storedDefaults['default_for_embeddings'] ?? config('ai.default_for_embeddings'),
                      'ai.default_for_reranking' => $storedDefaults['default_for_reranking'] ?? config('ai.default_for_reranking'),
                  ]);
              }
          }
        }
      } catch (\Exception $e) {
        // ignore if during install
      }

      // Register Activity Observers and Listeners
//      User::observe(UserObserver::class);
      Event::subscribe(AuthActivityListener::class);

      // Register Global Activity Observer for ALL models using model events
      $this->registerGlobalActivityObserver();

      Ai::extend('pollinations', function ($app, $config) {
            return new PollinationsProvider(
                new PrismGateway($app['events']),
                $config,
                $app->make(Dispatcher::class)
            );
        });
    }

    /**
     * Register global activity observer for all models
     */
    protected function registerGlobalActivityObserver(): void
    {
        $observer = new GlobalActivityObserver();

        Event::listen('eloquent.created: *', function ($event, $models) use ($observer) {
            foreach ($models as $model) {
                if ($model instanceof Model) {
                    $observer->created($model);
                }
            }
        });

        Event::listen('eloquent.updated: *', function ($event, $models) use ($observer) {
            foreach ($models as $model) {
                if ($model instanceof Model) {
                    $observer->updated($model);
                }
            }
        });

        Event::listen('eloquent.deleted: *', function ($event, $models) use ($observer) {
            foreach ($models as $model) {
                if ($model instanceof Model) {
                    $observer->deleted($model);
                }
            }
        });
    }
}
