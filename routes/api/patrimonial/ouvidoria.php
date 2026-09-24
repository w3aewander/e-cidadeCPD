<?php

use App\Domain\Patrimonial\Ouvidoria\Controller\Atendimento\AtendimentoJsonController;
use App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico\DocumentoTipoProcessoController;
use App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico\FornecedorController;
use App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico\ProcessoEletronicoController;
use App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico\TramitacaoExternaController;
use Illuminate\Support\Facades\Route;

Route::prefix('atendimento')
    ->group(function () {
        /**
         * Autenticação para usuarios
         */
        Route::middleware(["auth:api"])->group(function () {
            Route::prefix('atendimento')
                ->namespace('Atendimento')
                ->group(function () {
                    $controller = "AtendimentoController";
                    Route::get("/", "{$controller}@index");
                    Route::post('/buscarProcessosOuvidoria', "{$controller}@buscarProcessosOuvidoria");
                    Route::post('/consultaAtendimentos', "{$controller}@consultaAtendimentos");
                    Route::post('/buscarSolicitacaoOuvidoria', "{$controller}@buscarSolicitacaoOuvidoria");
                    Route::post(
                        '/buscarSolicitacaoOuvidoriaPorProcesso',
                        "{$controller}@buscarSolicitacaoOuvidoriaPorProcesso"
                    );
                    Route::middleware(["legacySession"])->group(function () use ($controller) {
                        Route::post('/aprovarProcessoOuvidoria', "{$controller}@aprovarProcessoOuvidoria");
                        Route::post('/rejeitarProcessoOuvidoria', "{$controller}@rejeitarProcessoOuvidoria");
                        Route::post('/existeInscricao', "{$controller}@existeInscricaoAlvara");
                    });

                    Route::get(
                        "/find-json/numero/{numero}/ano/{ano}/instituicao/{instituicao}",
                        [AtendimentoJsonController::class, "index"]
                    );
                });

            Route::prefix('atendimento-json')
                ->namespace('Atendimento')
                ->group(function () {
                    Route::get(
                        "/numero/{numero}/ano/{ano}",
                        "AtendimentoJsonController@index"
                    );
                    Route::put(
                        "/atendimento_id/{atendimento_id}",
                        "AtendimentoJsonController@update"
                    );
                });
        });

        /**
         * Autenticação para usuário ou aplicação
         */
        Route::middleware(["api", "clientCredential"])->group(function () {
            Route::prefix("processo-eletronico")
                ->namespace('ProcessoEletronico')
                ->group(function () {
                    $controller = "ProcessoEletronicoController";
                    Route::post("/buscar-mensagens", "{$controller}@mensagens");
                    Route::post("/mensagem", "{$controller}@mensagemOuvidoria");
                    Route::post("/savevisualizacao/{numero_processo}", "{$controller}@saveVisualizacao");
                    Route::get("/menu", "{$controller}@menu");
                    Route::post("/solicitacao-atendimento", "{$controller}@solicitacaoDeAtendimento");
                    Route::get("/servidor/cpf", "{$controller}@consultaServidor");
                    Route::get("/dependente/servidor/cpf", "{$controller}@consultarDependentesServidor");
                    Route::get(
                        "/servidor/{cpf}/tipoprocesso/{tipoProcesso}",
                        "{$controller}@verificaServidorPossuiPermissaoRecadastramento"
                    );
                    Route::get("/atendimento/{id}", "{$controller}@consultaAtendimentoPorId");
                    Route::post("/atendimentos", "{$controller}@consultaAtendimentosPorCpfCnpj");
                    Route::get("/detalhe-processo/{codigoProcesso}", "{$controller}@detalheProcesso");

                    Route::get(
                        "/persona/{cpfCnpj}",
                        "\\" . ProcessoEletronicoController::class . "@personasCpfCnpj"
                    );

                    Route::get(
                        "/formulario/{tipoProcesso}",
                        "\\" . ProcessoEletronicoController::class . "@formulario"
                    );

                    Route::get("processos/documentos/tipo", "\\". DocumentoTipoProcessoController::class . "@getDocumentosPorTipo");
                });

            Route::prefix("servidor")
                ->namespace('ProcessoEletronico')
                ->group(function () {
                    $controller = "ServidorController";
                    Route::get("matriculas/{cpf}", $controller . "@getMatriculas");
                    Route::get("assentamentos/{matricula}", $controller . "@getAssentamentos");
                    Route::get("averbacoes/{matricula}", $controller . "@getAverbacoes");
                    Route::get("ferias/{matricula}", $controller . "@getFerias");
                    Route::get("anos-trabalhados/{matricula}", $controller . "@getAnosTrabalhados");
                    Route::get("comprovante-irrf/{matricula}/ano/{ano}", $controller . "@getComprovanteIRRF");
                });

            Route::prefix("fornecedor")->group(function () {
                Route::get("notas", "\\" . FornecedorController::class . "@notas");
                Route::get("empenhos", "\\" . FornecedorController::class . "@empenhos");
            });

            Route::prefix("tramite-externo")->group(function () {
                Route::get(
                    "processo-tramite/codigo/{codigo}/cpf-cnpj/{cpf_cnpj}",
                    "\\" . TramitacaoExternaController::class . "@getProcessoComTramitacoes"
                );
                Route::get(
                    "tipos-documentos/cpf-cnpj/{cpf_cnpj}",
                    "\\" . TramitacaoExternaController::class . "@documentoTipos"
                );
                Route::post(
                    "listar-documentos",
                    "\\" . TramitacaoExternaController::class . "@listaDocumentos"
                );
            });
        });
    });
