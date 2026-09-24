<?php

namespace App\Domain\Patrimonial\Protocolo\Controller\AnexosCgm;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Protocolo\Services\AnexosCgm\AnexosCgmService;
use App\Domain\Patrimonial\Protocolo\Requests\AnexosCgm\GetArquivoCgmRequest;
use App\Domain\Patrimonial\Protocolo\Requests\AnexosCgm\ProcessaArquivoRequest;
use App\Domain\Patrimonial\Protocolo\Requests\AnexosCgm\DeleteArquivoCgmRequest;
use App\Domain\Patrimonial\Protocolo\Requests\AnexosCgm\DownloadArquivoCgmRequest;
use App\Domain\Patrimonial\Protocolo\Requests\AnexosCgm\UpdateDadosArquivoCgmRequest;

class AnexosCgmController extends Controller
{

    private $anexosCgmService;

    public function __construct(AnexosCgmService $anexosCgmService)
    {
        $this->anexosCgmService = $anexosCgmService;
    }

    public function processarArquivo(ProcessaArquivoRequest $request)
    {
        return new DBJsonResponse($this->anexosCgmService->processarArquivo(
            $request->cgm,
            $request->desc,
            $request->obs,
            $request->user,
            $request->file
        ));
    }


    public function getArquivosCgm(GetArquivoCgmRequest $request)
    {
        return new DBJsonResponse($this->anexosCgmService->getArquivosCgm($request->cgm));
    }


    public function deleteArquivo(DeleteArquivoCgmRequest $request)
    {
        return new DBJsonResponse($this->anexosCgmService->deleteArquivo(
            $request->sequencial,
            $request->idArq,
            $request->idUser
        ));
    }

    public function updateDadosArquivo(UpdateDadosArquivoCgmRequest $request)
    {
        return new DBJsonResponse($this->anexosCgmService->updateDadosArquivo(
            $request->sequencial,
            $request->desc,
            $request->obs
        ));
    }

    public function downloadArquivo(DownloadArquivoCgmRequest $request)
    {

        return new DBJsonResponse($this->anexosCgmService->downloadArquivo($request->idArq));
    }
}
