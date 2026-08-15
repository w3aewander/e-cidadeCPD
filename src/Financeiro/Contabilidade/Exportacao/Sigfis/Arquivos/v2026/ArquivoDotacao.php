<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use db_utils;
use cl_orcdotacao;
use stdClass;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Xsd\XSDFactory;
use ECidade\Financeiro\Orcamento\Repository\FonteRecursoSiconfiRepository;




class ArquivoDotacao extends ArquivoBase
{
    protected $sNomeArquivo  = 'Dotacao';

    public function buscaTudo(){
        $sql = pg_query("SELECT codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso, sum(valordotacao) FROM etceado GROUP BY codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function gerarDados()
    {
        $clOrcDotacao = new cl_orcdotacao;

        $sCampos      = "orcdotacao.o58_anousu, orcdotacao.o58_coddot, orcdotacao.o58_orgao, orcdotacao.o58_unidade, ";
        $sCampos     .= "orcdotacao.o58_subfuncao, orcdotacao.o58_projativ, orcdotacao.o58_codigo, orcdotacao.o58_funcao, ";
        $sCampos     .= "orcdotacao.o58_programa, orcdotacao.o58_codele, orcdotacao.o58_valor, orcdotacao.o58_instit, ";
        $sCampos     .= "orcelemento.o56_elemento, orcelemento.o56_codele, orctiporec.o15_descr, orcprojativ.o55_tipo  ";
        $sOrder       = "orcdotacao.o58_coddot";
        $sWhere       = "     orcdotacao.o58_anousu = {$this->iAnoUsu}";
        $sWhere      .= " and orcdotacao.o58_instit = " . db_getsession('DB_instit');
    
        $sSqlOrcDotacao = $clOrcDotacao->sql_query_dotacao(null, null, $sCampos, $sOrder, $sWhere);
        $rsOrcDotacao   = $clOrcDotacao->sql_record($sSqlOrcDotacao);

        $fonteselemento = array(
          319008 => 339008,
          319009 => 339008,
          339018 => 339018,
          339040 => 339040,
          339014 => 339014,
          339030 => 339030,
          339036 => 339036,
          449052 => 449052,
          339039 => 339039,
          469071 => 469071,
          469073 => 469073,
          329021 => 329021,
          339047 => 339047,
          339096 => 339096,
          339092 => 339092,
          449040 => 449040,
          339045 => 339045
        );
    
    
        $fontes50 = array(
          6000 => 1600,
            6001 => 1600,
            6002 => 1600,
            6003 => 1600,
            6004 => 1600,
            6005 => 1600,
            6012 => 1601,
            6021 => 1602,
            6031 => 1603,
            6032 => 1600,
            6041 => 1604,
            6051 => 1605,
            6211 => 1621,
            6212 => 1621,
            6213 => 1621,
            6214 => 1621,
            6215 => 1621,
            6216 => 1621,
            6217 => 1621,
            6218 => 1621,
            6219 => 1621,
            6311 => 1631,
            6312 => 1632,
            6351 => 1635,
            6591 => 1501,
            6592 => 1501,
            6593 => 1600,
            6593 => 1500,
            6594 => 1600,
            6594 => 1500,
            6595 => 1500,
            6596 => 1600,
            6597 => 1600,
            6219 => 1621
        );

        $fontes65 = array(
        200 => 1501,
        163 => 1661,
        1660 => 164
        );

        $fontes96 = array(
          200 => 1500,
          23 => 1540,
          28 => 1550,
          11 => 1551,
          12 => 1552,
          45 => 1553,
          5 => 1569,
          211 => 1570,
          8 => 1573,
          34 => 1569
        );

        $fontes0 = array(
          5 => 1569,
6 => 1750,
8 => 1704,
8 => 1705,
10 => 1500,
11 => 1551,
12 => 1552,
14 => 1700,
16 => 1569,
18 => 1700,
21 => 1701,
22 => 1700,
23 => 1540,
24 => 1708,
28 => 1550,
29 => 1500,
34 => 1569,
44 => 1700,
45 => 1553,
50 => 1799,
62 => 1660,
68 => 1500,
78 => 1500,
80 => 1501,
92 => 1700,
97 => 1755,
97 => 1756,
98 => 1701,
101 => 1500,
102 => 1700,
104 => 1500,
105 => 1700,
108 => 1500,
111 => 1500,
117 => 1701,
124 => 1700,
131 => 1500,
134 => 6002,
136 => 1500,
138 => 1500,
139 => 1500,
145 => 1500,
147 => 1660,
148 => 1700,
149 => 1500,
150 => 1500,
151 => 1700,
157 => 1500,
158 => 1700,
160 => 1749,
161 => 1500,
164 => 1660,
167 => 1569,
170 => 1700,
171 => 1701,
173 => 1751,
178 => 1752,
200 => 1500,
201 => 1501,
202 => 1501,
203 => 1501,
204 => 1501,
205 => 1501,
206 => 1501,
207 => 1501,
208 => 1501,
209 => 1501,
210 => 1501,
211 => 1700,
211 => 1701,
211 => 1702,
211 => 1703,
212 => 1501,
213 => 1754,
214 => 1802,
215 => 1801,
216 => 1800,
219 => 1704,
219 => 1705,
220 => 1749,
224 => 1899,
225 => 1501,
1500 => 1500,
1569 => 1500,
1700 => 1500,
1703 => 1500,
1704 => 1500,
6002 => 1600,
6212 => 1621,
6594 => 1659,
6000 => 1600,
6001 => 1600,
6002 => 1600,
6003 => 1600,
6004 => 1600,
6005 => 1600,
6012 => 1601,
6021 => 1602,
6031 => 1603,
6032 => 1600,
6041 => 1604,
6051 => 1605,
6211 => 1621,
6212 => 1621,
6213 => 1621,
6214 => 1621,
6215 => 1621,
6216 => 1621,
6217 => 1621,
6218 => 1621,
6219 => 1621,
6311 => 1631,
6312 => 1632,
6351 => 1635,
6591 => 1501,
6592 => 1501,
6593 => 1600,
6593 => 1500,
6594 => 1600,
6594 => 1500,
6595 => 1500,
6596 => 1600,
6597 => 1600,
6219 => 1621
        );

        $RemessaDotacao = new stdClass();
        $dotacao = array();

        $xdados = [];
        if (pg_num_rows($rsOrcDotacao) > 0) {
            if (empty($this->sCodigoTribunal)) {
                throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
            }

            $ix = 1;
            $guardaconta = "";
            $guardavalor = 0;
            $guardatudo = array();

            
            for ($i = 0; $i < $clOrcDotacao->numrows; $i++) {
                $oDadosQuery  = new stdClass();
                $oDadosQuery  = db_utils::fieldsMemory($rsOrcDotacao, $i);
                                
                $oDados       = new stdClass();

                if ($oDadosQuery->o55_tipo == 0 or $oDadosQuery->o55_tipo == 9) $oDadosQuery->o55_tipo = 3;        
                $sUnidadeOrcamentaria = str_pad($oDadosQuery->o58_unidade, 4, ' ', STR_PAD_LEFT);
          
                $oDados->Identificador = $ix;
                $oDados->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDados->Ano = $oDadosQuery->o58_anousu;
                $oDados->CodigoOrgao = $oDadosQuery->o58_orgao;
                $oDados->CodigoUnidadeOrcamentaria = trim($sUnidadeOrcamentaria);
                $oDados->CodigoPrograma = $oDadosQuery->o58_programa;

                $tipodaacao = 0;
                if(strlen($oDadosQuery->o58_projativ) == 3){
                    $tipodaacao = 3;
                }else{
                    if((substr($oDadosQuery->o58_projativ, 0, 1) % 2) == 0){
                        $tipodaacao = 2;
                    } else {
                        $tipodaacao = 1;
                    }
                }
                $oDados->TipoAcao = $tipodaacao; //substr($oDadosQuery->o58_projativ, 0, 1); //$oDadosQuery->o55_tipo;          
                //$oDados->CodigoAcao = (strlen($oDadosQuery->o58_projativ) == 3 ) ? "0".$oDadosQuery->o58_projativ : $oDadosQuery->o58_projativ;
                $oDados->CodigoAcao = $oDadosQuery->o58_projativ;
                $oDados->CodigoFuncao = (strlen($oDadosQuery->o58_funcao) == 1) ? "0".$oDadosQuery->o58_funcao : $oDadosQuery->o58_funcao;
                $oDados->CodigoSubFuncao = (strlen($oDadosQuery->o58_subfuncao) == 2) ? "0".$oDadosQuery->o58_subfuncao : $oDadosQuery->o58_subfuncao;
                $oDados->NaturezaDespesa = (substr($oDadosQuery->o56_elemento,1,6) == 319009) ? 339008 : substr($oDadosQuery->o56_elemento,1,6);
                $oDados->CodigoFonteRecurso = ($oDadosQuery->o58_codigo == 1740) ? 1704 : $oDadosQuery->o58_codigo;

                if($oDados->NaturezaDespesa == 319034){
                    $oDados->NaturezaDespesa = 319004;
                }
                if($oDados->NaturezaDespesa == 337170){
                    $oDados->NaturezaDespesa = 337100;
                }
                if($oDados->NaturezaDespesa == 337170){
                    $oDados->NaturezaDespesa = 337100;
                }
                if($oDados->NaturezaDespesa == 337270){
                    $oDados->NaturezaDespesa = 337300;
                }
                if($oDados->NaturezaDespesa == 337270){
                    $oDados->NaturezaDespesa = 337300;
                }
                if($oDados->NaturezaDespesa == 447170){
                    $oDados->NaturezaDespesa = 447100;
                }
                if($oDados->NaturezaDespesa == 337270){
                    $oDados->NaturezaDespesa = 337300;
                }
                if($oDados->NaturezaDespesa == 337270){
                    $oDados->NaturezaDespesa = 337300;
                }
                if($oDados->NaturezaDespesa == 319048){
                    $oDados->NaturezaDespesa = 319094;
                }
                if($oDados->NaturezaDespesa == 319036){
                    $oDados->NaturezaDespesa = 339036;
                }

                if(db_getsession('DB_instit') == 50){
                    if($fontes50[$oDados->CodigoFonteRecurso]){
                        $oDados->CodigoFonteRecurso = $fontes50[$oDados->CodigoFonteRecurso];
                    }
            
                    if($fonteselemento[$oDados->NaturezaDespesa]){
                        $oDados->NaturezaDespesa = $fonteselemento[$oDados->NaturezaDespesa];
                    }
                }elseif(db_getsession('DB_instit') == 65){
                    if($fontes0[$oDados->CodigoFonteRecurso]){
                        $oDados->CodigoFonteRecurso = $fontes0[$oDados->CodigoFonteRecurso];
                    }
                }elseif(db_getsession('DB_instit') == 96){
                    if($fontes96[$oDados->CodigoFonteRecurso]){
                        $oDados->CodigoFonteRecurso = $fontes96[$oDados->CodigoFonteRecurso];
                    }
                }else{
                    if($fontes0[$oDados->CodigoFonteRecurso]){
                        $oDados->CodigoFonteRecurso = $fontes0[$oDados->CodigoFonteRecurso];
                    }
                }

                $oDados->ValorDotacao = number_format($oDadosQuery->o58_valor, 2 , ".", "");
                if(db_getsession('DB_instit') != 50){
                    if($oDados->NaturezaDespesa.$oDados->CodigoFonteRecurso.$oDadosQuery->o58_projativ == $guardaconta){
                        $guardatudo[$oDados->NaturezaDespesa.$oDados->CodigoFonteRecurso.$oDadosQuery->o58_projativ] += $oDados->ValorDotacao;
                        $guardaconta = $oDados->NaturezaDespesa.$oDados->CodigoFonteRecurso.$oDadosQuery->o58_projativ;
                        continue;
                    }
                    $guardaconta = $oDados->NaturezaDespesa.$oDados->CodigoFonteRecurso.$oDadosQuery->o58_projativ;          
                }
                $ix++;
                $xDados[] = $oDados;
            }//for

            
            $indice = 0;
            if(db_getsession('DB_instit') != 50){
              foreach ($xDados as $linha) {
                if(array_key_exists($linha->NaturezaDespesa, $guardatudo)){                    
                  $xDados[$indice]->ValorDotacao += $guardatudo[$linha->NaturezaDespesa];
                }
                $indice++;
              }
            }
            
            pg_query("CREATE TABLE etceado(identificador int, codigounidadegestora varchar(100), ano varchar(100), codigoorgao varchar(100),codigounidadeorcamentaria varchar(100), codigoprograma varchar(100), tipoacao int, codigoacao varchar(100), codigofuncao varchar(100), codigosubfuncao varchar(100), naturezadespesa varchar(100), codigofonterecurso int, valordotacao float)");
            
            foreach ($xDados as $linha) {              
              pg_query("INSERT INTO etceado(identificador, codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso, valordotacao) VALUES({$linha->Identificador}, '{$linha->CodigoUnidadeGestora}', '{$linha->Ano}', '{$linha->CodigoOrgao}', '{$linha->CodigoUnidadeOrcamentaria}', '{$linha->CodigoPrograma}', {$linha->TipoAcao}, '{$linha->CodigoAcao}', '{$linha->CodigoFuncao}', '{$linha->CodigoSubFuncao}', '{$linha->NaturezaDespesa}', {$linha->CodigoFonteRecurso}, {$linha->ValorDotacao})");
            }

            
            $cql = $this->buscaTudo();
            
            $ix2 = 1;
            foreach($cql as $linha){
              $oDadosDotacao = new stdClass();
              $oDadosDotacao->Identificador = $ix2;
              $oDadosDotacao->CodigoUnidadeGestora = $linha["codigounidadegestora"];
              $oDadosDotacao->Ano = $linha["ano"];
              $oDadosDotacao->CodigoOrgao = $linha["codigoorgao"];
              $oDadosDotacao->CodigoUnidadeOrcamentaria = $linha["codigounidadeorcamentaria"];
              $oDadosDotacao->CodigoPrograma = $linha["codigoprograma"];
              $oDadosDotacao->TipoAcao = $linha["tipoacao"];
              $oDadosDotacao->CodigoAcao = $linha["codigoacao"];
              $oDadosDotacao->CodigoFuncao = $linha["codigofuncao"];
              $oDadosDotacao->CodigoSubFuncao = $linha["codigosubfuncao"];
              $oDadosDotacao->NaturezaDespesa = $linha["naturezadespesa"];
              $oDadosDotacao->CodigoFonteRecurso = $linha["codigofonterecurso"];
              $oDadosDotacao->ValorDotacao =  $linha["sum"];
              $ix2++;

              $dotacao[] = (object) ['Dotacao' => $oDadosDotacao];
            }
            $RemessaDotacao->Dotacoes = $dotacao;
            pg_query("DROP TABLE etceado");

            /*
            for ($i = 0; $i < pg_num_rows($rsOrcDotacao); $i++) {
                $oDadosQuery = db_utils::fieldsMemory($rsOrcDotacao, $i);

                $oDadosDotacao = new stdClass();

                $identificador = date("Y", db_getsession("DB_datausu"));
                $identificador .= date("m", db_getsession("DB_datausu"));
                $identificador .= $oDadosQuery->o58_coddot;

                $oDadosDotacao->Identificador = $identificador;
                $oDadosDotacao->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDadosDotacao->Ano = $oDadosQuery->o58_anousu;
                $oDadosDotacao->CodigoOrgao = $oDadosQuery->o58_orgao;
                $oDadosDotacao->CodigoUnidadeOrcamentaria = $oDadosQuery->o58_unidade;
                $oDadosDotacao->CodigoPrograma = $oDadosQuery->o58_programa;
                $tipo = (!in_array($oDadosQuery->o55_tipo, [1,2])) ? 3 : $oDadosQuery->o55_tipo;
                $oDadosDotacao->TipoAcao =  $tipo;
                $oDadosDotacao->CodigoAcao = $oDadosQuery->o58_projativ;
                $oDadosDotacao->CodigoFuncao = $oDadosQuery->o52_siconfi;
                $oDadosDotacao->CodigoSubFuncao = $oDadosQuery->o53_siconfi;
                $oDadosDotacao->NaturezaDespesa = $oDadosQuery->natureza_despesa;
                $oDadosDotacao->CodigoFonteRecurso = $oDadosQuery->codigo_siconfi;
                $oDadosDotacao->ValorDotacao = $oDadosQuery->o58_valor;

                $dotacao[] = (object) ['Dotacao' => $oDadosDotacao];
            }*/
            //$RemessaDotacao->Dotacoes = $dotacao;
        }
        $this->aDados= $RemessaDotacao;
    }//função gera dados
}//classe
