<?php

namespace App\Domain\ProcessoEletronico\Repositories;

use App\Domain\Tributario\Arrecadacao\Models\Recibobarpix;
use App\Domain\Tributario\Arrecadacao\Models\Recibopaga;
use App\Domain\Tributario\Arrecadacao\Services\ArrecadacaoPixService;
use App\Domain\Tributario\Caixa\Models\Recibocodbar;
use ECidade\V3\Extension\Registry;
use ECidade\Tributario\Arrecadacao\CobrancaRegistrada\CobrancaRegistrada;
use ECidade\V3\Datasource\Database;
use Exception;

class EmissaoReciboRepository
{
    /**
     * Tipo do debito
     *
     * @var Arretipo $tipoDebito
     */
    private $tipoDebito;

    /**
     * Regra de emissão
     *
     * @var RegraEmissao
     */
    private $oRegraEmissao;

    /**
     * Recibo
     *
     * @return Recibo
     */
    private $oRecibo;

    /**
     * Codigo de barra do recibo gerado
     *
     * @var string
     */
    private $codigoBarra;

    /**
     * Convenio
     *
     * @var convenio
     */
    private $oConvenio;

    /**
     * Data do vencimento selecionado
     *
     * @var string
     */
    private $dataVencimento;

    /**
     * Valor total
     *
     * @var int|float
     */
    private $valorTotal;

    /**
     * Lista de débitos selecioandos
     *
     * @var string|array<int, Arrecad[]> $debitos
     */
    private $debitos = [];

    /**
     * ReciboPaga
     *
     * @var Collection<Recibopaga> $reciboPaga
     */
    private $reciboPaga;

    /**
     * Recibocodbar
     *
     * @var Recibocodbar $recibocodbar
     */
    private $recibocodbar;

    /**
     * ArrecadacaoPixService
     *
     * @var App\Domain\Tributario\Arrecadacao\Services\ArrecadacaoPixService $arrecadacaoPixService
     */
    private $arrecadacaoPixService;

    /**
     * Recibobarpix
     *
     * @var Recibobarpix $recibobarpix
     */
    private $recibobarpix;

    /**
     * CGM
     *
     * @var CGM $cgm
     */
    private $cgm;

    /**
     * ConvenioCobranca
     *
     * @var bool $ConvenioCobranca
     */
    private $lConvenioCobrancaValido;

    public function __construct($cgm, $tipoDebito, $debitos, $dataVencimento)
    {
        require_once(modification(ECIDADE_PATH . "libs/db_stdlib.php"));
        require_once(modification(ECIDADE_PATH . "libs/db_utils.php"));
        require_once(modification(ECIDADE_PATH . "libs/db_conecta.php"));

        $this->cgm        = $cgm;
        $this->tipoDebito = $tipoDebito;
        $this->debitos    = $debitos;

        $this->dataVencimento = date_format($dataVencimento, 'Y-m-d');
    }

    /**
     * Processa a emissao do recibo
     *
     * @throws Exception
     *
     * @return $this
     */
    public function emitirRecibo()
    {
        $database = Database::getInstance(true, null, true);

        $this->gerarRegraEmissao();
        $this->gerarRecibo();
        $this->gerarConvenio();

        $database->commit();

        $this->getReciboPaga();
        $this->getRecibocodbar();
        $this->gerarCobrancaRegistrada();
        $this->getDadosRecibo();

        $this->emitirPix();

        return $this;
    }

    /**
     * Efetua a emissao do qrcode do pix
     *
     * @return void
     */
    private function emitirPix()
    {
        try {
            $this->arrecadacaoPixService = new ArrecadacaoPixService;

            $this->arrecadacaoPixService->setVencimento($this->dataVencimento);
            $this->arrecadacaoPixService->setcodigoArrecadacao($this->oRecibo->getNumpreRecibo());
            $this->arrecadacaoPixService->setTipoDebito($this->tipoDebito->k00_tipo);

            $this->arrecadacaoPixService->geraPixCarne(
                $this->oConvenio,
                $this->oRecibo->getNumpreRecibo(),
                0,
                $this->cgm->z01_numcgm,
                $this->valorTotal
            );

            $this->recibobarpix = Recibobarpix::where('k00_numpre', $this->oRecibo->getNumpreRecibo())
                ->first();
        } catch (Exception $e) {
        }
    }

    /**
     * Retorna um lista de informacoes do recibo
     *
     * @return array<string, mixed>
     */
    public function getDadosRecibo()
    {
        return [
            'valor_total'       => $this->valorTotal,
            'qrcode_pix'        => ($this->recibobarpix) ? $this->recibobarpix->k00_qrcode : null,
            'linkqrcode_pix'    => ($this->recibobarpix) ? $this->recibobarpix->k00_linkqrcode : null,
            'codigo_barra'      => $this->oConvenio->getCodigoBarra(),
            'linha_digitavel'   => $this->oConvenio->getLinhaDigitavel(),
            'numero_debito'     => $this->oRecibo->getNumpreRecibo(),
            'data_vencimento'   => $this->oRecibo->getDataVencimento()
        ];
    }

