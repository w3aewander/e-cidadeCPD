<?php

namespace App\Domain\Financeiro\Tesouraria\Controller;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Financeiro\Tesouraria\Models\Saltes;
use App\Domain\Financeiro\Tesouraria\Resources\ContaResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Trata as contas da tesouraria
 */
class ContasController extends Controller
{
    /**
     * @param Request $request
     * @return DBJsonResponse
     */
    public function index(Request $request)
    {
        $contas = Saltes::query();

        if ($request->query('comContrapartida')) {
            $contas->with('contaContrapartida.conta');
        }

        if ($request->query('comContaExtra')) {
            $contas->with('contaExtra.conta');
        }

        if ($request->query('comReduzidos')) {
            $contas->join('contabilidade.conplanoreduz', 'c61_reduz', '=', 'k13_conta')
                ->join('contabilidade.conplano', function ($join) {
                    $join->on('c60_codcon', '=', 'c61_codcon')
                        ->on('c60_anousu', '=', 'c61_anousu');
                })
                ->join('orcamento.fonterecurso', 'orctiporec_id', '=', 'c61_codigo')
                ->join('orcamento.orctiporec', 'o15_codigo', '=', 'orctiporec_id')
                ->where('exercicio', session('DB_anousu'))
                ->where('c61_anousu', session('DB_anousu'))
                ->where('c61_instit', session('DB_instit'));
        }
        $contas->orderBy('k13_descr');

        return new DBJsonResponse(ContaResource::toArray($contas->get()));
    }
}
