<?php

namespace App\Domain\Patrimonial\Veiculos\Controller;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Veiculos\Models\Motorista;
use App\Domain\Patrimonial\Veiculos\Services\MotoristaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MotoristaController extends Controller
{
    /**
     * @throws \Exception
     */
    public function motoristaCentrais(Request $request)
    {
        $this->validate($request, [
            'cgm' => 'required',
        ]);

        $motorista = Motorista::where('ve05_numcgm', $request->get('cgm'))->get();

        if (count($motorista) == 0) {
            throw new \Exception('Nenhum motorista encontrado para este CGM');
        }

        if (count($motorista) > 1) {
            throw new \Exception('Mais de um motorista associado ao CGM');
        }

        $motorista = Motorista::where('ve05_numcgm', $request->get('cgm'))
            ->with('centrais')
            ->first();

        $centrais = [];

        foreach ($motorista->centrais as $centralItem) {
            $central = MotoristaService::formataDepartamentos(
                $centralItem->centraisDepartamentos
            );

            $centrais[] = $central;
        }

        return new DBJsonResponse($centrais);
    }
}
