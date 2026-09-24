<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
require_once(modification("dbforms/db_funcoes.php"));

$db_opcao = 1;
?>
<html>
<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script type="text/javascript" src="scripts/strings.js"></script>
    <script type="text/javascript" src="scripts/prototype.js"></script>
    <script type="text/javascript" src="scripts/classes/http/http.js"></script>
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBDownload.widget.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC>
<div class="container" style="width: 640px;">
    <form name='form1' id="form1">
        <fieldset>
            <legend>Relatório de Empenhos sem Dados para o SIGFIS 2024</legend>
            <table class="form-container">
               <tr>
                    <td>
                        <input type="radio" name="tipo" id="tpdf" value="pdf" checked>
                        <b>PDF</b>
                    </td>                    
                </tr>

                <tr>
                    <td>
                        <input type="radio" name="tipo" id="tcsv" value="csv">
                        <b>Planilha</b>
                    </td>
                </tr>

                
                    <tr>
                    <td>
                        <input type="radio" name="tipo" id="espcial" value="especial">
                        <b>Especial</b>
                    </td>
                </tr>
                

                <?php if(db_getsession("DB_instit") == 50) : ?>
                <tr>
                    <td>
                        <input type="radio" name="uni" value="1">
                        <b>Fundo Municipal de Saúde</b>

                        <input type="radio" name="uni" value="3">
                        <b>Serviço Autônomo Hospitalar</b>

                        <input type="radio" name="uni" value="todos" checked>
                        <b>Todos</b>
                    </td>
                    
                </tr>
                <?php endif; ?>
            </table>
            <br>
            <?php if(db_getsession("DB_instit") == 50) : ?>
                <input type='button' value='Emitir' onclick='js_emitir50()'>
            <?php else : ?>
                <input type='button' value='Emitir' onclick='js_emitir()'>
            <?php endif; ?>
        
    </form>
</div>
</body>
</html>
<?php
db_menu();
?>
<script type="text/javascript" src="scripts/session.js"></script>
<script>


    




    function js_emitir() {
        var tipo = document.querySelector('input[name="tipo"]:checked').value;        
        
        jan = window.open('relsigfis24impresso.php&tipo='+tipo, '','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0');
        jan.moveTo(0,0);        
    }

    function js_emitir50() {
        var tipo = document.querySelector('input[name="tipo"]:checked').value;
        var unidade = document.querySelector('input[name="uni"]:checked').value;
        
        jan = window.open('relsigfis24impresso.php&tipo='+tipo+'&unidade='+unidade, '','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0');
        jan.moveTo(0,0);        
    }

</script>
