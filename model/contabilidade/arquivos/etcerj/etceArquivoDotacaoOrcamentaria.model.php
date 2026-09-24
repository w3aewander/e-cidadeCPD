<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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

require_once  modification("interfaces/iPadArquivoTxtBase.interface.php");
require_once  modification("model/contabilidade/arquivos/sigfis/SigfisArquivoBase.model.php");


class etceArquivoDotacaoOrcamentaria extends SigfisArquivoBase implements iPadArquivoTXTBase {
  
  protected $iCodigoLayout     = 120;
  protected $sNomeArquivo      = 'Dotacao';

  public function nomexml(){
    return "RemessaDotacao";
  }

  public function abretag(){
    return "Dotacoes";
  }

  public function segundatag(){
    return "Dotacao";
  }

  public function getNomeElementos(){
    $elementos = array("Identificador", "CodigoUnidadeGestora", "Ano", "CodigoOrgao", "CodigoUnidadeOrcamentaria", "CodigoPrograma", "TipoAcao", "CodigoAcao", "CodigoFuncao", "CodigoSubFuncao", "NaturezaDespesa", "CodigoFonteRecurso", "ValorDotacao");
    return $elementos;
  }

  public function testax($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
  }

  public function buscaTudo(){
    $sql = pg_query("SELECT codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso, sum(valordotacao) FROM etceado GROUP BY codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso");
    $resultado = pg_fetch_all($sql);
    return $resultado;
  }
  
  /**
  * Busca os dados para gerar o Arquivo do Dotacao Orcamentaria
  */
  public function gerarDados() {
  
    /**
     * Busca os dados da db_config
     */
    $oDbConfig    = new db_stdClass();
    $oDadoConfig  = $oDbConfig->getDadosInstit();
    
    $clOrcDotacao = db_utils::getDao('orcdotacao');
    
    $sCampos      = "orcdotacao.o58_anousu, orcdotacao.o58_coddot, orcdotacao.o58_orgao, orcdotacao.o58_unidade, ";
    $sCampos     .= "orcdotacao.o58_subfuncao, orcdotacao.o58_projativ, orcdotacao.o58_codigo, orcdotacao.o58_funcao, ";
    $sCampos     .= "orcdotacao.o58_programa, orcdotacao.o58_codele, orcdotacao.o58_valor, orcdotacao.o58_instit, ";
    $sCampos     .= "orcelemento.o56_elemento, orcelemento.o56_codele, orctiporec.o15_descr, orcprojativ.o55_tipo  ";
    $sOrder       = "orcdotacao.o58_coddot";
    $sWhere       = "     orcdotacao.o58_anousu = {$this->iAnoUso}";
    $sWhere      .= " and orcdotacao.o58_instit = " . db_getsession('DB_instit');
    
    $sSqlOrcDotacao = $clOrcDotacao->sql_query_dotacao(null, null, $sCampos, $sOrder, $sWhere);
    $rsOrcDotacao   = $clOrcDotacao->sql_record($sSqlOrcDotacao);
    //var_dump($sSqlOrcDotacao); die("Sequel");

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
          200 => 1500,
          6000 => 1500,
          6001 => 1600,
          6002 => 1600,
          6003 => 1600,
          6004 => 1600,
          6005 => 1600,
          6012 => 1601,
          6021 => 1602,
          6031 => 1603,
          6032 => 1659,
          6041 => 1600,
          6051 => 1659,
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
          6312 => 1631,
          6351 => 1635,
          6591 => 1659,
          6592 => 1659,
          6593 => 1659,
          6594 => 1659,
          6595 => 1659,
          6596 => 1659,
          6597 => 1659
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
      28 => 1550,
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
      212 => 1501, 
      225 => 1501, 
      219 => 1704, 
      8 => 1704, 
      219 => 1705, 
      8 => 1705, 
      211 => 1700, 
      211 => 1701, 
      211 => 1702, 
      211 => 1703, 
      6 => 1750, 
      173 => 1751, 
      24 => 1708, 
      92 => 1700, 
      220 => 1749, 
      160 => 1749, 
      98 => 1701, 
      21 => 1701, 
      97 => 1755, 
      97 => 1756, 
      215 => 1801, 
      216 => 1800, 
      80 => 1501, 
      50 => 1799, 
      213 => 1754, 
      224 => 1899, 
      214 => 1802, 
      178 => 1752, 
      117 => 1701,
      23 => 1540,
11 => 1551,
45 => 1553,
34 => 1569,
164 => 1660,
148 => 1700,
22 => 1700,
105 => 1700,
18 => 1700,
44 => 1700,
14 => 1700,
124 => 1700,
158 => 1700,
151 => 1700
    );
    
