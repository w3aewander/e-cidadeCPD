<?php
// php5.6 artisan route:list --path=web/financeiro/tesouraria/
Route::prefix('slip')->group(function () {
    Route::prefix('gerar')->group(function () {
        Route::get('cobertura-recurso-extra', function () {
            return view('financeiro.tesouraria.slips.gerar.cobertura-recurso-extra');
        });
    });
});
