<?php
 
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaAutorizacoes(){
  $sql = pg_query("SELECT e54_autori,e54_emiss,e54_numcgm,z01_nome,db_usuarios.login, case when e61_numemp is not null then e60_codemp else '' end as e60_codemp from empautoriza inner join cgm on cgm.z01_numcgm = empautoriza.e54_numcgm inner join db_config on db_config.codigo = empautoriza.e54_instit inner join db_usuarios on db_usuarios.id_usuario = empautoriza.e54_login inner join db_depart on db_depart.coddepto = empautoriza.e54_depto inner join pctipocompra on pctipocompra.pc50_codcom = empautoriza.e54_codcom inner join concarpeculiar on concarpeculiar.c58_sequencial = empautoriza.e54_concarpeculiar left join empempaut on empautoriza.e54_autori = empempaut.e61_autori left join empempenho on empempenho.e60_numemp = empempaut.e61_numemp left join empautidot on e56_autori = empautoriza.e54_autori and e56_anousu=e54_anousu left join orcdotacao on e56_Coddot = o58_coddot and e56_anousu = o58_anousu where e61_autori is null and e54_anulad is null and e54_instit = 50 order by e54_autori desc");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function retornaUsuarios50(){
  $anousu = db_getsession("DB_anousu");
  //$sql = pg_query("SELECT DISTINCT db_usuarios.id_usuario,db_usuarios.nome FROM db_usuarios INNER JOIN db_depusu ON db_depusu.id_usuario = db_usuarios.id_usuario INNER JOIN db_depart ON db_depusu.coddepto = db_depart.coddepto WHERE db_depart.instit = 50 ORDER BY nome");
  $sql = pg_query("SELECT 'Usuário'::varchar as dl_usuario,null::varchar as dl_tipo,u.login,u.nome,u.email, u.id_usuario from db_permissao p INNER JOIN db_usuarios u on u.id_usuario = p.id_usuario INNER JOIN db_itensmenu i ON i.id_item = p.id_item WHERE p.id_item = 2567 AND p.id_modulo = 398 AND i.itemativo = '1' AND i.libcliente = true AND P.id_instit = 50 AND p.anousu = 2023 and usuarioativo = '1' AND u.usuext = '0' UNION all SELECT distinct null as dl_usuario,'Perfil' as dl_tipo,uu.login,uu.nome,uu.email, u.id_usuario FROM db_permissao p INNER JOIN db_permherda h on h.id_perfil = p.id_usuario INNER JOIN db_usuarios uu on h.id_perfil = uu.id_usuario INNER JOIN db_usuarios u on u.id_usuario = h.id_usuario INNER JOIN db_itensmenu i ON i.id_item = p.id_item WHERE p.anousu = {$anousu} AND p.id_instit = 50 AND p.id_modulo = 398 AND i.itemativo = 1 and uu.usuext = '2' and i.id_item = 2567 and i.libcliente = true and uu.usuarioativo = '1' and u.usuarioativo = '1' ORDER BY nome");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

$autorizacoes = retornaAutorizacoes();
$usuarios = retornaUsuarios50();


//testa($autorizacoes);
//testa($usuarios);
//var_dump($usu_autoriza);


if($_POST){
  $usu_autoriza = db_getsession("DB_id_usuario");
  $usu_autorizado = $_POST["xusuario"];
  $nautorizacao = $_POST["xautoriza"];
  $hoje = date("Y-m-d");

  if($usu_autorizado == 0 || $nautorizacao == 0){
    echo "<script>alert('É necessário selecionar uma autorização e um usuário.');</script>";
  }else{    
    pg_query("INSERT INTO controleautorizacoes(usuarioautoriza, usuarioliberado, numautorizacao, dataliberacao) VALUES({$usu_autoriza}, {$usu_autorizado}, {$nautorizacao}, '{$hoje}')");
    echo "<script>alert('Autorização liberada.');</script>";
  }
}

?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>  
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">

  <fieldset style="width: 600px;">
    <legend id="legenda_suspensao" class="bold">Liberação de Autorização de Empenho</legend>
    
    <form method="post" action="">
    <table style="width: 100%" border="0">
      <tr>
        <td class="bold" style="width: 120px;">
          <label class="bold">
            <b>Autorização:</b>
          </label>
        </td>
        <td>
          <select id="xautoriza" name="xautoriza">
            <option value="0">Selecione</option>
            <?php foreach ($autorizacoes as $autorizacao) : $xdata = implode("/", array_reverse(explode("-", $autorizacao["e54_emiss"]))) ?>
              <option value="<?=$autorizacao['e54_autori']?>"><?=$autorizacao['e54_autori'] . " - " . $xdata;?></option>
            <?php endforeach; ?>            
          </select>
        </td>
      </tr>

      <tr>
        <td class="bold" style="width: 120px;">
          <label class="bold">
            <b>Usuário:</b>
          </label>
        </td>
        <td>
          <select id="xusuario" name="xusuario">
            <option value="0">Selecione</option>
            <?php foreach ($usuarios as $usuario) : ?>
              <option value="<?=$usuario['id_usuario']?>"><?=$usuario['nome'] . " - " . $usuario["id_usuario"];?></option>
            <?php endforeach; ?>            
          </select>
        </td>
      </tr>


      
      
      <?php /* ?>
      <tr>
        <td colspan="2">
          <fieldset style="width: 97%">
            <legend class="bold"><label for="justificativa">Justificativa</label></legend>
            <textarea style="width: 100%; height: 100px;" id="justificativa"></textarea>
          </fieldset>
        </td>
      </tr>
      <?php */ ?>
    </table>
    <p>
    <input id="liberar" name="liberar" value="Liberar" type="submit"/>
  </p>
    </form>
  </fieldset>  
</div>

<div class="container">
<table>
  <tr>
    <td>
      <fieldset>
        <legend><b>Histórico de Liberações</b></legend>
        <center>
          <input name="pesquisar" type="button" id="pesquisar" value="Liberações" onclick="js_pesquisa22();" >
        </center>
      </fieldset> 
    </td>
  </tr>      
</table>
</div>

<script>
  function js_pesquisa22(){  
  //js_OpenJanelaIframe('','db_iframe_orcdotacao','func_certificacao.php?funcao_js=parent.js_preenchepesquisa|id|nocertificado','Pesquisa',true);
  js_OpenJanelaIframe('','db_iframe_orcdotacao','libempenho_hist.php','Pesquisa',true);
}

  
</script>

<?php db_menu(); ?> 
</body>
</html>

