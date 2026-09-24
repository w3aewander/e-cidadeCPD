<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));

db_postmemory($_POST);

$clconvenio = new cl_convenio;
$db_opcao = 1;
$db_botao = true;
if (isset($incluir)) {
    db_inicio_transacao();
    $clconvenio->r56_posano = $r56_posano1.$r56_posano2;
    $clconvenio->r56_posmes = $r56_posmes1.$r56_posmes2;
    $clconvenio->r56_posreg = $r56_posreg1.$r56_posreg2;
    $clconvenio->r56_poseve = $r56_poseve1.$r56_poseve2;
    $clconvenio->r56_posq01 = $r56_posq011.$r56_posq012;
    $clconvenio->r56_posq02 = $r56_posq021.$r56_posq022;
    $clconvenio->r56_posq03 = $r56_posq031.$r56_posq032;
    $clconvenio->r56_posrubrica = $r56_posrubrica1.$r56_posrubrica2;
    $clconvenio->r56_poscpf     = $r56_poscpf1.$r56_poscpf2;
    $clconvenio->incluir($r56_codrel, db_getsession('DB_instit'));
    db_fim_transacao();
}
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body>
  <div class="container">
  <?php
    include(modification("forms/db_frmconvenio.php"));
    ?>
  </div>
<?php
db_menu();
?>
</body>
</html>
<?php
if (isset($incluir)) {
    if ($clconvenio->erro_status=="0") {
        $clconvenio->erro(true, false);
        $db_botao=true;
        echo "<script> document.form1.db_opcao.disabled=false;</script>  ";
        if ($clconvenio->erro_campo!="") {
            echo "<script> document.form1.".$clconvenio->erro_campo.".style.backgroundColor='#99A9AE';</script>";
            echo "<script> document.form1.".$clconvenio->erro_campo.".focus();</script>";
        };
    } else {
        $clconvenio->erro(true, true);
    };
};
?>
