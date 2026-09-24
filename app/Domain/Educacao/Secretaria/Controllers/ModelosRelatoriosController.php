<?php

namespace App\Domain\Educacao\Secretaria\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Educacao\Secretaria\Models\ModeloRelatorio;
use App\Http\Controllers\Controller;

class ModelosRelatoriosController extends Controller
{
    public function getModelosHistorico()
    {
        $modelos = ModeloRelatorio::select(["ed217_orientacao as orientacao", "ed217_i_codigo", "ed217_c_nome"])
            ->where('ed217_i_relatorio', 1)->get();
        return new DBJsonResponse($modelos);
    }
}
