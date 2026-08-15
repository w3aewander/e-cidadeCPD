<?php

namespace App\Domain\Financeiro\Contabilidade\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Financeiro\Contabilidade\Services\MapeamentoEmpenhoRPContaService;
use App\Http\Controllers\Controller;
use BusinessException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapeamentoEmpenhoRPContaController extends Controller
{
    protected $service;

    /**
     * Contrutor
     *
     * @param MapeamentoEmpenhoRPContaService $service
     */
    public function __construct(MapeamentoEmpenhoRPContaService $service)
    {
        $this->service = $service;
    }

    /**
     * Contas Mapeadas
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {get} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta Contas Mapeadas
     * @apiName ContasMapeadas
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiParam {Number} instituicao Codigo da instituicao
     * @apiParam {Number} exercicio Ano do exercicio
     * @apiSuccess {Object[]} data Contas mapeadas
     * @apiUse Error
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'instituicao'  => 'required|numeric',
            'exercicio' => 'required|digits:4',
        ]);

        try {
            $mapeamentos = $this->service->getContasMapeadas((object) $request->all());
            return new DBJsonResponse($mapeamentos);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }

    /**
     * Contas para mapear
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {get} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta/contas Contas para mapear
     * @apiName ContasMapear
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiSuccess {Object[]} data Contas para mapear
     * @apiUse Error
     */
    public function contas(Request $request)
    {
        $instit = session('DB_instit');
        $exercicio = session('DB_anousu');

        try {
            $contas = $this->service->getContasRP($exercicio, $instit);
            return new DBJsonResponse($contas);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }

    /**
     * Empenhos a mapear
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {get} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta/empenhos Empenhos a mapear
     * @apiName EmpenhosMapear
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiParam {Number} estrutural Codigo da conta estrutural
     * @apiParam {Number} exercicio Ano do exercicio
     * @apiSuccess {Object[]} data Empenhos
     * @apiUse Error
     */
    public function empenhos(Request $request)
    {
        $this->validate($request, [
            'reduzido'  => 'required|numeric',
            'exercicio' => 'required|digits:4',
            'tipoLiquidacao' => 'required|numeric'
        ]);

        try {
            $empenhos = $this->service->getEmpenhos((object) $request->all());
            return new DBJsonResponse($empenhos);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }

    /**
     * Empenhos da conta
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {get} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta/empenhos-conta Empenhos da conta
     * @apiName EmpenhosConta
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiParam {Number} reduzido Codigo da conta reduzida
     * @apiParam {Number} exercicio Ano do exercicio
     * @apiParam {String} empenho Filtros do empenho
     * @apiSuccess {Object[]} data Empenhos
     * @apiUse Error
     */
    public function empenhosConta(Request $request)
    {
        $this->validate($request, [
            'reduzido'  => 'required|numeric',
            'exercicio' => 'required|digits:4',
            'empenho'   => 'string'
        ]);

        try {
            $empenhos = $this->service->getEmpenhosConta((object) $request->all());
            return new DBJsonResponse($empenhos);
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }

    /**
     * Salvar mapeamento
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {post} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta/save Salvar mapeamento
     * @apiName SaveMapeamento
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiParam {Number} reduzido Codigo da conta reduzida
     * @apiParam {Number} exercicio Ano do exercicio
     * @apiParam {Object[]} empenhos Lista de empenhos
     * @apiSuccess {Object[]} data
     * @apiUse Error
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'reduzido'  => 'required|numeric',
            'exercicio' => 'required|digits:4',
            'empenhos'  => 'required|array'
        ]);

        try {
            $this->service->saveEmpenhos((object) $request->all());
            return new DBJsonResponse(null, 'Salvo com sucesso');
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }

    /**
     * Deletar Mapeamento
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {delete} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta/delete Deletar Mapeamento
     * @apiName DeleteMapeamento
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiParam {Number} codigo do mapeamento
     * @apiSuccess {Object[]} data
     * @apiUse Error
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['codigo' => 'required|numeric']);

        try {
            $this->service->deleteEmpenho($request->codigo);
            return new DBJsonResponse(null, 'Excluido com sucesso');
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }

    /**
     * Deletar Mapeamento
     *
     * @param Request $request
     * @return DBJsonResponse
     *
     * @api {delete} financeiro/contabilidade/procedimento/mapeamento-empenho-rp-conta/delete Deletar Mapeamento
     * @apiName DeleteMapeamento
     * @apiGroup Contabilidade/MapeamentoEmpenhoRPSaldo
     * @apiParam {Number} reduzido Reduzido
     * @apiParam {Number} exercicio Ano do exercicio
     * @apiSuccess {Object[]} data
     * @apiUse Error
     */
    public function deleteAll(Request $request)
    {
        $this->validate($request, [
            'reduzido'  => 'required|numeric',
            'exercicio' => 'required|digits:4',
        ]);

        try {
            $this->service->deleteAllEmpenhos($request->reduzido, $request->exercicio);
            return new DBJsonResponse(null, 'Excluido com sucesso');
        } catch (BusinessException $e) {
            return new DBJsonResponse(null, $e->getMessage(), 406);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return new DBJsonResponse(null, 'Erro interno. Consulte os logs.', 406);
        }
    }
}
