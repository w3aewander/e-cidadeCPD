<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use cl_orcunidade;
use db_utils;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;

class ArquivoUnidadeOrcamentaria extends ArquivoBase
{
    protected $iCodigoLayout = 113;
    protected $sNomeArquivo  = 'UnidadeOrcamentaria';

    /**
    * Busca os dados para gerar o Arquivo de Unidade Orçamentária
    */
    public function gerarDados()
    {

        $clOrcUnidade   = new cl_orcunidade;
        $sCampos        = "orcunidade.o41_anousu, orcunidade.o41_unidade, orcunidade.o41_descr, orcunidade.o41_orgao";
        $sWhere         = "     o41_instit = " .db_getsession("DB_instit");
        $sWhere        .= " and o41_anousu = {$this->iAnoUsu}";
        $sSqlOrcUnidade = $clOrcUnidade->sql_query_file(null, null, null, $sCampos, null, $sWhere);
        $rsOrcUnidade   = db_query($sSqlOrcUnidade);

        if (pg_num_rows($rsOrcUnidade) > 0) {
            if (empty($this->sCodigoTribunal)) {
                throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
            }

            $RemessaPPAProgramaIndicador = new \stdClass();
            $unidadeOrcamentaria = array();

            for ($i = 0; $i < pg_num_rows($rsOrcUnidade); $i++) {
                $oDadosQuery = db_utils::fieldsMemory($rsOrcUnidade, $i);

                $identificador = date("Y", db_getsession("DB_datausu"));
                $identificador .= date("m", db_getsession("DB_datausu"));
                $identificador .= $oDadosQuery->o41_orgao.$oDadosQuery->o41_unidade;

                $oDadosUnidadeOrcamentaria = new \stdClass();
                $oDadosUnidadeOrcamentaria->Identificador = $identificador;
                $oDadosUnidadeOrcamentaria->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDadosUnidadeOrcamentaria->Ano = $oDadosQuery->o41_anousu;
                $oDadosUnidadeOrcamentaria->CodigoOrgao =$oDadosQuery->o41_orgao ;
                $oDadosUnidadeOrcamentaria->CodigoUnidadeOrcamentaria = $oDadosQuery->o41_unidade;
                $descricaoUnidade = Helper::convertAndLimit($oDadosQuery->o41_descr, 250);
                $oDadosUnidadeOrcamentaria->DescricaoUnidadeOrcamentaria = $descricaoUnidade;

                $unidadeOrcamentaria[] =  (object) ['UnidadeOrcamentaria' => $oDadosUnidadeOrcamentaria];
            }

            $RemessaPPAProgramaIndicador->UnidadesOrcamentarias = $unidadeOrcamentaria;
            $this->aDados= $RemessaPPAProgramaIndicador;
        }
    }
}
