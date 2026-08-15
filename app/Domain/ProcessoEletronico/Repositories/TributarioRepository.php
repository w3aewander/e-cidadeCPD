<?php

namespace App\Domain\ProcessoEletronico\Repositories;

use App\Domain\Configuracao\Instituicao\Model\DBConfig;
use App\Domain\Patrimonial\Protocolo\Model\Cgm;
use App\Domain\Tributario\Arrecadacao\Models\Arretipo;
use App\Domain\Tributario\Cadastro\Models\Iptubase;
use App\Domain\Tributario\Cadastro\Models\Promitente;
use App\Domain\Tributario\Cadastro\Models\Propri;
use App\Domain\Tributario\Caixa\Models\Arrecad;
use App\Domain\Tributario\Caixa\Models\Arreinscr;
use App\Domain\Tributario\Caixa\Models\Arrematric;
use App\Domain\Tributario\Caixa\Models\Arrenumcgm;
use App\Domain\Tributario\Caixa\Models\Cadtipo;
use App\Domain\Tributario\Caixa\Models\Recibounica;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TributarioRepository
{
    /**
     * Retorna uma lista de tipos de debitos em aberto do contribuinte
     *
     * @param  string  $tipoContribuinte  Tipo do contribuinte,
     * M = Matricula, I = Inscrição, C = Número CGM
     * @param  string|int  $value  Valor do tipo do contribuinte
     *
     * @return Collection<Arretipo>
     * @throws Exception
     *
     */
    public function getTipoDebito($tipoContribuinte, $value)
    {
        $tiposIput = Cadtipo::where('k03_tipo', 1)->with([
            'arretipo' => function ($query) {
                return $query->where('k00_recibodbpref', '<=', 2);
            },
        ])->first();

        $tipoDebitos = $tiposIput->arretipo->pluck('k00_tipo')->toArray();
        // $debitos  = $this->getContribuinte($tipoContribuinte, $value);
        // $debitos  = $debitos->has('arrecad')->get();

        // $tipoDebitos = [];

        // $debitos->each(function ($debito) use (&$tipoDebitos) {
        //     $tipoDebitos = array_merge($tipoDebitos, $debito->arrecad->pluck('k00_tipo')->toArray());
        // });

        // $tipoDebitos = array_unique($tipoDebitos);

        return Arretipo::whereIn('k00_tipo', $tipoDebitos)->with('cadtipo')
                       ->get();
    }

    /**
     * Verifica e retorna o cgm referente ao CPF/CNPJ
     *
     * @param  string  $cpfCnpj
     *
     * @return Cgm
     * @throws Exception
     *
     */
    public function getCgmByCpfCnpj($cpfCnpj)
    {
        $result = Cgm::where('z01_cgccpf', $cpfCnpj)->get();

        if ($result->count() > 1) {
            throw new Exception('Mais de um CGM foi encontrado');
        } elseif ($result->count() < 1) {
            throw new Exception('CPF/CPNJ não cadastrado no sistema');
        }

        return $result[0];
    }

    /**
     * Retorna uma lista de matriculas com base no CGM informado
     *
     * @param  string  $cpfCnpj
     *
     * @return Collection<Iptubase>
     */
    public function getImoveisByCPFCNPJ($cpfCnpj)
    {
        $cgm              = $this->getCgmByCpfCnpj($cpfCnpj);
        $db21_regracgmiss = $this->getConfig('db21_regracgmiss');
        $propri           = [];
        $promitente       = [];


        $iptubase = Iptubase::where('j01_numcgm', $cgm->z01_numcgm)->with(
            'proprietario'
        )->pluck('j01_matric')->toArray();

        if ($db21_regracgmiss == 1 or $db21_regracgmiss == 0) {
            $propri = Propri::where('j42_numcgm', $cgm->z01_numcgm)->pluck(
                'j42_matric'
            )->toArray();
        } elseif ($db21_regracgmiss == 2 or $db21_regracgmiss == 0) {
            $promitente = Promitente::where('j41_numcgm', $cgm->z01_numcgm)
                                    ->pluck('j41_numcgm')->toArray();
        }

        $matriculas = array_merge($iptubase, $propri, $promitente);
        $matriculas = array_unique($matriculas);
        $result     = Iptubase::whereIn('j01_matric', $matriculas)->with(
            'proprietario'
        )->get();

        return $result;
    }

    /**
     * @param $matricula
     *
     * @return  Builder|Iptubase|Model|null
     */
    public function getImovelByMatricula($matricula)
    {
        return  Iptubase::where('j01_matric', $matricula)->with(
            'proprietario'
        )->first();
    }

    /**
     * Retorna uma lista de debitos referente ao tipos
     *
     * @param  string  $tipoContribuinte  Tipo do contribuinte,
     * M = Matricula, I = Inscrição, C = Número CGM
     * @param  string|int  $value  Valor do tipo do contribuinte
     *
     * @return mixed
     * @throws Exception
     *
     */
    public function getContribuinte($tipoContribuinte, $value)
    {
        switch ($tipoContribuinte) {
            case "M":
                return Arrematric::where('k00_matric', $value);
                break;
            case "I":
                return Arreinscr::where('k00_matric', $value);
                break;
            case "C":
                return Arrenumcgm::where('k00_matric', $value);
                break;
            default:
                throw new Exception(
                    'O tipo do contribuinte informado é invalido'
                );
        }
    }

    /**
     * Retorna os debitos do contribuinte referente ao tipo de contribuinte
     *
     * @param  string  $tipoContribuinte  Tipo do contribuinte,
     * M = Matricula, I = Inscrição, C = Número CGM
     * @param  string|int  $value  Valor do tipo do contribuinte
     * @param  int  $tipoDebito
     *
     * @return array<Collection<Arrecad>, Collection<Recibounica>>
     */
    public function getDebitosByTipoContribuinte(
        $tipoContribuinte,
        $value,
        $tipoDebito
    ) {
        $numpres = $this->getContribuinte($tipoContribuinte, $value);
        $numpres = $numpres->pluck('k00_numpre')->toArray();
        $debitos = Arrecad::whereIn('k00_numpre', $numpres)->where(
            'k00_tipo',
            $tipoDebito
        )->select('k00_numpre', 'k00_numpar')->selectRaw(
            'max(k00_hist) as k00_hist,'.'max(k00_numtot) as k00_numtot,'
            .'max(k00_tipo) as k00_tipo,'.'min(k00_dtoper) as k00_dtoper,'
            .'min(k00_dtvenc) as k00_dtvenc,'.'sum(k00_valor) as k00_valor,'
            .'count(distinct k00_receit) as total_detalhe_debitos'
        )->groupBy('k00_numpre', 'k00_numpar')->get();

        $numpres = [];

        $debitos->each(function (Arrecad $arrecad) use (&$numpres) {
            $arrecad->calcular();
            $arrecad->arrecad();

            $numpres[] = $arrecad->k00_numpre;
        });

        $numpres     = array_unique($numpres);
        $cotasUnicas = $this->getDebitoCotaUnicaByNumpres(
            $tipoDebito,
            $numpres
        );

        return [$debitos, $cotasUnicas];
    }

    /**
     * Retorna os debitos de cota unica referente aos numpres
     *
     * @param  int[]  $numpres  Lista de numpres do tipo de debito
     * @param  int  $k00_tipo  Tipo de debito da cota unica
     *
     * @return Collection<Recibounica>
     */
    public function getDebitoCotaUnicaByNumpres($k00_tipo, $numpres = [])
    {
        $cotasUnicas = Recibounica::whereIn('k00_numpre', $numpres)->where(
            'k00_dtvenc',
            '>',
            date('Y-m-d')
        )->get();

        $cotasUnicas->each(
            function (Recibounica $cotaUnica, $index) use (&$k00_tipo) {
                $cotaUnica->index    = ($index + 1);
                $cotaUnica->k00_tipo = $k00_tipo;

                $cotaUnica->calcular();
            }
        );

        return $cotasUnicas;
    }

    /**
     * Processa a emissao do recibo
     *
     * @param  string  $cpfCnpj
     * @param  int  $tipoDebito
     * @param  array<array<string, mixed>>  $debitos
     *
     * @return EmissaoReciboRepository
     * @throws Exception
     */
    public function emitirRecibo($cpfCnpj, $tipoDebito, $debitos = [])
    {
        $cgm        = $this->getCgmByCpfCnpj($cpfCnpj);
        $tipoDebito = Arretipo::where('k00_tipo', $tipoDebito)->first();
        $debitos    = $this->montedDebito($tipoDebito, $debitos);
        $dataVenc   = $this->getLowDataVenc($debitos);
        $this->verifyTipoDebitoEmissao($tipoDebito, $dataVenc);

        $now =  new \DateTime();

        if ($dataVenc < $now) {
            $dataVenc =  $now;
        }

        if ($dataVenc->format("Y") > $now->format("Y")) {
            $dataVenc =  $now->modify("last day of this month");
        }

        $recibo = (new EmissaoReciboRepository(
            $cgm,
            $tipoDebito,
            $debitos,
            $dataVenc
        ))->emitirRecibo();

        return $recibo;
    }

    /**
     * Retorna a data mais proxima do vencimento
     *
     * @param  array<int, Collection<Arrecad>>  $debitos
     *
     * @return DateTime
     * @throws Exception
     */
    private function getLowDataVenc($debitos)
    {
        $data = null;
        foreach ($debitos as $debito) {
            foreach ($debito as $arrecad) {
                if ($data == null) {
                    $data = new \DateTime($arrecad->k00_dtvenc);
                }
                $k00_dtvenc = new \DateTime($arrecad->k00_dtvenc);

                if ($data > $k00_dtvenc) {
                    $data = $k00_dtvenc;
                }
            }
        }

        return $data;
    }

    /**
     * Montar lista de debitos
     *
     * @param  Arretipo  $arretipo
     * @param  array<array<string, mixed>>  $debitos
     *
     * @return array<int, Collection<Arrecad>>
     * @throws \Exception
     *
     */
    private function montedDebito(Arretipo $arretipo, $debitos = [])
    {
        $result       = [];
        $totalDebitos = count($debitos);

        if ($totalDebitos == 0) {
            throw new Exception('Nenhum debito foi informado');
        }

        foreach ($debitos as $index => $debito) {
            $numpreDebito = $debito['numero_debito'];
            $numparDebito = $debito['numero_parcela'];
            $dtvencDebito = $debito['data_vencimento'];
            $cotaUnica    = false;

            if (! is_numeric($numparDebito) and $numparDebito[0] != 'U') {
                throw new Exception(
                    'Numero da parcela do debito de index('.$index
                    .') e invalido'
                );
            } elseif (! is_numeric($numparDebito) and $numparDebito[0] == 'U'
                                                       and $totalDebitos > 1
            ) {
                throw new Exception(
                    'Numero da parcela do debito de index('.$index
                    .') e invalido'
                );
            } elseif (! is_numeric($numparDebito) and $numparDebito[0] == 'U'
                                                       and $totalDebitos == 1
            ) {
                $cotaUnica = true;
            }

            $where = [
                ['k00_numpre', $numpreDebito],
                ['k00_tipo', $arretipo->k00_tipo],
            ];

            if (! $cotaUnica) {
                $where[] = ['k00_dtvenc', $dtvencDebito];
                $where[] = ['k00_numpar', $numparDebito];
            }

            $arrecad = Arrecad::where($where)->first();

            if (! $arrecad) {
                if ($cotaUnica) {
                    $numparDebito = 'Parcela unica';
                }

                throw new Exception(
                    'O debito de codigo ('.$numpreDebito.') e parcela ('
                    .$numparDebito
                    .'), nao foi encontrado ou nao esta mais em aberto'
                );
            }

            if ($cotaUnica) {
                $reciboUnica = Recibounica::where('k00_numpre', $numpreDebito)
                                          ->where('k00_dtvenc', $dtvencDebito)
                                          ->first();

                if (! $reciboUnica) {
                    throw new Exception(
                        'O debito de parcela unica, nao foi encontrado ou nao esta mais em aberto'
                    );
                }

                $arrecad->reciboUnica = $reciboUnica;
            }

            if (! isset($result[$numpreDebito])) {
                $result[$numpreDebito] = [];
            }

            $result[$numpreDebito][] = $arrecad;
        }

        return $result;
    }

    /**
     * Verifica se o tipo de debito pode emitir um recibo
     *
     * @param  Arretipo  $arretipo
     * @param  \DateTime  $dataVenc
     *
     * @return void
     * @throws \Exception
     *
     */
    private function verifyTipoDebitoEmissao(
        Arretipo $arretipo,
        \Datetime $dataVenc
    ) {
        if (is_null($arretipo->k00_emrec)) {
            throw new Exception(
                'Nao e permitido emitir recibo para esse tipo de debito'
            );
        }

        if (is_null($arretipo->k00_recibodbpref) or $arretipo->k00_recibodbpref
                                                    != '1'
        ) {
            throw new Exception(
                'Nao e permitido emitir recibo para esse tipo de debito'
            );
        }

        if ($arretipo->k00_liberacarnepref == false and $dataVenc < date_create(
            'now'
        )
        ) {
            throw new Exception(
                'Nao e permitido emitir recibo com data vencida para esse tipo de debito'
            );
        }
    }

    /**
     * Retorna a configuração do banco dedaos
     *
     * @param  string  $coluna
     *
     * @return mixed
     */
    private function getConfig($coluna)
    {
        return DBConfig::where('codigo', '1')->first()->{$coluna};
    }
}
