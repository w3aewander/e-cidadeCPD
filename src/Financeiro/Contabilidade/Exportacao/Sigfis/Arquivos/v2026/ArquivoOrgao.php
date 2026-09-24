<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use cl_orcorgao;
use db_utils;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;

class ArquivoOrgao extends ArquivoBase
{
    protected $sNomeArquivo = 'Orgao';

    public function gerarDados()
    {
        $oDaoOrcorgao = new cl_orcorgao();

        $iAnoSessao = db_getsession('DB_anousu');
        $iInstituicaoSessao = db_getsession('DB_instit');

        $sCampos = " distinct orcorgao.o40_anousu, ";
        $sCampos .= " db_config.nomeinst, ";
        $sCampos .= " orcorgao.o40_orgao, ";
        $sCampos .= " o40_descr ";
        $sWhereBuscaOrgaos = " o40_anousu = {$iAnoSessao} ";
        $sWhereBuscaOrgaos .= " and o40_instit = {$iInstituicaoSessao} ";
        $sSqlBuscaOrgaos = $oDaoOrcorgao->sql_query(null, null, $sCampos, null, $sWhereBuscaOrgaos);

        $rsSqlBuscaOrgaos = $oDaoOrcorgao->sql_record($sSqlBuscaOrgaos);

        if (!$rsSqlBuscaOrgaos) {
            throw new \Exception("Erro ao consultar Orgao");
        }

        $aOrgaos = db_utils::getCollectionByRecord($rsSqlBuscaOrgaos);

        if (count($aOrgaos) > 0) {
            if (empty($this->sCodigoTribunal)) {
                throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
            }

            $RemessaOrgao = new \stdClass();
            $RemessaOrgao->Orgaos = [];

            foreach ($aOrgaos as $oOrgao) {
                $id = $oOrgao->o40_anousu . $iInstituicaoSessao . $oOrgao->o40_orgao;
                $Orgao = new \stdClass();
                $Orgao->Identificador = $id;
                $Orgao->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $Orgao->Ano = $oOrgao->o40_anousu;
                $Orgao->CodigoOrgao = $oOrgao->o40_orgao;
                $Orgao->DescricaoOrgao = Helper::convertAndLimit($oOrgao->o40_descr);

                $RemessaOrgao->Orgaos[] = (object)['Orgao' => $Orgao];
            }

            $this->aDados = $RemessaOrgao;
        }
    }
}
