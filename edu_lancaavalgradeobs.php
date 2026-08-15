<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));

if($_GET["id"] && isset($_GET["id"])){
    pg_query("UPDATE titulosiaobs SET observacao = '{$_GET["nobs"]}' WHERE id = {$_GET["id"]}");
}else{    
    pg_query("INSERT INTO titulosiaobs(calendario, turma, etapa, periodo, disciplina, observacao) VALUES({$_GET["calendario"]}, {$_GET["turma"]}, {$_GET["etapa"]}, {$_GET["periodo"]}, {$_GET["disciplina"]}, '{$_GET["nobs"]}')");
}
?>
    
    <textarea name="nobs" id="nobs" rows="4" cols="50"><?=$_GET["nobs"]?></textarea>
    <p><span style="font-size: 10px;"><i>Observação salva.</i></span></p>

