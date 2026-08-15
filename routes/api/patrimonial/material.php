<?php
// php5.6 artisan route:list --path=v4/api/patrimonial/material

Route::middleware("auth:api")->prefix('relatorios')->group(function () {
    Route::post("controle-estoque", "RelatoriosController@controleEstoque");
    Route::post('rastreabilidade', 'RelatoriosController@rastreabilidadeMaterial');
    Route::post("resumo-contabil-estoque", "RelatoriosController@resumoContabilEstoque");
});

Route::middleware(['auth:api'])->prefix('consulta')->group(function () {
    Route::get('fabricante/{id}', 'FabricantesController@get');
});

Route::middleware(['auth:api'])->prefix('implantacao')->group(function () {
    Route::post('importar-planilha', 'ImplantacaoSaldoPlanilhaController@importar');
});
