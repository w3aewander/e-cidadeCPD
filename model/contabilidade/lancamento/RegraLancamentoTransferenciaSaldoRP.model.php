<?php

require_once(modification("interfaces/IRegraLancamentoContabil.interface.php"));
/**
 * E-cidade Software Publico para Gestão Municipal
 *   Copyright (C) 2009 DBSeller Serviços de Informática Ltda
 *                          www.dbseller.com.br
 *                          e-cidade@dbseller.com.br
 *   Este programa é software livre; você pode redistribuí-lo e/ou
 *   modificá-lo sob os termos da Licença Pública Geral GNU, conforme
 *   publicada pela Free Software Foundation; tanto a versão 2 da
 *   Licença como (a seu critério) qualquer versão mais nova.
 *   Este programa e distribuído na expectativa de ser útil, mas SEM
 *   QUALQUER GARANTIA; sem mesmo a garantia implícita de
 *   COMERCIALIZAÇÃO ou de ADEQUAÇÃO A QUALQUER PROPÓSITO EM
 *   PARTICULAR. Consulte a Licença Pública Geral GNU para obter mais
 *   detalhes.
 *   Você deve ter recebido uma cópia da Licença Pública Geral GNU
 *   junto com este programa; se não, escreva para a Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *   02111-1307, USA.
 *   Cópia da licença no diretório licenca/licenca_en.txt
 *                                 licenca/licenca_pt.txt
 */


/**
 * Classe responsavel por criar a regra de lancamento de inscricao dos Restos a pagar
 * @author Rafael Lopes
 * @package Contabilidade
 * @subpackage lancamento
 * @version $Revision: 1.0 $
 * Class RegraLancamentoEncerramentoRP
 */
class RegraLancamentoTransferenciaSaldoRP implements IRegraLancamentoContabil
{
    protected $documentosTransferencia = array(2034, 2035, 2037);

    /**
     * @param int $iCodigoDocumento
     * @param int $iCodigoLancamento
     * @param ILancamentoAuxiliar| $oLancamentoAuxiliar
     * @return bool|RegraLancamentoContabil
     * @throws Exception
     */
    public function getRegraLancamento(
        $iCodigoDocumento,
        $iCodigoLancamento,
        ILancamentoAuxiliar $oLancamentoAuxiliar
    ) {
        if (in_array($iCodigoDocumento, $this->documentosTransferencia)) {
            $oDaoTransacao = new cl_contranslr;
            $sWhere = "     c45_coddoc      = {$iCodigoDocumento}";
            $sWhere .= " and c45_anousu      = " . db_getsession("DB_anousu");
            $sWhere .= " and c46_seqtranslan = {$iCodigoLancamento}";
            $sSqlTransacao = $oDaoTransacao->sql_queryRegraLancamento(null, "*", null, $sWhere);
            $rsTransacao = $oDaoTransacao->sql_record($sSqlTransacao);

            if ($oDaoTransacao->numrows == 0) {
                $sMsgErro = "Não há lançamentos configurados para o documento {$iCodigoDocumento}.";
                throw new BusinessException($sMsgErro);
            }
            for ($iLinhaRegra = 0; $iLinhaRegra < $oDaoTransacao->numrows; $iLinhaRegra++) {
                $oDadosTransacao = db_utils::fieldsMemory($rsTransacao, $iLinhaRegra);

                $regra = new RegraLancamentoContabil($oDadosTransacao->c47_seqtranslr);

                if (in_array($iCodigoDocumento, [2034,2037]) && $oLancamentoAuxiliar->isEstorno()) {
                    $oRegraLancamento = clone $regra;
                    $contaCredito = $regra->getContaCredito();
                    $contaDebito = $regra->getContaDebito();
                    $oRegraLancamento->setContaDebito($contaCredito);
                    $oRegraLancamento->setContaCredito($contaDebito);
                    return $oRegraLancamento;
                }

                return $regra;
            }
        }
    }
}
