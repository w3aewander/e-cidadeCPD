<?

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("classes/db_protprocesso_classe.php"));
require_once(modification("classes/db_procdoctipo_classe.php"));
require_once(modification("classes/db_protparam_classe.php"));
require_once(modification("classes/db_procvar_classe.php"));
require_once(modification("classes/db_andpadrao_classe.php"));
require_once(modification("classes/db_proctipovar_classe.php"));
require_once(modification("classes/db_procprocessodoc_classe.php"));
require_once(modification("classes/db_db_depusu_classe.php"));
require_once(modification("classes/db_db_syscampo_classe.php"));
require_once(modification("dbforms/db_funcoes.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaLogin($idusuario){
  $sql = pg_query("SELECT login FROM db_usuarios WHERE id_usuario = {$idusuario}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["login"];
}

db_postmemory($HTTP_SERVER_VARS);
db_postmemory($HTTP_POST_VARS);

$oPost = db_utils::postMemory($_POST,0);
$oGet  = db_utils::postMemory($_GET,0);

$clprotprocesso    = new cl_protprocesso;
$clprocprocessodoc = new cl_procprocessodoc;
$clproctipovar     = new cl_proctipovar;
$clandpadrao       = new cl_andpadrao;
$cldepusu          = new cl_db_depusu;
$clprotparam       = new cl_protparam;
$cldoc             = new cl_procprocessodoc;

$db_opcao = 22;
$db_botao = false;

if($_POST){  
  $codproc = $_POST["p58_codproc"];
  $codigop58 = $_POST["p58_codigo"];
  $codcgm = $_POST["p58_numcgm"];
  $nome = $_POST["z01_nome"];
  $obs = $_POST["p58_obs"];  
  
  $atualiza = pg_query($conn, "UPDATE protprocesso SET p58_codigo = {$codigop58}, p58_numcgm = {$codcgm}, p58_requer = '{$nome}', p58_obs = '{$obs}' WHERE p58_codproc = {$codproc}");  
  
  if($atualiza){
    $loginusuario = retornaLogin(db_getsession("DB_id_usuario"));
    $hoje = date("Y-m-d");
    pg_query("INSERT INTO historicomanproc(p58_codproc, p58_codigo, p58_numcgm, p58_requer, p58_obs, loginusuario, data) VALUES({$codproc}, {$codigop58}, {$codcgm}, '{$nome}', '{$obs}', '{$loginusuario}', '{$hoje}')");
      //header("Location:preorcrece.php?t=2");
      //$error = pg_last_error($conn);
    }

  $db_opcao = 3;
  echo utf8_decode("<script>alert('Alteração feita com sucesso.');</script>");
  
}



if (isset($oGet->chavepesquisa) && $oGet->chavepesquisa != "") {
	$p58_codproc = $oGet->chavepesquisa;
}

if (isset($oGet->p58_codigo) && $oGet->p58_codigo != "") {
	$p58_codigo = $oGet->p58_codigo;
}

if(isset($btnalterar) && $btnalterar == 2){


}elseif(!isset($btnalterar) ){
    if(isset($chavepesquisa) ){
       $db_opcao = 2;
       $result   = $clprotprocesso->sql_record($clprotprocesso->sql_query($chavepesquisa)); 
       db_fieldsmemory($result,0);
       $db_botao = true;
       $result_andam = $clprotprocesso->sql_record($clprotprocesso->sql_query_alt($p58_codproc,"*",null,"p58_codproc = $p58_codproc and p61_codandam is null and p63_codtran is null "));
       if ($clprotprocesso->numrows==0){
           $db_opcao = 3;
          $db_botao  = false;
      }
   }
}else{
//     include(modification("classes/db_procdoctipo_classe.php"));
       $cldoc = new cl_procdoctipo;
       $res = $cldoc->sql_record($cldoc->sql_query($p58_codigo,"","p56_coddoc,p56_descr"));
        $db_opcao = 2;
        $db_botao = true;

}

if (!isset($btnalterar)){
      $btnalterar = 2;
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
<?
if (isset($oGet->alt) && $oGet->alt == 1) {
  if(isset($db_opcao)){
  	$sOnLoad = " onload='js_preenchepesquisa(".$oGet->chavepesquisa.");'";
  }
} else {
	 $sOnLoad = " onload='a=1'";
}
?>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" <?=$sOnLoad?>>
<form name="form1" method="post" action="" onsubmit="return js_validaObservacao();">
<br /><br />
<center>
<table width="790" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="430" align="center" valign="top" bgcolor="#CCCCCC">
      <?
         include("forms/frm_manutencaoprocesso.php");
      ?>
    </td>
  </tr>
</table>
</center>
</form>
</body>
</html>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
<?
if ((isset($HTTP_POST_VARS["db_opcao"]) && $HTTP_POST_VARS["db_opcao"]) == "Alterar") {
  echo "<script> window.open('pro4_capaprocesso.php?codproc=".$clprotprocesso->p58_codproc."','','location=0'); </script>";
  $result_param = $clprotparam->sql_record($clprotparam->sql_query_file());
  if ($clprotparam->numrows>0) {
    db_fieldsmemory($result_param,0);
    if ($p90_emiterecib == "t") {
      echo "<script>
          if (confirm('Deseja Emitir Recibo?')) {
            location.href='cai4_recibo001.php?p58_codproc=$p58_codproc&codtipo=$p58_codigo&incproc=true&mostramenu=true&sIframe=iframe_dadosprocesso';
          } else {
            location.href='pro4_aba1protprocesso002.php';
          }
           </script>";

    } else {
        echo "<script>location.href='pro4_aba1protprocesso002.php';</script>";
    }
  } else {
      echo "<script>location.href='pro4_protprocesso002.php';</script>";
  }
}
?>
