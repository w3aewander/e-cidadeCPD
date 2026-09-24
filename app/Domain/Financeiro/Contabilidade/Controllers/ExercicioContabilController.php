<?php

namespace App\Domain\Financeiro\Contabilidade\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Financeiro\Contabilidade\Services\ImplantacaoSaldoInicialService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExercicioContabilController extends Controller
{
    public function implantarSaldoInicial(Request $request)
    {

        $service = new ImplantacaoSaldoInicialService();
        $service->setExercicio($request->get('DB_anousu'))
            ->setInstituicao($request->get('DB_instit'))
            ->processar();
        return new DBJsonResponse([], 'Contas da instituição implantadas.');
    }
}
