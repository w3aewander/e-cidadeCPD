<?php
/*
*     E-cidade Software Publico para Gestao Municipal
*  Copyright (C) 2016  DBselller Servicos de Informatica
*                            www.dbseller.com.br
*                         e-cidade@dbseller.com.br
*
*  Este programa e software livre; voce pode redistribui-lo e/ou
*  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
*  publicada pela Free Software Foundation; tanto a versao 2 da
*  Licenca como (a seu criterio) qualquer versao mais nova.
*
*  Este programa e distribuido na expectativa de ser util, mas SEM
*  QUALQUER GARANTIA; sem mesmo a garantia implicita de
*  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
*  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
*  detalhes.
*
*  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
*  junto com este programa; se nao, escreva para a Free Software
*  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
*  02111-1307, USA.
*
*  Copia da licenca no diretorio licenca/licenca_en.txt
*                                licenca/licenca_pt.txt
*/

namespace ECidade\Tributario\Arrecadacao\Custas;

use \DateTime;
use \Exception;
use \DBException;
use \BusinessException;
use \Taxa as TaxaModel;
use \cl_processoforopartilhacusta;
use \cl_inicialnumpre;
use \Recibo;
use \ECidade\Tributario\Arrecadacao\Repository\Taxa;
use \ECidade\Tributario\Juridico\InicialPartilha\Repository\InicialPartilha as InicialPartilhaRepository;
use \ECidade\Tributario\Juridico\InicialPartilha\Repository\InicialPartilhaCustas as InicialPartilhaCustasRepository;
use \ECidade\Tributario\Juridico\InicialPartilha\InicialPartilha;
use \ECidade\Tributario\Juridico\InicialPartilha\InicialPartilhaCustas;
use \ECidade\Tributario\Juridico\ProcessoForo\Repository\ProcessoForo as ProcessoForoRepository;
use \ECidade\Tributario\Juridico\ProcessoForoPartilha\Repository\ProcessoForoPartilha as ProcessoForoPartilhaRepository;
use \ECidade\Tributario\Juridico\ProcessoForoPartilha\ProcessoForoPartilha;
use \ECidade\Tributario\Juridico\ProcessoForoPartilha\ProcessoForoPartilhaCusta;

class Custas
{
    /**
     * @var int
     */
    private $iNumpre;

    /**
     * @var int
     */
    private $iArreTipo;

    /**
     * @var array
     */
    private $aProcessosForo;

    /**
     * @var int
     */
    private $iInicial;

    /**
     * @var int
     */
    private $iProcessoForo;

    /**
     * @var int[];
     */
    private $aNumpres;

    /**
     * @var InicialPartilha
     */
    private $oInicialPartilha;

    /**
     * Modelo de Recibo
     * @const int
     */
    const TIPO_MODELO_RECIBO = 19;

    /**
     * Modelo de Carne
     * @const int
     */
    const TIPO_MODELO_CARNE = 20;

    const TIPO_DEBITO = null;

    /**
     * Codigo de historico de custas
     * @const int
     */
    const CODIGO_HISTORICO = 11403;

    /**
     * Custas constructor.
     * @param int $iInicial
     * @param int $iArreTipo
     * @throws DBException
     */
    public function __construct($iInicial = null, $iArreTipo = null)
    {
        if (!empty($iInicial)) {
            $this->iInicial = $iInicial;
            $this->aNumpres = $this->getNumpresInicial();
        }

        if (!empty($iArreTipo)) {
            $this->iArreTipo = $iArreTipo;
        }
    }

    /**
     * @param Recibo $oRecibo
     * @return Recibo
     * @throws Exception
     * @throws BusinessException
     */
    public function processar(Recibo $oRecibo)
    {
        if ($this->isDebitoTemProcesso()) {
            
            /* NOTE DEBITO COM CUSTAS JURIDICAS */
            $this->processarCustasJuridica($oRecibo);

        } elseif ($this->isDebitoTemCustas()) {

            if (!$this->inInicialNumpre($oRecibo->getDebitosRecibo())) {
                throw new BusinessException("Nenhum numpre do recibo na Inicial");
            }

            /* NOTE: DEBITO COM CUSTAS ADMINISTRATIVAS */
            $this->processarCustasAdministrativas($oRecibo);
        }

        $aCustas = $this->oInicialPartilha->getCustas();

        if (!empty($aCustas)) {
            $oRecibo = $this->adicionaCustasRecibo($oRecibo);
        }
        
        return $oRecibo;
    }

    public function getInicialPartilha()
    {
        return $this->oInicialPartilha;
    }

