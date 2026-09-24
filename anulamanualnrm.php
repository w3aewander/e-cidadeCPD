<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("classes/db_matestoque_classe.php"));
require_once(modification("classes/db_matestoqueitem_classe.php"));
require_once(modification("classes/db_matestoqueini_classe.php"));
require_once(modification("classes/db_matestoqueinil_classe.php"));
require_once(modification("classes/db_matestoqueinill_classe.php"));
require_once(modification("classes/db_matestoqueinimei_classe.php"));
require_once(modification("classes/materialestoque.model.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_app.utils.php"));

require_once(modification("model/contabilidade/contacorrente/ContaCorrenteFactory.model.php"));
require_once(modification("model/contabilidade/contacorrente/ContaCorrenteBase.model.php"));
require_once(modification("model/financeiro/ContaBancaria.model.php"));
require_once(modification("model/contabilidade/planoconta/ContaPlano.model.php"));
require_once(modification("model/contabilidade/planoconta/ClassificacaoConta.model.php"));
require_once(modification("model/contabilidade/planoconta/ContaCorrente.model.php"));
require_once(modification("model/contabilidade/planoconta/ContaOrcamento.model.php"));
require_once(modification("model/contabilidade/planoconta/ContaPlanoPCASP.model.php"));


db_app::import("exceptions.*");
db_app::import("contabilidade.*");
db_app::import("contabilidade.lancamento.*");
db_app::import("estoque.*");
db_app::import("Acordo");
db_app::import("AcordoComissao");
db_app::import("CgmFactory");
db_app::import("financeiro.*");
db_app::import("contabilidade.*");
db_app::import("contabilidade.lancamento.*");
db_app::import("Dotacao");

