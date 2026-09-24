<?php

namespace App\Domain\Patrimonial\Ouvidoria\Controller\Atendimento;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Ouvidoria\Services\AtendimentoJsonService;
use App\Http\Controllers\Controller;
use ECidade\Lib\Session\DefaultSession;
use Illuminate\Http\Request;

class AtendimentoJsonController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index($numero, $ano)
    {
        $defaultSession = DefaultSession::getInstance();
        $json = AtendimentoJsonService::findJson(
            $numero,
            $ano,
            $defaultSession->get(DefaultSession::DB_INSTIT)
        );

        return new DBJsonResponse($json);
    }

    public function update($atendimento_id, Request $request)
    {
        $this->validate($request, ['json' => 'required']);
        AtendimentoJsonService::update($atendimento_id, addslashes($request->get("json")));
        return new DBJsonResponse([], "Atualizado com sucesso!");
    }
}
