<?php

require_once modification('libs/db_stdlib.php');
require_once modification('libs/db_conecta.php');
require_once modification('libs/db_sessoes.php');
require_once modification('libs/db_usuariosonline.php');
require_once modification('dbforms/db_funcoes.php');
require_once modification('classes/db_caddisciplina_classe.php');

db_postmemory($_POST);
parse_str($_SERVER['QUERY_STRING'], $queryString);

foreach ($queryString as $key => $value) {
    ${$key} = $value;
}

$clcaddisciplina = new cl_caddisciplina();
$clcaddisciplina->rotulo->label('ed232_i_codigo');
$clcaddisciplina->rotulo->label('ed232_i_codigo');

?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="iso-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="estilos.css">
    <script src="scripts/scripts.js"></script>
</head>
<b4ody>
<form name="form2" method="post" class="container">

</form>
<?php
if (isset($pesquisa_chave) === false) {
	$sql = "select 
			ed232_i_codigo,
			ed232_c_descr	
			from 
			regencia 
			inner join disciplina on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina 
			inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina 
			where 
			ed59_i_turma = ".$turma." 
			order by ed232_c_descr";
	
	echo '<div class="container">';
	echo '  <fieldset>';
	echo '    <legend>Resultado da Pesquisa</legend>';
	  db_lovrot($sql,15,"()","",$funcao_js,"","NoMe",$repassa);
	echo '  </fieldset>';
	echo '</div>';
}else {
	if ($pesquisa_chave != null && $pesquisa_chave != "") {
		$result = $clcaddisciplina->sql_record($clcaddisciplina->sql_query($pesquisa_chave));
		 if($clcaddisciplina->numrows!=0){
			db_fieldsmemory($result,0);
			echo "<script>".$funcao_js."('$ed232_i_codigo',false);</script>";
		} else {
			echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não Encontrado',true);</script>";
		}
	} else {
		echo "<script>{$funcao_js}('', false);</script>";
	}
}
?>
</body>
</html>
<?php if (isset($pesquisa_chave) === false) { ?>
    <script rel="script" type="text/javascript">
    </script>
<?php } ?>
<script rel="script" type="text/javascript">
    js_tabulacaoforms("form2","chave_ed232_i_codigo",true,1,"chave_ed232_i_codigo",true);
</script>
