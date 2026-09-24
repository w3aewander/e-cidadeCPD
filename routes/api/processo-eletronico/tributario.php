<?php

use App\Domain\ProcessoEletronico\Controllers\TributarioController;
use App\Domain\Tributario\Cadastro\Controllers\CadastroController;
use App\Domain\Tributario\ISSQN\Controller\AlvaraController;

Route::group([], function () {
    Route::post(
        '/imoveis',
        TributarioController::class . '@getImoveisByCPFCNPJ'
    )->name('process-eletronico.tributario.imoveis');

    Route::post(
        '/tipos_debitos',
        TributarioController::class . '@getTipoDebitosByValue'
    )->name('process-eletronico.tributario.debitos');

    Route::post(
        '/debitos',
        TributarioController::class . '@getDebitosByTipoDebito'
    )->name('process-eletronico.tributario.debitos');

    Route::post(
        '/emitir',
        TributarioController::class . '@emitirRecibo'
    )->name('process-eletronico.tributario.emitir');

    /**
     * Rotas para ALVARÁ dos forms do processo-eletronico
     */
    Route::get(
        '/portes',
        AlvaraController::class . '@getPortes'
    )->name('process-eletronico.tributario.portes');

    Route::get(
        '/iptu/matricula',
        CadastroController::class . '@getIptuMatricula'
    )->name('process-eletronico.tributario.iptumatricula');
});
