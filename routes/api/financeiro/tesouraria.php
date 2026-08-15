<?php
// php5.6 artisan route:list --path=v4/api/financeiro/tesouraria
Route::prefix('')->group(function () {
    Route::post('importar', "ImportarArquivoTefController@store");
    Route::apiResource('processar', "ProcessarArquivoTefController");
    Route::post('inconsistente', "ProcessarArquivoTefController@registrarInconsistente");

    //
    Route::post('contas-pendentes', "ImplantacaoConciliacaoBancariaController@contasPendentes");
    Route::post('processar-implantacao', "ImplantacaoConciliacaoBancariaController@processarImplantacao");
    Route::post(
        'processar-implantacao-por-conta',
        "ImplantacaoConciliacaoBancariaController@processarImplantacao"
    );
});

Route::prefix('relatorio')->group(function () {
    Route::post('tef', "RelatorioArquivoTefController@tef");
    Route::post('ExtratoContaBancaria', 'RelatorioExtratoContaBancariaController@extratoContaBancaria');
});

Route::prefix('contatesouraria')->group(function () {
    Route::post('buscar', "ContaBancariaOutrosDadosController@buscar");
    Route::post('alterar', "ContaBancariaOutrosDadosController@alterar");
});

Route::prefix('slip')->group(function () {
    Route::prefix('cobertura-recurso-extra')->group(function () {
        Route::prefix('preparados')->group(function () {
            Route::get('empenho', 'SlipCoberturaRecursoExtraController@apropriacaoRetencao');
            Route::get('planilhas', 'SlipCoberturaRecursoExtraController@apropriacaoReceitaExtra');
        });

        Route::post('gerar', 'SlipCoberturaRecursoExtraController@gerar');
    });
});

Route::get('contas', "ContasController@index");
