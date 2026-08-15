<? 


require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_utils.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("classes/db_matestoque_classe.php");
require_once("classes/db_matestoqueitem_classe.php");
require_once("classes/db_matestoqueini_classe.php");
require_once("classes/db_matestoqueinimei_classe.php");
require_once("classes/db_db_depart_classe.php");
require_once("classes/db_transmater_classe.php");
require_once("classes/db_empempitem_classe.php");
require_once("classes/db_empparametro_classe.php");
require_once("classes/db_matestoqueitemnotafiscalmanual_classe.php");
require_once("classes/materialestoque.model.php");
require_once("dbforms/db_funcoes.php");
require_once("libs/db_app.utils.php");

require_once("model/contabilidade/contacorrente/ContaCorrenteFactory.model.php");
require_once("model/contabilidade/contacorrente/ContaCorrenteBase.model.php");
require_once("model/financeiro/ContaBancaria.model.php");
require_once("model/contabilidade/planoconta/ContaPlano.model.php");
require_once("model/contabilidade/planoconta/ClassificacaoConta.model.php");
require_once("model/contabilidade/planoconta/ContaCorrente.model.php");
require_once("model/contabilidade/planoconta/ContaOrcamento.model.php");
require_once("model/contabilidade/planoconta/ContaPlanoPCASP.model.php");

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

db_postmemory($HTTP_POST_VARS);

function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}


function retornaProximoId(){
  $sql = pg_query("SELECT max(id) as id FROM controleentradanota");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["id"] + 1;
}

