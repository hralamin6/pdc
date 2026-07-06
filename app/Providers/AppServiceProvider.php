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