db_app::import("contabilidade.contacorrente.*");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaDados($idnrm){
  $sql = pg_query("SELECT m80_codigo, obs FROM controleentradanota INNER JOIN materiaisnrm ON controleentradanota.id = materiaisnrm.idnrm WHERE idnrm = {$idnrm}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

$idnrm = $_GET["id"];
$dadosuteis = retornaDados($idnrm);

function buscaDadosMateriais($codlancamento){
  $sql = pg_query("SELECT * FROM materiaisnrm WHERE m80_codigo = {$codlancamento}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaNRM($idnrm){
  $sql = pg_query("SELECT * FROM controleentradanota WHERE id = {$idnrm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaEmpenhoPorSeq($seq){
  $sql = pg_query("SELECT e60_codemp||'/'||e60_anousu as empenho FROM empempenho WHERE e60_numemp = {$seq}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["empenho"];
}

function buscaValorTotalEmpenho($seq){
  $sql = pg_query("SELECT e60_vlremp FROM empempenho WHERE e60_numemp = {$seq}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["e60_vlremp"];
}

function buscaValorTotalItens($idnrm, $lancamento){ 
  $sql = pg_query("SELECT sum(m71_valor) as total from materiaisnrm WHERE idnrm = {$idnrm} AND m80_codigo != {$lancamento}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["total"] ? $resultado[0]["total"] : 0;
}

function buscaItensPorNrm($nrm){
  $sql = pg_query("SELECT * FROM materiaisnrm WHERE idnrm = {$nrm}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


function retornaValores($m80_codigo){
  $sql = pg_query("SELECT matestoqueini.m80_codigo, m70_codigo, m71_codlanc, m71_quantatend, m70_quant, m60_codmater, m60_descr, coddepto, descrdepto, m71_quant, m77_lote, m77_dtvalidade, m78_matfabricante, m76_nome, m71_valor, m79_sequencial, m79_notafiscal, m79_data, (m71_valor/m71_quant) as m71_valorunit, matestoqueini.m80_obs from matestoqueini inner join matestoqueinimei on matestoqueinimei.m82_matestoqueini = matestoqueini.m80_codigo left join matestoquetransf on matestoquetransf.m83_matestoqueini = matestoqueini.m80_codigo inner join matestoqueitem on matestoqueitem.m71_codlanc = matestoqueinimei.m82_matestoqueitem left join matestoqueitemlote on matestoqueitem.m71_codlanc = matestoqueitemlote.m77_matestoqueitem left join matestoqueitemfabric on matestoqueitem.m71_codlanc = matestoqueitemfabric.m78_matestoqueitem left join matfabricante on matestoqueitemfabric.m78_matfabricante = matfabricante.m76_sequencial inner join matestoque on matestoque.m70_codigo = matestoqueitem.m71_codmatestoque inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join db_usuarios on db_usuarios.id_usuario = matestoqueini.m80_login inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto left join matestoqueinil on matestoqueinil.m86_matestoqueini = matestoqueini.m80_codigo left join matestoqueinill on matestoqueinill.m87_matestoqueinil = matestoqueinil.m86_codigo left join matestoqueini b on b.m80_codigo = matestoqueinill.m87_matestoqueini left join matestoqueitemnotafiscalmanual on matestoqueitemnotafiscalmanual.m79_matestoqueitem = matestoqueitem.m71_codlanc where matestoqueini.m80_codigo={$m80_codigo} and m71_quantatend=0");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
db_postmemory($HTTP_POST_VARS);
$clmatestoque = new cl_matestoque;
$clmatestoqueitem = new cl_matestoqueitem;
$clmatestoqueini = new cl_matestoqueini;
$clmatestoqueinil = new cl_matestoqueinil;
$clmatestoqueinill = new cl_matestoqueinill;
$clmatestoqueinimei = new cl_matestoqueinimei;
$db_botao = false;
$db_opcao = 33;

$iCodidoMovimentacaoEstoque = '';

$excluir = "sim";
foreach($dadosuteis as $linha){
if ($excluir == "sim") {
  
  $m80_codigo = $linha["m80_codigo"];
  $m80_obs = $linha["obs"];

  $dadosnecessarios = retornaValores($m80_codigo);
  
  $m70_codigo = $dadosnecessarios["m70_codigo"];
  $m71_codlanc = $dadosnecessarios["m71_codlanc"];
  $m60_codmater = $dadosnecessarios["m60_codmater"];
  

  $sqlerro = false;
  $m80_codigo = (isset($m80_codigo)&&!empty($m80_codigo))?$m80_codigo:'null';
  $sSql = $clmatestoqueini->sql_query_mater(null,"matestoqueini.m80_codigo,
                                                                                            m70_codigo,
                                                                                            m71_codlanc,
                                                                                            m70_valor,
                                                                                            m70_codmatmater,
                                                                                            m70_coddepto,
                                                                                            m70_quant,
                                                                                            m71_quant,
                                                                                            m71_quantatend"
    ,"","matestoqueini.m80_codigo=$m80_codigo
                                                                                            and m70_codigo=$m70_codigo
                                                                                            and m71_codlanc=$m71_codlanc
                                                                                            and m71_quantatend=0");
  
  $result_matestoque = $clmatestoqueini->sql_record($sSql);
  

  if ($clmatestoqueini->numrows>0) {
    
    db_inicio_transacao();
    db_fieldsmemory($result_matestoque,0);
    MaterialEstoque::bloqueioMovimentacaoItem($m70_codmatmater, $m70_coddepto);

    if($m71_quantatend==0){

      $clmatestoqueinil->m86_matestoqueini = $m80_codigo;
      $clmatestoqueinil->incluir(null);
      $vaipromatestoqueinill = $clmatestoqueinil->m86_codigo;
      if($clmatestoqueinil->erro_status==0){        
        $erro_msg = $clmatestoqueinil->erro_msg;
        $sqlerro=true;
      }
      
      $result_data_registro = $clmatestoqueini->sql_record($clmatestoqueini->sql_query_file("","m80_data, m80_hora","","m80_codigo=$m80_codigo"));
      if($clmatestoqueini->numrows>0){
        db_fieldsmemory($result_data_registro,0);
        
        if ( date("Y-m-d",db_getsession("DB_datausu")) < $m80_data ){          
          $erro_msg = 'Data atual é anterior a data do registro, cancelamento abortado!';
          $sqlerro=true;
        } else {
          if ( date("Y-m-d",db_getsession("DB_datausu")) == $m80_data ){
        	   if ( db_hora() <=  $m80_hora){              
        	   	 $erro_msg = 'Hora atual dever ser posterior a hora e data do registro, cancelamento abortado!';
        	   	 $sqlerro=true;
        	   }
          }
        }
      }
      
      $m80_login = db_getsession("DB_id_usuario");
      $m80_data  = date("Y-m-d",db_getsession("DB_datausu"));
      $m80_hora  = date('H:i:s');
      $m80_coddepto = db_getsession("DB_coddepto");
      
      if ($sqlerro==false) {        
				$clmatestoqueini->m80_login          = $m80_login;
		    $clmatestoqueini->m80_data           = $m80_data;
				$clmatestoqueini->m80_hora           = $m80_hora;
				$clmatestoqueini->m80_obs            = $m80_obs;
				$clmatestoqueini->m80_codtipo        = 4;
				$clmatestoqueini->m80_coddepto       = $m80_coddepto;        
				$clmatestoqueini->incluir(null);

				$iCodidoMovimentacaoEstoque = $clmatestoqueini->m80_codigo;
				$matestoqueininovo          = $clmatestoqueini->m80_codigo;
				$erro_msg = $clmatestoqueini->erro_msg;
				if($clmatestoqueini->erro_status==0){          				  
          $sqlerro=true;
				  $erro_msg = $clmatestoqueini->erro_msg;
				}
		  }

      if($sqlerro==false){
				$clmatestoqueinill->m87_matestoqueini  = $matestoqueininovo;
				$clmatestoqueinill->m87_matestoqueinil = $vaipromatestoqueinill;
				$clmatestoqueinill->incluir($vaipromatestoqueinill);
				if($clmatestoqueinill->erro_status==0){
				  
          $erro_msg = $clmatestoqueinill->erro_msg;
				  $sqlerro=true;
				}
			}

      $quantestoque = $m70_quant-$m71_quant;
      $valorestoque = $m70_valor-$m71_valor;

      if($sqlerro==false){
				$clmatestoque->m70_codigo = $m70_codigo;
			  $clmatestoque->m70_valor  = "$valorestoque";
				$clmatestoque->m70_quant  = "$quantestoque";
				$clmatestoque->alterar($m70_codigo);
				if($clmatestoque->erro_status==0){
          
				  $erro_msg = $clmatestoque->erro_msg;
				  $sqlerro=true;
				}
			}

      if($sqlerro==false){
        $clmatestoqueitem->m71_codlanc    = $m71_codlanc;
        $clmatestoqueitem->m71_quantatend = $m71_quant;
				$clmatestoqueitem->alterar($m71_codlanc);
				if($clmatestoqueitem->erro_status==0){
          
				  $erro_msg = $clmatestoqueitem->erro_msg;
				  $sqlerro=true;
				}
      }

      if($sqlerro == false){
        $clmatestoqueinimei->m82_matestoqueitem = $m71_codlanc;
        $clmatestoqueinimei->m82_matestoqueini  = $matestoqueininovo;
        $clmatestoqueinimei->m82_quant          = $m71_quant;
        $clmatestoqueinimei->incluir(null);
        if($clmatestoqueinimei->erro_status==0){
          die("5");
          $erro_msg = $clmatestoqueiniimei->erro_msg;
          $sqlerro=true;
        }
      }

      $oInstituicao = new Instituicao(db_getsession("DB_instit"));
      $dtAtual      = date("Y-m-d", db_getsession("DB_datausu"));
      $oDataAtual   = new DBDate($dtAtual);      
      
      if ($sqlerro == false && USE_PCASP  &&  (ParametroIntegracaoPatrimonial::possuiIntegracaoMaterial($oDataAtual, $oInstituicao) ))  {        
        try {
          $oDadosEntrada                       = new stdClass();
          $sSqlBuscaValorEntrada = "select m89_valorfinanceiro 
                                      from matestoqueinimei 
                                           inner join matestoqueinimeipm on m89_matestoqueinimei =  m82_codigo 
                                     where m82_matestoqueini = {$m80_codigo} ";
          $rsBuscaValorEntrada = db_query($sSqlBuscaValorEntrada);          
          $nValor = db_utils::fieldsMemory($rsBuscaValorEntrada, 0)->m89_valorfinanceiro;

          
          $oMaterialEstoque = new materialEstoque($m60_codmater);
          
          $oDadosEntrada->iMovimentoEstoque    = $clmatestoqueinimei->m82_codigo;
          $oDadosEntrada->sObservacaoHistorico = $m80_obs;
          $oDadosEntrada->nValorLancamento     = round($nValor, 2);
          $oDadosEntrada->iContaPCASP          = $m66_codcon;
          $oDadosEntrada->iCodigoMaterial      = $m60_codmater;
          $oAlmoxarifado = new Almoxarifado(db_getsession('DB_coddepto'));
          $oAlmoxarifado->saidaManual($oDadosEntrada);

        } catch (BusinessException $eErro) {
          //Erro aqui
          $sqlerro  = true;
          $erro_msg = ($eErro->getMessage());

        } catch (Exception $eErro) {
          
          $sqlerro  = true;
          $erro_msg = ($eErro->getMessage());
        } catch (ParameterException $eErro) {
          
          $sqlerro  = true;
          $erro_msg = $eErro->getMessage();
        }        
      }
      
      if($sqlerro == false){
        pg_query("UPDATE materiaisnrm SET anulada = 'sim' WHERE idnrm = {$idnrm} AND m80_codigo = {$m80_codigo}");
        pg_query("UPDATE controleentradanota SET anulada = 'sim' WHERE id = {$idnrm} ");
      }
      db_fim_transacao($sqlerro);
    }//qtd = 0
  }//rows > 0
}//if excluir
}//foreach

if($sqlerro == false){  
  db_msgbox(utf8_decode("Anulação feita com sucesso"));
  echo "<script>location.href='mat1_matestoqueini003nrm.php?entrada=true';</script>";
}else{
  echo "<script>alert('".$erro_msg."');</script>";
  sleep(3);
  echo "<script>location.href='mat1_matestoqueini003nrm.php?entrada=true';</script>";
}