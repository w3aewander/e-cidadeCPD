<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));

if($_GET["id"] && isset($_GET["id"])){
    pg_query("UPDATE titulosia SET nomeia1 = '{$_GET["t1"]}', nomeia2 = '{$_GET["t2"]}', nomeia3 = '{$_GET["t3"]}', nomeia4 = '{$_GET["t4"]}', nomeia5 = '{$_GET["t5"]}' WHERE id = {$_GET["id"]}");
}else{    
    pg_query("INSERT INTO titulosia(calendario, turma, etapa, periodo, disciplina, nomeia1, nomeia2, nomeia3, nomeia4, nomeia5) VALUES({$_GET["calendario"]}, {$_GET["turma"]}, {$_GET["etapa"]}, {$_GET["periodo"]}, {$_GET["disciplina"]}, '{$_GET["t1"]}', '{$_GET["t2"]}', '{$_GET["t3"]}', '{$_GET["t4"]}', '{$_GET["t5"]}')");
}
?>
<p><span>IA 1:</span> <input type="text" maxlength="10" name="nia1" id="nia1" value="<?=$_GET["t1"]?>"></p>
<p><span>IA 2:</span> <input type="text" maxlength="10" name="nia2" id="nia2" value="<?=$_GET["t2"]?>"></p>
<p><span>IA 3:</span> <input type="text" maxlength="10" name="nia3" id="nia3" value="<?=$_GET["t3"]?>"></p>
<p><span>IA 4:</span> <input type="text" maxlength="10" name="nia4" id="nia4" value="<?=$_GET["t4"]?>"></p>
<p><span>IA 5:</span> <input type="text" maxlength="10" name="nia5" id="nia5" value="<?=$_GET["t5"]?>"></p>
<p><span style="font-size: 10px;"><i>Nomes salvos. Serão atualizados na grade na próxima vez que entrar na tela.</i></span></p>