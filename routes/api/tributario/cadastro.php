<?php

use App\Domain\Tributario\Cadastro\Controllers\EnderecoController;
use App\Domain\Tributario\Cadastro\Controllers\CadastroController;

Route::middleware(["api", "clientCredential"])->group(function () {
        $path = "\App\Domain\Tributario\Cadastro\Controllers\\";

        Route::get("dados-matricula", "{$path}CadastroController@dadosImovel");
        Route::get("setor-registro-imoveis", "{$path}CadastroController@getSetorRegImoveis");
        Route::get("localidade-rural", "{$path}CadastroController@getLocalidadeRural");
        Route::get("bairros", "{$path}CadastroController@getBairros");
        Route::get("logradouros", "{$path}CadastroController@getLogradouros");
        Route::post("buscar-imoveis", "{$path}CadastroController@getListaImoveis");
        Route::get("buscar-labels-imoveis", "{$path}CadastroController@getLabelsListaImoveis");
        Route::get("endereco-base-cliente", "{$path}CadastroController@getEnderecoBaseCliente");
        Route::get("bairro-base-cliente", "{$path}CadastroController@getBairroBaseCliente");

        /**
         * Rotas para endereços dos forms do processo-eletronico
         */
        Route::get("paises", "\\". EnderecoController::class . "@getPaises");
        Route::get("estados", "\\". EnderecoController::class . "@getEstados");
        Route::get("cidades", "\\". EnderecoController::class . "@getCidades");
        Route::get("endereco-por-cep", "\\". EnderecoController::class . "@getEnderecoLocalidadeCep");
        Route::get("escritorios-contabeis", "\\". CadastroController::class . "@getEscritoriosContabeis");
        Route::get("atividade", "\\". CadastroController::class . "@getAtividadesPorTipo");
        Route::get("zonas", "\\". CadastroController::class . "@getZonas");
    });

    Route::middleware(["api", "clientCredential"])->group(function () {
        $path = "\App\Domain\Tributario\Cadastro\Controllers\\";

        Route::get("dados-isencao/{id}", "{$path}CadastroIsencaoIptuController@getDadosIsencao");
    });

    Route::middleware(["auth:api"])->group(function () {

       Route::prefix('configuracao')->group(function () {

        $path = "\App\Domain\Tributario\Cadastro\Controllers\\";
         Route::get("listar", "{$path}ParametrosController@listar");
         Route::post("salvar", "{$path}ParametrosController@salvar");
       });
    });