    /**
     * Pega os dados da recibocodbar
     *
     * @return void
     */
    private function getRecibocodbar()
    {
        $this->recibocodbar = Recibocodbar::where('k00_numpre', $this->oRecibo->getNumpreRecibo())
            ->first();
    }

    /**
     * Gerar Regra de emissão para emissão do recibo
     *
     * @return void
     */
    private function gerarRegraEmissao()
    {
        require_once(modification(ECIDADE_PATH . "model/regraEmissao.model.php"));

        $this->oRegraEmissao = new \RegraEmissao(
            $this->tipoDebito->k00_tipo,
            23,
            db_getsession('DB_instit'),
            date("Y-m-d", db_getsession("DB_datausu")),
            db_getsession('DB_ip'),
            true,
            false,
            1,
            1,
            false
        );
    }

    /**
     * Gerar Recibo para emissão de recibo
     *
     * @return void
     */
    private function gerarRecibo()
    {
        require_once(modification(ECIDADE_PATH . "model/recibo.model.php"));

        $this->oRecibo = new \Recibo(2, $this->cgm->z01_numcgm, 9);

        $this->oRecibo->setNumBco(
            $this->oRegraEmissao->getCodConvenioCobranca()
        );

        foreach ($this->debitos as $numpre => $debitos) {
            foreach ($debitos as $debito) {
                if ($debito->reciboUnica) {
                    $this->oRecibo->addNumpre($numpre, 0);

                    $this->dataVencimento = $debito->reciboUnica->k00_dtvenc;
                    break;
                } else {
                    $this->oRecibo->addNumpre($numpre, $debito->k00_numpar);
                }
            }
        }

        $this->oRecibo->setDataVencimentoRecibo(
            $this->dataVencimento
        );

        $this->oRecibo->emiteRecibo($this->verifyCobrancaConvenio(), true, null, true);

        if ($this->lConvenioCobrancaValido &&
            !CobrancaRegistrada::utilizaIntegracaoWebService($this->oRegraEmissao->getConvenio())
        ) {
            CobrancaRegistrada::adicionarRecibo($this->oRecibo, $this->oRegraEmissao->getConvenio());
        }

        $this->codigoBarra = $this->formatarCodigoBar($this->oRecibo->getTotalRecibo());
        $this->valorTotal  = $this->oRecibo->getTotalRecibo();
    }

    /**
     * Cadastra ReciboWebservice
     *
     * @return void
     */
    private function gerarCobrancaRegistrada()
    {
        if ($this->lConvenioCobrancaValido &&
            CobrancaRegistrada::utilizaIntegracaoWebService($this->oRegraEmissao->getConvenio())
        ) {
            if (db_getsession('DB_DEBUG', false) && db_getsession('DB_DEBUG') == true) {
                if (!Registry::get('app.container')->has('app.webservice_caixa.debug')) {
                    Registry::get('app.container')->register('app.webservice_caixa.debug', function () {
                        $debugWebservice = (object) array(
                          'debug'  => true,
                          'origem' => 'Recibo'
                        );

                        return $debugWebservice;
                    });
                }
            }

            CobrancaRegistrada::registrarReciboWebservice(
                $this->oRecibo->getNumpreRecibo(),
                $this->oRegraEmissao->getConvenio(),
                $this->valorTotal,
                false,
                []
            );
        }
    }

    /**
     * Verifica cobrancaConvenio
     *
     * @return bool
     */
    private function verifyCobrancaConvenio()
    {
        $this->lConvenioCobrancaValido = CobrancaRegistrada::validaConvenioCobranca(
            $this->oRegraEmissao->getConvenio()
        );

        return $this->lConvenioCobrancaValido;
    }

    /**
     * Gera o Conveio para emissão
     *
     * @return void
     */
    private function gerarConvenio()
    {
        require_once(modification(ECIDADE_PATH . "model/convenio.model.php"));

        $this->oConvenio = new \convenio(
            $this->oRegraEmissao->getConvenio(),
            $this->oRecibo->getNumpreRecibo(),
            0,
            $this->oRecibo->getTotalRecibo(),
            $this->codigoBarra,
            $this->dataVencimento,
            $this->tipoDebito->k00_tercdigcarneunica
        );

        $this->codigoBarra = preg_replace('/\D/', '', $this->oConvenio->getLinhaDigitavel());
    }

    /**
     * Formatar o valor igual a função db_formart e
     *
     * @param int|double|float $valor
     *
     * @return string
     */
    private static function formatarCodigoBar($valorTotalRecibo)
    {
        $valorTotalRecibo = str_replace(
            '.',
            '',
            str_pad(
                number_format($valorTotalRecibo, 2, "", "."),
                11,
                "0",
                STR_PAD_LEFT
            )
        );

        return str_pad(
            $valorTotalRecibo,
            11,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Pega os Recibo gerados para recibopaga
     *
     * @return void
     */
    private function getReciboPaga()
    {
        $this->reciboPaga = Recibopaga::where(
            'k00_numnov',
            $this->oRecibo->getNumpreRecibo()
        )->get();
    }
}