function retornaDescricao($codigo){
	$sql = pg_query("SELECT m60_descr FROM matmater WHERE m60_codmater = {$codigo}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["m60_descr"];
}

function buscaUnidade($codigo){
	$sql = pg_query("SELECT m61_abrev FROM matunid INNER JOIN matmater ON m61_codmatunid = m60_codmatunid WHERE m60_codmater = {$codigo}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["m61_abrev"];
}

function retornaCGM($codigo){
	$sql = pg_query("SELECT m76_numcgm FROM matfabricante WHERE m76_sequencial = {$codigo}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["m76_numcgm"];
}

function retornaSequencial($ano, $instituicao){
  $sql = pg_query("SELECT max(sequencial) as sequencial FROM controleentradanota WHERE anousuario = {$ano} AND instituicao = {$instituicao}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["sequencial"] + 1;
}

function confereNrmEmpenho($seq, $nf){	
	$sql = pg_query("SELECT * FROM controleentradanota WHERE sequencialempenho = {$seq} AND e69_numero = '{$nf}' AND (anulada != 'sim' OR anulada is null)");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}

function buscaCodigom66($codigomaterial){
	$sql1 = pg_query("SELECT m68_materialestoquegrupo from matmatermaterialestoquegrupo inner join matmater on matmater.m60_codmater = matmatermaterialestoquegrupo.m68_matmater inner join materialestoquegrupo on materialestoquegrupo.m65_sequencial = matmatermaterialestoquegrupo.m68_materialestoquegrupo inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join db_estruturavalor on db_estruturavalor.db121_sequencial = materialestoquegrupo.m65_db_estruturavalor where m68_matmater = {$codigomaterial}");
	$r1 = pg_fetch_all($sql1);
	$r1 = $r1[0]["m68_materialestoquegrupo"];
	$zano = db_getsession("DB_anousu");
	$sql2 = pg_query("SELECT m66_codcon from materialestoquegrupoconta where m66_materialestoquegrupo = {$r1} AND m66_anousu = {$zano} order by m66_materialestoquegrupo");
	$resultado = pg_fetch_all($sql2);
	return $resultado[0]["m66_codcon"];
}

$clempparametro     = new cl_empparametro;
$clmatestoque       = new cl_matestoque;
$clmatestoqueitem   = new cl_matestoqueitem;
$clmatestoqueini    = new cl_matestoqueini;
$clmatestoqueinimei = new cl_matestoqueinimei;
$cldb_depart        = new cl_db_depart;
$cltransmater       = new cl_transmater;
$clempempitem       = new cl_empempitem;
$oDaoMatEstoqueItemNotaFiscal = db_utils::getDao("matestoqueitemnotafiscalmanual");

$res_empparametro = $clempparametro->sql_record($clempparametro->sql_query(db_getsession("DB_anousu"),"e30_numdec"));
if ($clempparametro->numrows > 0){
  db_fieldsmemory($res_empparametro,0);
  if (trim($e30_numdec) == "" || $e30_numdec == 0){
    $numdec = 2;
  } else {
    $numdec = $e30_numdec;
  }
} else {
  $numdec = 2;
}

$db_opcao = 1;
$db_botao = true;
$passou   = false;

$iCodidoMovimentacaoEstoque = '';

/**
 * @todo refatorar para usar classe
 */




if (isset($incluir)) {
	$confere = confereNrmEmpenho($_POST["seqempenho"], $_POST["m79_notafiscal"]);
	if($confere){
		echo "<script>alert('NRM já existente para o empenho selecionado');</script>";
		$barra = "sim";
	}else{


	//testa($_POST);
	//die("Confere");
	$indice = 0;
	$codlancamento = array();
	foreach ($_POST["hm60_codmater"] as $linha) {

		
		
		$m60_codmater = $linha;		
		$coddepto = $_POST["hcoddepto"][$indice];
		$m71_quant = $_POST["hm71_quant"][$indice];
		$m71_valor = $_POST["hm71_valor"][$indice];		
		$m80_obs = $_POST["hm80_obs"][$indice];
		$m80_codtipo = $_POST["hm80_codtipo"][$indice];
		$m79_notafiscal = $_POST["hm79_notafiscal"][$indice];
		$m79_data = $_POST["hm79_data"][$indice];
		$m77_lote = $_POST["hm77_lote"][$indice];
		$m77_dtvalidade = $_POST["hm77_dtvalidade"][$indice];
		$m78_matfabricante = $_POST["hm78_matfabricante"][$indice];
		$m66_codcon = $_POST["hm66_codcon"][$indice];
		$e69_serienota = $_POST["he69_serienota"][$indice];
		$e69_subserienota = $_POST["he69_subserienota"][$indice];
		$numprocessolicit = $_POST["hnumprocessolicit"][$indice];

		if(empty($m80_obs)){
			$m80_obs = " ";
		}
		//testa($linha);

  		if (isset($m60_codmater) && trim($m60_codmater)!=""){
				if ($m71_valor == 0 or $m71_quant == 0) {
				$sqlerro = true;
				$erro_msg = "Valores zerados!";
				} else {
					$sqlerro = false;
					db_inicio_transacao();
					$result_matestoque = $clmatestoque->sql_record($clmatestoque->sql_query_file(null,"m70_codigo,m70_quant,m70_valor","","m70_codmatmater=$m60_codmater and m70_coddepto=$coddepto"));
					if($clmatestoque->numrows>0){
						db_fieldsmemory($result_matestoque,0);
						$quant = 0;
						$valor = 0;
						$quant = $m70_quant+$m71_quant;
						if ($quant > 0){
							$valor = $m70_valor+$m71_valor;
						}
						$clmatestoque->m70_valor = "$valor";
						$clmatestoque->m70_quant = "$quant";
						$clmatestoque->m70_codigo= $m70_codigo;
						$clmatestoque->alterar($m70_codigo);
						if($clmatestoque->erro_status==0){
							$sqlerro=true;
						}
						$erro_msg = $clmatestoque->erro_msg;
					}else{
						$clmatestoque->m70_codmatmater = $m60_codmater;
						$clmatestoque->m70_coddepto    = $coddepto;
						$clmatestoque->m70_valor       = $m71_valor;
						$clmatestoque->m70_quant       = $m71_quant;
						$clmatestoque->incluir(null);
						if($clmatestoque->erro_status==0){
							$sqlerro=true;
						}
						$m70_codigo = $clmatestoque->m70_codigo;
						$erro_msg   = $clmatestoque->erro_msg;
					}
				if($sqlerro == false){
					$clmatestoqueini->m80_login          = db_getsession("DB_id_usuario");
					$clmatestoqueini->m80_data           = date("Y-m-d",db_getsession("DB_datausu"));
					$clmatestoqueini->m80_hora           = date('H:i:s');
					$clmatestoqueini->m80_obs            = $m80_obs;
					$clmatestoqueini->m80_codtipo        = $m80_codtipo;
					$clmatestoqueini->m80_coddepto       = $coddepto;
					$clmatestoqueini->incluir(@$m80_codigo);
					if($clmatestoqueini->erro_status==0){
						$sqlerro=true;
					}
					$iCodidoMovimentacaoEstoque = $clmatestoqueini->m80_codigo;
					$m82_matestoqueini          = $clmatestoqueini->m80_codigo;
					$erro_msg                   = $clmatestoqueini->erro_msg;
					$codlancamento[$indice] = $clmatestoqueini->m80_codigo;
				}
				if($sqlerro == false){
					if(isset($m70_codigo) && trim($m70_codigo)!=""){
						$clmatestoqueitem->m71_codmatestoque = $m70_codigo;
						$clmatestoqueitem->m71_data          = date("Y-m-d",db_getsession("DB_datausu"));
						$clmatestoqueitem->m71_valor         = $m71_valor;
						$clmatestoqueitem->m71_quant         = $m71_quant;
						$clmatestoqueitem->m71_quantatend    = '0';
						$clmatestoqueitem->incluir(null);
						if($clmatestoqueitem->erro_status==0){
							$sqlerro=true;
						}
						$m80_matestoqueitem = $clmatestoqueitem->m71_codlanc;
						$erro_msg           = $clmatestoqueitem->erro_msg;
					}
					if (!$sqlerro) {
				  		/**
				   		* Inclui nota fiscal manual
				   		*/
				  		if (!empty($m79_notafiscal) && !empty($m79_data)) {
  				  			$oDaoMatEstoqueItemNotaFiscal->m79_sequencial     = null;
  				  			$oDaoMatEstoqueItemNotaFiscal->m79_matestoqueitem = $m80_matestoqueitem;
  				  			$oDaoMatEstoqueItemNotaFiscal->m79_notafiscal     = $m79_notafiscal;
  				  			$oDaoMatEstoqueItemNotaFiscal->m79_data           = $m79_data;
				    		$oDaoMatEstoqueItemNotaFiscal->incluir(null);
				  		}
					}

					if ($sqlerro == false) {
				  		if (trim($m77_lote) != "") {
				    		$clmatestoqueitemlote = db_utils::getDao("matestoqueitemlote");
				    		$clmatestoqueitemlote->m77_lote = $m77_lote;
				    		$clmatestoqueitemlote->m77_dtvalidade = implode("-",array_reverse(explode("/", $m77_dtvalidade)));
				    		$clmatestoqueitemlote->m77_matestoqueitem = $m80_matestoqueitem;
				    		$clmatestoqueitemlote->incluir(null);
				    		if ($clmatestoqueitemlote->erro_status == 0){
				      			$erro_msg = $clmatestoqueitemlote->erro_msg;
					    		$sqlerro  = true;
				    		}
				  		}
					}
					if (!$sqlerro) {
				  		if (trim($m78_matfabricante) != "") {
				    		$clmatestoqueitemfabric = db_utils::getDao("matestoqueitemfabric");
				    		$clmatestoqueitemfabric->m78_matestoqueitem = $m80_matestoqueitem;
				    		//$clmatestoqueitemfabric->m78_matfabricante  = $m78_matfabricante;
				    		$clmatestoqueitemfabric->m78_matfabricante  = 1;
				    		$clmatestoqueitemfabric->incluir(null);
				    		if ($clmatestoqueitemfabric->erro_status  == 0) {
				      			$erro_msg = $clmatestoqueitemfabric->erro_msg;
					    		$sqlerro  = true;
				    		}
				  		}
					}
					if ($sqlerro == false) {
						$clmatestoqueinimei->m82_matestoqueitem = $m80_matestoqueitem;
						$clmatestoqueinimei->m82_matestoqueini  = $m82_matestoqueini;
						$clmatestoqueinimei->m82_quant          = $m71_quant;
						$clmatestoqueinimei->incluir(@$m82_codigo);
						if($clmatestoqueinimei->erro_status==0){
							$erro_msg = $clmatestoqueinimei->erro_msg;
							$sqlerro=true;
						}
					}
				}

				if ($sqlerro==false){$passou=true;}      
      	
      			if ($sqlerro == false) {
        			$oDataImplantacao = new DBDate(date("Y-m-d", db_getsession('DB_datausu')));
        			$oInstituicao     = new Instituicao(db_getsession('DB_instit'));

			        /**
			         * Efetua os Lancamentos Contabeis de entrada no estoque
			         * - valida parametro de integracao da contabilidade com material
			         */
        			if ( USE_PCASP && ParametroIntegracaoPatrimonial::possuiIntegracaoMaterial($oDataImplantacao, $oInstituicao) ) {
          				try {
            				$oDadosEntrada                       = new stdClass();
            				$oDadosEntrada->iMovimentoEstoque    = $clmatestoqueinimei->m82_codigo;
            				$oDadosEntrada->sObservacaoHistorico = $m80_obs;
            				$oDadosEntrada->nValorLancamento     = $m71_valor;
            				$novom66 = buscaCodigom66($linha);            				
            				//$oDadosEntrada->iContaPCASP          = $m66_codcon;            				
            				$oDadosEntrada->iContaPCASP          = $novom66;
            				$oDadosEntrada->iCodigoMaterial      = $m60_codmater;
            				
            				
            				//testa($oDadosEntrada);
            				//var_dump($oDadosEntrada->iContaPCASP); die("Não pode ser vazio");
            				$oAlmoxarifado = new Almoxarifado(db_getsession('DB_coddepto'));

            				if (isset($entrada) && $entrada == "true") {
              					$oAlmoxarifado->entradaManual($oDadosEntrada);
            				} else {
              					$oAlmoxarifado->implantacaoEstoque($oDadosEntrada);
            				}
          				} catch (BusinessException $eErro) {
            				$sqlerro  = true;
            				$erro_msg = $eErro->getMessage();
          				} catch (ParameterException $eErro) {
            				$sqlerro  = true;
            				$erro_msg = ($eErro->getMessage());
	            			/**
	             			* Erro Originado por conta corrente:
	             			*/
            				if ($eErro->getCode() == '1010') {
              					$erro_msg .= "\nDicas: Verifique o cadastro das contas do grupo do material.";
            				}
          				} catch (Exception $eErro) {
            				$sqlerro  = true;
            				$erro_msg = ($eErro->getMessage());
          				}
        			}
      			}
				db_fim_transacao($sqlerro);			
				//exit;
			}//fim do else do material		

		
		

			if($sqlerro == false){
				//INSERE NA TABELA DE NOTAS
      			if ($sqlerro == false){
      				if(($m79_notafiscal) && ((int)$m79_notafiscal != 0)){
      					$idnrmnf = retornaProximoId();      					
      					$insere = pg_query($conn, "INSERT INTO notafiscalcoc(nota, serienf, subserienf, numprocessolicit, idnrm) VALUES(".$m79_notafiscal.", '".$e69_serienota."', '".$e69_subserienota."', '".$numprocessolicit."', {$idnrmnf})");

      					if($insere){
        					//Certo
      					} else {
         					$error = pg_last_error($conn);        	
      					}
      				}
      			}
      			
    			$indice++;
			}
  		}else{
    		$sqlerro = true;
    		$erro_msg = "Usuário: \\n\\nCódigo do material não informado.\\n\\nAdministrador:";
  		}
  	
  	}//end foreach
  	
  	//Inserir nrm aqui
  		if($sqlerro == false){
    		$id = retornaProximoId();
    		echo "<input type='hidden' name='id_nrm' id='id_nrm' value='{$id}'>";
    		$indice2 = 0;
    		$valortotal = 0;
    		foreach ($hm60_codmater as $linha) {

  				$m60_codmater = $linha;
  				$m60_descr = retornaDescricao($m60_codmater);  				
  				$m52_vlruni = $_POST["vu"][$indice2];
  				$m71_valor = $_POST["vt"][$indice2];
  				$m71_quant = $_POST["qt"][$indice2];
  				$m61_abrev = buscaUnidade($m60_codmater);
  				$e69_numemp = $seqempenho;
  				$instit = db_getsession('DB_instit');
  				$valortotal += $m71_valor;
  				$m80_codigo = $codlancamento[$indice2];

  				//$kql = "INSERT INTO materiaisnrm(idnrm, m60_codmater, m60_descr, m52_vlruni, m71_valor, m71_quant, m61_abrev, e69_numemp, instit) VALUES({$id}, {$m60_codmater}, '{$m60_descr}', '{$m52_vlruni}', '{$m71_valor}', {$m71_quant}, '{$m61_abrev}', {$e69_numemp}, {$instit})";
  				//echo $kql; echo "<br>";
  				pg_query("INSERT INTO materiaisnrm(idnrm, m60_codmater, m60_descr, m52_vlruni, m71_valor, m71_quant, m61_abrev, e69_numemp, instit, m80_codigo) VALUES({$id}, {$m60_codmater}, '{$m60_descr}', '{$m52_vlruni}', '{$m71_valor}', {$m71_quant}, '{$m61_abrev}', {$e69_numemp}, {$instit}, {$m80_codigo})");
				$indice2++;
			}





    		$nuinst = db_getsession('DB_instit');
			$e69_numero = $m79_notafiscal;
			$e69_dtnota = implode("-", array_reverse(explode("/", $m79_data)));
			$e69dtrecebe = $_POST["e69_dtrecebe"];
			
			
			if($e69dtrecebe == ""){
				$e69_dtrecebe = "null";
			}else{
				$e69_dtrecebe = implode("-", array_reverse(explode("/", $e69dtrecebe)));
				$e69_dtrecebe = "'{$e69_dtrecebe}'";
			}
			
			$diasistema = date("d/m/Y", db_getsession("DB_datausu"));
			$horasistema = date("H:i:s");

			$e70_valor = $valortotal;			
			$m51_codordem = $m51_codordem;
			//$m51_numcgm = retornaCGM($m78_matfabricante);
			$m51_numcgm = $m78_matfabricante;
			$codigopa = "";
			$iddousuario = db_getsession("DB_id_usuario");
			$anousuario = db_getsession("DB_anousu");
			//$datainclusao = date("d/m/Y H:i:s");
			$datainclusao = $diasistema . " " . $horasistema;
			$obs = $m80_obs;			
			$nsequencial = retornaSequencial($anousuario, $nuinst);
			$nsequencialnota = $nsequencial . "/" . $anousuario;
			$sequencialempenho = $seqempenho;

			
			if($m51_codordem != ""){
				//$xql1 = "INSERT INTO controleentradanota(id, instituicao, e69_numero, e69_dtnota, e69_dtrecebe, e70_valor, m51_codordem, m51_numcgm, codigopa, usuario, anousuario, sequencial, sequencialempenho, datainclusao, obs) VALUES({$id}, {$nuinst}, '{$e69_numero}', '{$e69_dtnota}', {$e69_dtrecebe}, {$e70_valor}, {$m51_codordem}, {$m51_numcgm}, '{$codigopa}', {$iddousuario}, {$anousuario}, {$nsequencial}, {$sequencialempenho}, '{$datainclusao}', '{$obs}' )";
				//var_dump($xql1);
				
				pg_query("INSERT INTO controleentradanota(id, instituicao, e69_numero, e69_dtnota, e69_dtrecebe, e70_valor, m51_codordem, m51_numcgm, codigopa, usuario, anousuario, sequencial, sequencialempenho, datainclusao, obs, manual) VALUES({$id}, {$nuinst}, '{$e69_numero}', '{$e69_dtnota}', {$e69_dtrecebe}, {$e70_valor}, {$m51_codordem}, {$m51_numcgm}, '{$codigopa}', {$iddousuario}, {$anousuario}, {$nsequencial}, {$sequencialempenho}, '{$datainclusao}', '{$obs}', 'sim')");
			}else{
				//$xql2 = "INSERT INTO controleentradanota(id, instituicao, e69_numero, e69_dtnota, e69_dtrecebe, e70_valor, m51_numcgm, codigopa, usuario, anousuario, sequencial, sequencialempenho, datainclusao, obs) VALUES({$id}, {$nuinst}, '{$e69_numero}', '{$e69_dtnota}', {$e69_dtrecebe}, {$e70_valor}, {$m51_numcgm}, '{$codigopa}', {$iddousuario}, {$anousuario}, {$nsequencial}, {$sequencialempenho}, '{$datainclusao}', '{$obs}' )";
				//var_dump($xql2); die("Confere"); 
				pg_query("INSERT INTO controleentradanota(id, instituicao, e69_numero, e69_dtnota, e69_dtrecebe, e70_valor, m51_numcgm, codigopa, usuario, anousuario, sequencial, sequencialempenho, datainclusao, obs, manual) VALUES({$id}, {$nuinst}, '{$e69_numero}', '{$e69_dtnota}', {$e69_dtrecebe}, {$e70_valor}, {$m51_numcgm}, '{$codigopa}', {$iddousuario}, {$anousuario}, {$nsequencial}, {$sequencialempenho}, '{$datainclusao}', '{$obs}', 'sim')");
			}
			//$kql2 = "INSERT INTO controleentradanota(id, instituicao, e69_numero, e69_dtnota, e69_dtrecebe, e70_valor, m51_codordem, m51_numcgm, codigopa, usuario, anousuario, sequencial, sequencialempenho, datainclusao, obs) VALUES({$id}, {$nuinst}, '{$e69_numero}', '{$e69_dtnota}', '{$e69_dtrecebe}', {$e70_valor}, {$m51_codordem}, {$m51_numcgm}, '{$codigopa}', {$iddousuario}, {$anousuario}, {$nsequencial}, {$sequencialempenho}, '{$datainclusao}', '{$obs}' )";
			//echo $kql2; echo "<br>";
			//
			
		}
}
//die("Fim dos fins final");

}//if incluir
if (!isset($coddepto)||$coddepto==""){
  $result_departamento = $cldb_depart->sql_record($cldb_depart->sql_query_file(db_getsession("DB_coddepto"),"coddepto,descrdepto"));
  db_fieldsmemory($result_departamento,0);
}
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="document.form1.m60_codmater.focus();" >
<table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
<tr>
<td width="360" height="18">&nbsp;</td>
<td width="263">&nbsp;</td>
<td width="25">&nbsp;</td>
<td width="140">&nbsp;</td>
</tr>
</table>
<center>
<?
  include("forms/db_frmmatestoqueininrm.php");  
?>



</center>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>

<script>
	function imprime(id){
  	//var id = document.getElementById("id_nrm").value;
		console.log("zzzzzzzzzzzzzzzzzzzzzzz");
		console.log("Entrou?");
  	var jan = window.open('rel_entradanotaavulsa.php?id='+id,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  	console.log(jan);
  	console.log("zzzzzzzzzzzzzzzzzzzzzzz");
    jan.moveTo(0,0);
  	
  }


  function abreJanela(){
  	//var id = document.getElementById("id_nrm").value;
  	var id = 5627;
  	var jan = window.open('rel_entradanotaavulsa.php?id='+id,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
    jan.moveTo(0,0);
  	
  }
</script>

</body>
</html>
<?
if(isset($incluir)){
	if(isset($barra) && $barra = "sim"){continue;}


  db_msgbox($erro_msg);

  echo "
    <script>
  		if(confirm('Deseja imprimir a NRM?')){
  			var id = document.getElementById('id_nrm').value;
  			imprime(id);
  }
  </script>";
  ?>

  <?php  

  if($sqlerro==false){        
      $location = "?entrada=true";
    }
    echo "
    <script>   
    location.href = 'mat1_matestoqueini001nrm.php$location'; 
    </script>
    ";
  

}
?>