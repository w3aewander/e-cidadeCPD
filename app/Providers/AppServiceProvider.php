<?php

namespace App\Providers;

use App\QueryLogger;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(LC_TIME, config('app.locale'));

        if (PHP_VERSION_ID < 74000) {
            setlocale(
                LC_TIME,
                [
                    'ptb',
                    'pt_BR',
                    'portuguese-brazil',
                    'portuguese-brazilian',
                    'bra',
                    'brazil',
                    'br',
                ]
            );
        }

        DB::listen(function ($query) {
            if (!\TraceLog::getInstance()->isActive()) {
                return;
            }

            $sql = $query->sql;
            foreach ($query->bindings as $binding) {
                if ($binding instanceof \DateTime) {
                    $binding = $binding->format('Y-m-d H:i:s');
                }
                $sql = preg_replace('/\?/', $binding, $sql, 1);
            }

            QueryLogger::logQuery($sql, $query->bindings, $query->time);
        });

        Event::listen(QueryException::class, function ($e) {
            if (!\TraceLog::getInstance()->isActive()) {
                return;
            }

            $sql = $e->getSql();
            $bindings = $e->getBindings();
            foreach ($bindings as $binding) {
                if ($binding instanceof \DateTime) {
                    $binding = $binding->format('Y-m-d H:i:s');
                }
                $sql = preg_replace('/\?/', $binding, $sql, 1);
            }

            QueryLogger::logQueryError($sql, $bindings);
        });
    }

    /**
     * @throws Exception
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }
}