    //$www = pg_fetch_all($rsOrcDotacao);
    //$this->testax($www);
    //die("Confere");

    $this->addLog("==== Iniciando Processamento Arquivo {$this->getNomeArquivo()}\n");
    $xdados = [];
    if ($clOrcDotacao->numrows > 0) {
      
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      $ix = 1;
      $guardaconta = "";
      $guardavalor = 0;
      $guardatudo = array();
      for ($i = 0; $i < $clOrcDotacao->numrows; $i++) {
        $oDadosQuery  = new stdClass();
        $oDadosQuery  = db_utils::fieldsMemory($rsOrcDotacao, $i);
        $oElementoTCE = SigfisVinculoDespesa::getVinculoDespesa($oDadosQuery->o56_codele);        
        $oDados       = new stdClass();
        
        $iCodigoRecursoTCE = '';
        if ($oRecursoTCE = SigfisVinculoRecurso::getVinculoRecurso($oDadosQuery->o58_codigo)) {
          $iCodigoRecursoTCE = $oRecursoTCE->recursotce;
        } else {          
          $sErroLog  = "Recurso {$oDadosQuery->o58_codigo} - {$oDadosQuery->o15_descr} ";
          $sErroLog .= "Sem Vinculo com Dotação {$oDadosQuery->o58_coddot} do SIGFIS.\n";
          $this->addLog($sErroLog);
        }
        
        //if(substr($oDadosQuery->o56_elemento,1,6) != 339039){continue;}
        //var_dump($oDadosQuery->o58_codigo);
        //if($oDadosQuery->o58_orgao != 5 && $oDadosQuery->o58_orgao != 10 && db_getsession('DB_instit') != 50){continue;}

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
          $oDados->CodigoAcao = (strlen($oDadosQuery->o58_projativ) == 3 ) ? "0".$oDadosQuery->o58_projativ : $oDadosQuery->o58_projativ;
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


          if(db_getsession('DB_instit') == 50){
            if($fontes50[$oDados->CodigoFonteRecurso]){
              $oDados->CodigoFonteRecurso = $fontes50[$oDados->CodigoFonteRecurso];
            }
            
            if($fonteselemento[$oDados->NaturezaDespesa]){
              $oDados->NaturezaDespesa = $fonteselemento[$oDados->NaturezaDespesa];
            }
          }elseif(db_getsession('DB_instit') == 65){
            if($fontes65[$oDados->CodigoFonteRecurso]){
              $oDados->CodigoFonteRecurso = $fontes65[$oDados->CodigoFonteRecurso];
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
          //$this->aDados[] = $oDados;
          $xDados[] = $oDados;
      }//for      
    }//if
    
          //$this->testax($guardatudo); die("Conf");
    
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

            //$tudo = pg_query("SELECT codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso, sum(valordotacao) FROM etceado GROUP BY codigounidadegestora, ano, codigoorgao, codigounidadeorcamentaria, codigoprograma, tipoacao, codigoacao, codigofuncao, codigosubfuncao, naturezadespesa, codigofonterecurso")
            //$rtudo = pg_fetch_all($tudo);
            //$this->testax($rtudo);
            $cql = $this->buscaTudo();
            
            $ix2 = 1;
            foreach($cql as $linha){
              $oDados2 = new stdClass();
              $oDados2->Identificador = $ix2;
              $oDados2->CodigoUnidadeGestora = $linha["codigounidadegestora"];
              $oDados2->Ano = $linha["ano"];
              $oDados2->CodigoOrgao = $linha["codigoorgao"];
              $oDados2->CodigoUnidadeOrcamentaria = $linha["codigounidadeorcamentaria"];
              $oDados2->CodigoPrograma = $linha["codigoprograma"];
              $oDados2->TipoAcao = $linha["tipoacao"];
              $oDados2->CodigoAcao = $linha["codigoacao"];
              $oDados2->CodigoFuncao = $linha["codigofuncao"];
              $oDados2->CodigoSubFuncao = $linha["codigosubfuncao"];
              $oDados2->NaturezaDespesa = $linha["naturezadespesa"];
              $oDados2->CodigoFonteRecurso = $linha["codigofonterecurso"];
              $oDados2->ValorDotacao =  $linha["sum"];
              $ix2++;
              $this->aDados[] = $oDados2;
            }
            //$this->aDados[] = "";
            //$this->aDados[] = $oDados2;
            //$this->testax($this->aDados);
            //die("Confere");
    
    $this->addLog("==== Fim do Arquivo {$this->getNomeArquivo()}\n");
    pg_query("DROP TABLE etceado");
  }
}
