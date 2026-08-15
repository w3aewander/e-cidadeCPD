<?php

use App\Domain\Patrimonial\Protocolo\Controller\Processo\ProtocoloDocumentoController;
use Illuminate\Support\Facades\Route;
use \Illuminate\Support\Facades\Request;

Route::get('atendimento-ajustar-json', function (Request $request) {
    return view("patrimonial.protocolo.atendimento-ajustar-json");
});
Route::get('documento-andamento-manutencao-atividade', function () {
    return view("patrimonial.protocolo.andamento-documento.manutencao-atividade");
});

Route::get('documentos', function (Request $request) {
    return view("patrimonial.protocolo.documento");
});

Route::get('mensageria-protocolo/{processo}', function ($processo) {
    return view("patrimonial.protocolo.mensageria-protocolo", compact('processo'));
});

Route::get(
    "solicitacao-assinatura/processo/{codigoProcesso}/despacho/{codigoDespacho}",
    function ($codigoProcesso, $codigoDespacho) {
        $data["codigoProcesso"] = $codigoProcesso;
        $data["codigoDespacho"] = $codigoDespacho;
        return view(
            "patrimonial.protocolo.solicita-assinatura",
            $data
        );
    }
);

Route::get(
    "/capa-preview",
    "\\" .
    ProtocoloDocumentoController::class
    . "@layoutCapaPreview"
);

Route::get('consulta-atendimento', function () {
    return view("patrimonial.protocolo.consulta-atendimento");
});
