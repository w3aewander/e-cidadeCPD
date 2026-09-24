<?php
 
namespace App\Domain\ProcessoEletronico\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\ProcessoEletronico\Repositories\TributarioRepository;
use App\Domain\ProcessoEletronico\Requests\GetDebitosRequest;
use App\Domain\ProcessoEletronico\Requests\GetImoveisRequest;
use App\Domain\ProcessoEletronico\Requests\GetTiposDebitosRequest;
use App\Domain\ProcessoEletronico\Resources\TiposDebitosResource;
use App\Domain\ProcessoEletronico\Resources\ImoveisResource;
use Illuminate\Http\Request;

class TributarioController extends Controller
{
    /**
     * Repositorio TributarioRepository
     *
     * @var mixed
     */
    private $tributarioRepository;

    public function __construct(TributarioRepository $tributarioRepository)
    {
        $this->tributarioRepository = $tributarioRepository;
    }

    public function getImoveisByCPFCNPJ(GetImoveisRequest $request)
    {
        $imoveis = $this->tributarioRepository->getImoveisByCPFCNPJ($request->input('cpf_cnpj'));

        return new DBJsonResponse(
            ImoveisResource::toResponse($imoveis),
            "Foram encontrados " . $imoveis->count() . " imovei(s) para esse CPF/CNPJ"
        );
    }

    public function getTipoDebitosByValue(GetTiposDebitosRequest $request)
    {
        $tiposDebitos = $this->tributarioRepository->getTipoDebito(
            $request->input('tipo'),
            $request->input('value')
        );

        return new DBJsonResponse(
            TiposDebitosResource::toArray($tiposDebitos),
            "Foram encontrados " . $tiposDebitos->count() .
            " tipo(s) de debito(s) em aberto para esse contribuinte"
        );
    }

    public function getDebitosByTipoDebito(GetDebitosRequest $request)
    {
        $tiposDebitos = $this->tributarioRepository->getTipoDebito(
            $request->input('tipo'),
            $request->input('value')
        );

        $tiposDebitos->each(function ($tipo) use ($request) {
            $debitos = $this->tributarioRepository->getDebitosByTipoContribuinte(
                $request->input('tipo'),
                $request->input('value'),
                $tipo->k00_tipo
            );

            $tipo->debitos   = $debitos[0];
            $tipo->cotaUnica = $debitos[1];
        });

        $result = TiposDebitosResource::toArray($tiposDebitos);


        switch (strtoupper(trim($request->input('tipo')))) {
            case "M":
                $imovel = $this->tributarioRepository->getImovelByMatricula(
                    $request->input('value')
                );
                return new DBJsonResponse(
                    [
                        'imovel' => ImoveisResource::toObjectProprietario(
                            $imovel->proprietario
                        ),
                        'debitos'   => $result,
                    ],
                    "Foram encontrados ".$tiposDebitos->count()
                    ." tipo(s) de debito(s) em aberto para esse contribuinte"
                );
            default:
                return new DBJsonResponse(
                    $result,
                    "Foram encontrados " . count($result) .
                    " tipo(s) de debito(s) em aberto para esse contribuinte"
                );
        }

        // $debitos = $this->tributarioRepository->getDebitosByTipoContribuinte(
        //     $request->input('tipo'),
        //     $request->input('value'),
        //     $request->input('tipo_debito')
        // );

        // return new DBJsonResponse(
        //     DebitosResource::toArray($debitos),
        //     ""
        // );
    }

    /**
     * @throws \Exception
     */
    public function emitirRecibo(Request $request)
    {
        $recibo = $this->tributarioRepository->emitirRecibo(
            $request->input('cpf_cnpj'),
            $request->input('tipo_debito'),
            $request->input('debitos')
        );

        return new DBJsonResponse($recibo->getDadosRecibo(), 'O recibo foi gerado com sucesso');
    }
}
