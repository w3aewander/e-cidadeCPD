<?php

namespace App\Domain\Educacao\MatriculaOnline\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Educacao\CentralMatriculas\Enums\TipoCampoOpcionalEnum;
use App\Domain\Educacao\CentralMatriculas\Helpers\ConfiguracaoCentralHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CamposOpcionaisController extends Controller
{
    protected $helper;
    public function __construct(ConfiguracaoCentralHelper $helper)
    {
        $this->helper = $helper;
    }

    public function salvar(Request $request)
    {
        return  new DBJsonResponse($this->helper->camposOpcionais()->saveAll($request->all()));
    }

    public function index()
    {
        $campos = collect($this->helper->camposOpcionais()->index())->map(function ($campo) {
            $cp = $campo;
            $cp['tipo'] = (new TipoCampoOpcionalEnum(intval($cp['tipo'])))->toOject();
            return $cp;
        });
        return new DBJsonResponse($campos);
    }
}
