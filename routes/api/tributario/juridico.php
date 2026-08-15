<?php

use Illuminate\Support\Facades\Route;

$path = "\App\Domain\Tributario\Juridico\Controllers\\";

Route::prefix('anulacaocda')
->middleware(["api", "auth:api"])
->group(function ()  use ($path)  {
    Route::post("pesquisar", $path."AnulacaoCDAPorListaController@verificaLista");
    Route::post("processar", $path."AnulacaoCDAPorListaController@processar");
    Route::get("getprocessamento", $path."AnulacaoCDAPorListaController@getprocessamento");
});

Route::prefix('inclusaoiniciallista')
->middleware(["api", "auth:api"])
->group(function ()  use ($path)  {
    Route::post("pesquisar", $path."IncluirInicialListaController@verificaLista");
    Route::post("processar", $path."IncluirInicialListaController@processar");
    Route::get("getprocessamento", $path."IncluirInicialListaController@getprocessamento");
    Route::post("gethistorico", $path."IncluirInicialListaController@gethistorico");
});

Route::prefix('anexosparamn')
->middleware(["api", "auth:api"])
->group(function() use ($path) {
    Route::post("upload", $path."AnexoParametrosController@saveArquivo");
    Route::post("download", $path."AnexoParametrosController@downloadArquivo");
    Route::post("get-arquivos", $path."AnexoParametrosController@getArquivos");
});

Route::prefix('paramneproc')
->middleware(["api", "auth:api"])
->group(function() use ($path) {
    Route::post("save-conf-webservice", $path."ParametrosEprocController@saveConfWebservice");
    Route::post("save-conf-proxy", $path."ParametrosEprocController@saveConfProxy");
    Route::post("save-conf-api", $path."ParametrosEprocController@saveConfApi");
    Route::post("save-conf", $path."ParametrosEprocController@saveConfAll");
    Route::post("get-config-integrador", $path."ParametrosEprocController@getConfIntegrador");

    Route::post("save-conf-ajuizamento", $path."ParametrosEprocController@saveConfAjuizamento");
    Route::post("get-config-ajuizamento", $path."ParametrosEprocController@getConfAjuizamento");

    Route::post("save-conf-processo", $path."ParametrosEprocController@saveConfProcesso");
    Route::post("get-config-processo", $path."ParametrosEprocController@getConfProcesso");
});

Route::prefix('documentos-eproc')
->middleware(["auth:api"])
->namespace('Juridico\Controllers')
->group(function() use ($path) {
    Route::get("/", "DocumentoEprocController@all");
    Route::delete("/{id}", "DocumentoEprocController@delete")->where('id', '[0-9]+');
    Route::post("/", "DocumentoEprocController@save");
    Route::get('/cnj', 'DocumentoEprocController@getDocumentosCNJ');
});


Route::prefix('documentos-eproc')
->middleware(["auth:api"])
->namespace('Juridico\Controllers')
->group(function() use ($path) {
    Route::get("/", "DocumentoEprocController@all");
    Route::delete("/{id}", "DocumentoEprocController@delete")->where('id', '[0-9]+');
    Route::post("/", "DocumentoEprocController@save");
    Route::get('/cnj', 'DocumentoEprocController@getDocumentosCNJ');
});
