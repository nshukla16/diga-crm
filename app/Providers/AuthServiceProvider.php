<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Auth\Grants\PinGrant;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
            
        Passport::routes();
        Passport::tokensExpireIn(Carbon::now()->addDays(7));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));

        app(AuthorizationServer::class)->enableGrantType(
            $this->makePinGrant(), Passport::tokensExpireIn()
        );
    }

    protected function makePinGrant()
    {
        $grant = new PinGrant(
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }
}
