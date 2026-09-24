<?php

namespace App\Domain\Financeiro\Contabilidade\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Financeiro\Contabilidade\Requests\ImportarDdrRequest;
use App\Domain\Financeiro\Contabilidade\Services\ContaCorrente\ImportarDdr;
use App\Domain\Financeiro\Contabilidade\Services\ContaCorrente\TemplateDdr;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class ContaCorrente extends Controller
{
    public function implantarDDR(ImportarDdrRequest $request)
    {
        /**
         * @var $file UploadedFile
         */
        $file = $request->file('file');
        $exercicio = $request->get('exercicio');

        $service = new ImportarDdr($exercicio, $file->getRealPath());
        $service->importar();

        return new DBJsonResponse([], 'Processado com sucesso.');
    }

    public function template()
    {
        $data = Carbon::createFromFormat('Y-m-d', date("Y-m-d", $_SESSION["DB_datausu"]));

        $service = new TemplateDdr($data);
        return new DBJsonResponse($service->download(), 'Template gerado com sucesso.');
    }
}