    /**
     * Verifica se o debito tem inicial
     * @return bool
     * @throws DBException
     */
    private function isDebitoTemCustas()
    {
        if (!empty($this->iInicial)) {
            return true;
        }

        if (!empty($this->iNumpre)) {

            $sSql = "select * from inicialnumpre where v59_numpre = {$this->iNumpre}";
            $rsResult = db_query($sSql);

            if (!$rsResult) {
                throw new DBException("Ocorreu um erro ao verificar custas do Numpre: {$this->iNumpre}");
            }

            if (pg_num_rows($rsResult) > 0) {

                $oInicialNumpre = pg_fetch_object($rsResult);
                $this->iInicial = $oInicialNumpre->v59_inicial;
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o débito tem processo do foro
     * @return bool
     * @throws DBException
     */
    private function isDebitoTemProcesso()
    {
        if (!empty($this->iProcessoForo)) {
            return true;
        }

        if (!empty($this->iInicial)) {

            $sSql  = " select processoforoinicial.*                                                                        ";
            $sSql .= "   from inicial                                                                                      ";
            $sSql .= "        inner join processoforoinicial on processoforoinicial.v71_inicial = inicial.v50_inicial      ";
            $sSql .= "        inner join processoforo on processoforo.v70_sequencial = processoforoinicial.v71_processoforo";
            $sSql .= "  where processoforoinicial.v71_anulado = false                                                      ";
            $sSql .= "    and processoforo.v70_anulado = false                                                             ";
            $sSql .= "    and inicial.v50_inicial = {$this->iInicial}                                                      ";

            $rsResult = db_query($sSql);
            
            if (!$rsResult) {
                throw new DBException("Ocorreu um erro ao verificar se o débito tem processo.");
            }

            if (pg_num_rows($rsResult) > 0) {

                $oProcessoForoInicial = pg_fetch_object($rsResult);
                $this->iProcessoForo = $oProcessoForoInicial->v71_processoforo;
                return true;
            }
        }

        return false;
    }

    /**
     * Valida se é para utilizar a regra de emissao de recibo/carne com custas
     * @return bool
     */
    public function usaRegraEmissao()
    {
        $sExisteRegraModelos  = "select 1 from modcarnepadrao where k48_cadtipomod in (";
        $sExisteRegraModelos .= self::TIPO_MODELO_RECIBO . ", " . self::TIPO_MODELO_CARNE . ")";
        $sExisteRegraModelos .= " AND k48_datafim >= '" . date('Y-m-d', db_getsession("DB_datausu")) . "'";

        $rsExisteRegraModelos = db_query($sExisteRegraModelos);

        if (!$rsExisteRegraModelos) {
            throw new DBException("Erro ao verificar se existem regras para emissão com custas.");
        }

        $iNumRows = pg_num_rows($rsExisteRegraModelos);

        if ($iNumRows == 0) {
            return false;
        } else if ($this->isDebitoTemCustas() || $this->isDebitoTemProcesso()) {
            return true;
        }

        return false;
    }

    /**
     * Processa custas juridica
     * @param Recibo $oRecibo
     * @throws BusinessException
     */
    private function processarCustasJuridica(Recibo $oRecibo)
    {
        $oProcessoForoRepository = ProcessoForoRepository::getInstance();
        $oProcessoForo = $oProcessoForoRepository->getByInicial($this->iInicial);

        $oProcessoForoPartilhaRepository = ProcessoForoPartilhaRepository::getInstance();
        $oProcessoForoPartilha = $oProcessoForoPartilhaRepository->getByProcessoForoRecibo($oProcessoForo->getCodigo(), $oRecibo->getNumpreRecibo());
        
        if (empty($oProcessoForoPartilha)) {
            $oProcessoForoPartilha = new ProcessoForoPartilha();
            $oProcessoForoPartilha->setCodigoProcessoForo($oProcessoForo->getCodigo());
        }

        $oTaxaRepository = Taxa::getInstance();
        $aTaxas = $oTaxaRepository->getTodasComProcesso();
        $oValor = $this->getValorBaseCustas($oRecibo);

        $this->oInicialPartilha = $this->manipulaPartilhaJuridica($oProcessoForoPartilha, $oValor, $aTaxas, $oRecibo->getNumpreRecibo());
    }

    /**
     * Processa custas administrativas
     * @param Recibo $oRecibo
     * @throws BusinessException
     */
    private function processarCustasAdministrativas(Recibo $oRecibo)
    {
        $oInicialPartilhaRepository = InicialPartilhaRepository::getInstance();
        $oInicialPartilha = $oInicialPartilhaRepository->getUltimaByInicial($this->iInicial);
        $oTaxaRepository = Taxa::getInstance();
        $aTaxas = $oTaxaRepository->getTodasSemProcesso();

        $oValor = $this->getValorBaseCustas($oRecibo);
        if (empty($oInicialPartilha)) {
            $oInicialPartilha = new InicialPartilha();
        }

        $this->oInicialPartilha = $this->manipulaPartilha($oInicialPartilha, $oValor, $aTaxas, $oRecibo->getNumpreRecibo());
    }

    /**
     * @param InicialPartilha $oInicialPartilha
     * @param stdClass $oValor
     * @param TaxaModel[] $aTaxas
     * @param int $iNumnov
     * @return InicialPartilha
     * @throws DBException
     */
    private function manipulaPartilha(InicialPartilha $oInicialPartilha, $oValor, $aTaxas, $iNumnov)
    {
        $oInicialPartilhaRepository = InicialPartilhaRepository::getInstance();
        $aPartilhaCustas = $oInicialPartilha->getCustas();

        if (empty($aPartilhaCustas)) {

            $oInicialPartilha->setCodigoInicial($this->iInicial);
            $oInicialPartilha->setTipoLancamento(1);
            $oDataPartilha = new DateTime(date('Y-m-d', db_getsession("DB_datausu")));
            $oInicialPartilha->setDataPartilha($oDataPartilha);
        }

        $oInicialPartilha = $this->manipulaPartilhaCustas($oInicialPartilha, $oValor, $aTaxas, $iNumnov);

        $aPartilhaCustas = $oInicialPartilha->getCustas();

        if (empty($aPartilhaCustas) && $oInicialPartilha->getCodigo()) {
            if (!$oInicialPartilhaRepository->delete($oInicialPartilha)) {
                throw new DBException("Erro ao remover a Partilha de Custas.");
            }
            return new InicialPartilha();
        } else if (!empty($aPartilhaCustas)) {
            if (!$oInicialPartilhaRepository->persist($oInicialPartilha)) {
                throw new DBException("Erro ao criar a Partilha.");
            }
        }

        return $oInicialPartilha;
    }

    /**
     * @param InicialPartilha $oInicialPartilha
     * @param stdClass $oValor
     * @param TaxaModel[] $aTaxas
     * @param int $iNumnov
     * @return InicialPartilha
     */
    private function manipulaPartilhaCustas(InicialPartilha $oInicialPartilha, $oValor, $aTaxas, $iNumnov)
    {
        $aPartilhaCustas = $oInicialPartilha->getCustas();
        $oInicialPartilha->resetCustas();
        $fValorTotalPartilha = 0;

        /* Se for um recibo que ja possui partilha cria um indice das taxas ja existentes */
        $aTaxasExistentes = array();
        if (!empty($aPartilhaCustas)) {
            foreach ($aPartilhaCustas as $oPartilhaCustas) {
                $aTaxasExistentes[] = $oPartilhaCustas->getCodigoTaxa();
            }
        }

        /**
         * Se não tem nenhuma taxa configurada remove todas as instancias
         * de InicialPartilhaCustas das Partilha
         */
        if (empty($aTaxas) && !empty($aPartilhaCustas)) {
            $this->removeCustasPartilha($aPartilhaCustas);
        } else if (!empty($aTaxas)) {
            /**
             * Percorre as taxas configuradas para criar as custas da partilha
             */
            foreach ($aTaxas as $oTaxa) {

                if ($this->isPagoReciboTermo($oTaxa)) {
                    continue;
                }

                $oPartilhaCustas = new InicialPartilhaCustas();
                $oPartilhaCustas->setDispensaLancamentoRecibo(false);
                $oPartilhaCustas->setCodigoTaxa($oTaxa->getCodigoTaxa());

                if (in_array($oTaxa->getCodigoTaxa(), $aTaxasExistentes)) {

                    $iKey = array_search($oTaxa->getCodigoTaxa(), $aTaxasExistentes);
                    unset($aTaxasExistentes[$iKey]);
                    $oPartilhaCustas = $aPartilhaCustas[$iKey];

                    /* As custas que ficarem serão removidas da partilha*/
                    unset($aPartilhaCustas[$iKey]);
                }

                $fValorCustas = $this->calculaValorCustas($oValor, $oTaxa);
                $oPartilhaCustas->setValor($fValorCustas);
                $oPartilhaCustas->setNumnov($iNumnov);
                $oInicialPartilha->addCustas($oPartilhaCustas);

                if ($oPartilhaCustas->isDispensaLancamentoRecibo()) {
                    continue;
                }

                $fValorTotalPartilha += $fValorCustas;
            }

            $oInicialPartilha->setValorPartilha($fValorTotalPartilha);

            /**
             * Apaga todas as IniciaisPartilhaCustas que nao pertencem mais a partilha
             */
            $this->removeCustasPartilha($aPartilhaCustas);
        }

        return $oInicialPartilha;
    }

    /**
     * @param ProcessoForoPartilha $oProcessoForoPartilha
     * @param stdClass $oValor
     * @param TaxaModel[] $aTaxas
     * @param int $iNumnov
     * @return ProcessoForoPartilha
     * @throws DBException
     */
    private function manipulaPartilhaJuridica(ProcessoForoPartilha $oProcessoForoPartilha, $oValor, $aTaxas, $iNumnov)
    {
        $oProcessoForoPartilhaRepository = ProcessoForoPartilhaRepository::getInstance();

        $oProcessoForoPartilha->setTipoLancamento(1);
        $oDataPartilha = new DateTime(date('Y-m-d', db_getsession("DB_datausu")));
        $oProcessoForoPartilha->setDataPartilha($oDataPartilha);

        $oProcessoForoPartilha = $this->manipulaPartilhaCustasJuridica($oProcessoForoPartilha, $oValor, $aTaxas, $iNumnov);

        $oProcessoForoPartilhaRepository->persist($oProcessoForoPartilha);

        return $oProcessoForoPartilha;
    }


    /**
     * @param ProcessoForoPartilha $oProcessoForoPartilha
     * @param stdClass $oValor
     * @param TaxaModel[] $aTaxas
     * @param int $iNumnov
     * @return ProcessoForoPartilha
     */
    private function manipulaPartilhaCustasJuridica(ProcessoForoPartilha $oProcessoForoPartilha, $oValor, $aTaxas, $iNumnov)
    {
        $fValorTotalPartilha = 0;

        foreach ($aTaxas as $oTaxa) {

            if ($this->isPagoReciboTermo($oTaxa)) {
                continue;
            }

            $oProcessoForoPartilhaCusta = new ProcessoForoPartilhaCusta();
            $oProcessoForoPartilhaCusta->setDispensaLancamentoRecibo(false);
            $oProcessoForoPartilhaCusta->setCodigoTaxa($oTaxa->getCodigoTaxa());

            $fValorCustas = $this->calculaValorCustas($oValor, $oTaxa);

            /**PLUGINTAXAJURIDICAADICIONALPORNOME4**/

            $oProcessoForoPartilhaCusta->setValor($fValorCustas);
            $oProcessoForoPartilhaCusta->setNumnov($iNumnov);
            $oProcessoForoPartilha->addCustas($oProcessoForoPartilhaCusta);

            $fValorTotalPartilha += $fValorCustas;
        }

        $oProcessoForoPartilha->setValorPartilha($fValorTotalPartilha);

        return $oProcessoForoPartilha;
    }

    /**
     * @return int[]
     * @throws DBException
     */
    private function getNumpresInicial()
    {
        $oDaoInicialNumpre = new cl_inicialnumpre();
        $sWhere = "v59_inicial = {$this->iInicial}";
        $sSql = $oDaoInicialNumpre->sql_query_file(null, 'v59_numpre', null, $sWhere);

        $rsNumpres = db_query($sSql);

        if (!$rsNumpres) {
            throw new DBException("Erro ao obter os numpres da Inicial {$this->iInicial}");
        }

        $aNumpres = pg_fetch_all_columns($rsNumpres,0);
        return $aNumpres;
    }

    /**
     * Verifica se algum dos numpres pertence a inicial
     * @param $aNumpres
     * @return bool
     */
    private function inInicialNumpre($aNumpres)
    {
        foreach ($aNumpres as $oDebito) {
            if (in_array($oDebito->k00_numpre, $this->aNumpres)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param stdClass $oValorDebito
     * @param TaxaModel $oTaxa
     * @return float $fValorCustas;
     */
    private function calculaValorCustas($oValorDebito, TaxaModel $oTaxa)
    {
        $fPorcentagemTaxa = $oTaxa->getPercentual();
        $fValorCustas = $oTaxa->getValor();

        $fValorDebito = $oValorDebito->valor_hist - $oValorDebito->valor_desconto;

        if ($oTaxa->isAplicaJuroMulta()) {
            $fValorDebito = $oValorDebito->valor_hist + $oValorDebito->valor_juros + $oValorDebito->valor_multa - $oValorDebito->valor_desconto;
        }

        if (!empty($fPorcentagemTaxa) && $fPorcentagemTaxa > 0) {

            $fValorCustas =  ($fValorDebito * ($fPorcentagemTaxa / 100));

            if ($fValorCustas < $oTaxa->getValorMinimo()) {
                $fValorCustas = $oTaxa->getValorMinimo();
            } elseif ($fValorCustas > $oTaxa->getValorMaximo()) {
                $fValorCustas = $oTaxa->getValorMaximo();
            }
        }

        return round($fValorCustas, 2);
    }

    /**
     * @param Recibo $oRecibo
     * @return float
     * @throws DBException
     */
    private function getValorBaseCustas(Recibo $oRecibo)
    {
        $iNumnov = $oRecibo->getNumpreRecibo();

        $sSql  = " select sum(case when k00_hist <> 401 and k00_hist <> 400 and k00_hist <> 918 then k00_valor else 0 end) as valor_hist, ";
        $sSql .= "        sum(case when k00_hist = 400 then k00_valor else 0 end) as valor_juros,                                         ";
        $sSql .= "        sum(case when k00_hist = 401 then k00_valor else 0 end) as valor_multa,                                         ";
        $sSql .= "        sum(case when k00_hist = 918 then k00_valor else 0 end) as valor_desconto                                       ";
        $sSql .= "   from recibopaga                                                                                                      ";
        $sSql .= "  where k00_numnov = {$iNumnov}                                                                                         ";
        $sSql .= "    and k00_numpre in (select v59_numpre from inicialnumpre where v59_inicial = $this->iInicial)                        ";


        $rsValor = db_query($sSql);

        if (!$rsValor) {
            throw new DBException("Não foi possivel obter o valor base de calculo das custas.");
        }

        $oValor = pg_fetch_object($rsValor, 0);

        return $oValor;
    }

    /**
     * @param Recibo $oRecibo
     * @return Recibo
     */
    private function adicionaCustasRecibo(Recibo $oRecibo)
    {
        $aPartilhaCustas = $this->oInicialPartilha->getCustas();

        foreach ($aPartilhaCustas as $oPartilhaCustas) {

            if ($oPartilhaCustas->isDispensaLancamentoRecibo()) {
                continue;
            }

            $oTaxa = new TaxaModel($oPartilhaCustas->getCodigoTaxa());
            $iReceita = $oTaxa->getReceita();

            $oRecibo->adicionarReceitaCusta($iReceita, $oPartilhaCustas->getValor(), self::CODIGO_HISTORICO);
        }

        return $oRecibo;
    }

    /**
     * Retorna os tipos de debitos de iniciais
     * @return array
     */
    public static function getTiposDebitosIniciais()
    {
        return array(18, 12, 13);
    }

    /**
     * @param InicialPartilhaCustas[] $aCustas
     * @throws DBException
     */
    private function removeCustasPartilha($aCustas)
    {
        $oInicialPartilhaCustasRepository = InicialPartilhaCustasRepository::getInstance();
        foreach ($aCustas as $oCustasApagar) {
            if (!$oInicialPartilhaCustasRepository->delete($oCustasApagar)) {
                throw new DBException("Erro ao apagar a custas da taxa {$oCustasApagar->getCodigoTaxa()}.");
            }
        }
    }

    /**
     * @return int
     */
    public function getInicial()
    {
        return $this->iInicial;
    }

    private function isPagoReciboTermo(TaxaModel $oTaxa)
    {
        $lReturn = false;

        $sSql  = " select *                                                                  ";
        $sSql .= "   from termoini                                                           ";
        $sSql .= "        inner join termo on termo.v07_parcel = termoini.parcel             ";
        $sSql .= "        inner join recibopaga on recibopaga.k00_numpre = termo.v07_numpre  ";
        $sSql .= "        inner join disbanco on disbanco.k00_numpre = recibopaga.k00_numnov ";
        $sSql .= "  where termoini.inicial = {$this->iInicial}                               ";
        $sSql .= "    and termo.v07_situacao = 2                                             ";
        $sSql .= "    and disbanco.classi = true                                             ";
        $sSql .= "    and recibopaga.k00_receit = ".$oTaxa->getReceita();

        $rsResult = db_query($sSql);
        
        if (!$rsResult) {
            throw new DBException("Ocorreu um erro ao verificar se a custa já foi paga.");
        }

        if (pg_num_rows($rsResult) > 0) {
            $lReturn = true;
        }

        return $lReturn;
    }
}
