<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2026;

use stdClass;
use db_utils;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Exception;

class ArquivoPPAPrograma extends ArquivoBase{
    //protected $iCodigoLayout = 113;
    protected $sNomeArquivo  = 'PPAPrograma';
    
    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function buscaPPA(){
        //Quando precisar dos outros anos, retirar o v.pl10_ano = 2026
        //Testar programa específico: AND p.o54_programa = 2620
        $sql = pg_query("SELECT pe.pl9_codigo, p.o54_programa AS codigo_programa, p.o54_descr AS descricao_programa, op.pl27_orcorgao AS codigo_orgao, v.pl10_ano AS ano, v.pl10_valor AS valor_programa, obj.pl11_numero AS numero_objetivo, obj.pl11_descricao AS descricao_objetivo FROM planejamento.programaestrategico pe JOIN orcamento.orcprograma p ON p.o54_programa = pe.pl9_orcprograma AND p.o54_anousu = pe.pl9_anoorcamento JOIN planejamento.orgaoprogramaestregico op ON op.pl27_programaestrategico = pe.pl9_codigo JOIN planejamento.valores v ON v.pl10_chave = pe.pl9_codigo AND v.pl10_origem = 'PROGRAMA ESTRATEGICO' AND v.pl10_ano = 2026 LEFT JOIN planejamento.objetivosprogramaestrategico obj ON obj.pl11_programaestrategico = pe.pl9_codigo WHERE EXISTS (SELECT 1 FROM orcamento.orcprograma WHERE o54_programa = pe.pl9_orcprograma AND o54_anousu = pe.pl9_anoorcamento AND o54_tipoprograma IN (3, 4)) ORDER BY p.o54_programa, op.pl27_orcorgao, obj.pl11_numero");
        
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    


    public function gerarDados(){
        $programas = $this->buscaPPA();
    
        $ix = 1;
        $lista = [];
        $confere = [];
        
        
        

        foreach ($programas as $programa) {
            /*
            $chave = $programa["codigo_programa"] . $programa["codigo_orgao"];
            if (in_array($chave, $confere)) {
                continue;
            }
            array_push($confere, $chave);
            */

            $oDadosPrograma = new stdClass();
            $oDadosPrograma->Identificador = $ix;
            $oDadosPrograma->CodigoUnidadeGestora = $programa["codigo_orgao"];
            //$oDadosPrograma->CodigoPPAPrograma = $programa["codigo_programa"];
            $oDadosPrograma->Codigo = $programa["codigo_programa"];
            $oDadosPrograma->Descricao = substr($programa["descricao_programa"], 0, 250);
            $oDadosPrograma->Ano = $programa["ano"];
            $oDadosPrograma->Valor = $programa["valor_programa"];
            $objetivo = stripslashes($objetivo);
            $objetivo = preg_replace('/\s+/', ' ', $objetivo);
            $objetivo = trim($objetivo);
            $oDadosPrograma->Objetivo = "Finalidade do Programa"; //substr($programa["descricao_objetivo"], 0, 2000);
            $lista[] = (object) ['PPAPrograma' => $oDadosPrograma];
            $ix++; 
        }
        $RemessaPPAPrograma = new stdClass();
        $RemessaPPAPrograma->PPAProgramas = $lista;
        $this->aDados = $RemessaPPAPrograma;
    }
}
