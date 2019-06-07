<?php

namespace App\Providers;

use App\Http\Models\Validators\TaxAdjustValidator;
use App\Http\Models\Validators\TaxCommitValidator;
use App\Http\Models\Validators\TaxEstimateValidator;
use App\Http\Models\Validators\TaxVoidValidator;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AuthService::class, function () {
            return new AuthService();
        });
        $this->app->bind(TaxAdjustValidator::class, function ($app) {
            return new TaxAdjustValidator(
                $app[TaxEstimateValidator::class]
            );
        });
        $this->app->bind(TaxCommitValidator::class, function ($app) {
            return new TaxCommitValidator(
                $app[TaxEstimateValidator::class]
            );
        });
        $this->app->bind(TaxEstimateValidator::class, function () {
            return new TaxEstimateValidator();
        });
        $this->app->bind(TaxVoidValidator::class, function () {
            return new TaxVoidValidator();
        });


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
