<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014 DBSeller Servicos de Informatica
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
require_once(modification("libs/db_conecta" . ".php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification('libs/db_sql.php'));
require_once(modification("dbforms/db_funcoes.php"));
include(modification("dbforms/db_classesgenericas.php"));

$oPost = db_utils::postMemory($_POST);
$oGet = db_utils::postMemory($_GET);

$instituicao = db_getsession("DB_instit");
try {
    if ($instituicao != 45) {
        throw new \BusinessException('Esse Relatório é de uso exclusivo da instituição SERVICO AUTONOMO DE AGUA E ESGOTO.');
    }
} catch (Exception $exception) {
    return db_redireciona("db_erros.php?fechar=true&db_erro={$exception->getMessage()}");
}
if (isset($oPost->incluir)) {
    $swhere = "";
    if (!empty($matriculas_selecionadas_text)) {
        $swhere = " AND rh02_regist in (" . $matriculas_selecionadas_text . ")";
    }

    db_inicio_transacao();
    $sqlerro = false;
    $processado = true;
    $nomearq = "./tmp/arquivo_movimento_voltaredonda_saae_" . $_POST['mes'] . "_" . $_POST['ano'] . ".txt";

    $sql = "
        select '000' || substring(rh01_regist::text from 4) as matricula,
        z01_cgccpf as cpf,
        to_ascii(z01_nome,'latin1') as nome,
        rh01_instit as instit,
        rh26_orgao as orgao,
        r14_rubric as rubrica,
        round(r14_valor,2)
        ";

    $sql .= " as valor from gerfsal
        inner join rhpessoalmov on r14_regist = rh02_regist and r14_anousu = rh02_anousu and r14_mesusu = rh02_mesusu and rh02_instit = r14_instit
        inner join rhpessoal on rh02_regist = rh01_regist and rh02_instit = rh01_instit
        left join rhlotaexe on rh26_codigo = rh02_lota
        inner join cgm on rh01_numcgm = z01_numcgm
        inner join pontofs on r10_regist = r14_regist and r10_instit = r14_instit and r10_mesusu = r14_mesusu and r10_anousu = r14_anousu and r10_rubric = r14_rubric
        where r14_anousu = " . $_POST['ano'] . "
            and r14_mesusu = " . $_POST['mes'] . "
            and rh02_instit = ". $instituicao ."
            $swhere
            and r14_rubric in ('0180', '0204', '0246', '0289', '0294', '0309', '0314', '0333', '0349', '0365', '0378', '0428', '0432', '0463', '0483', '0484', '0490', '0498', '0543', '0544', '0554', '0564', '0574', '0584', '0585', '0619', '0625', '0308', '0310', '0317', '0320', '0321', '0327', '0334', '0335', '0336', '0339', '0347', '0350', '0356', '0357', '0377', '0379', '0381', '0382', '0383', '0384', '0385', '0389', '0394', '0395', '0397', '0262', '0131', '0174', '0177', '0179', '0189', '0219', '0220', '0285', '0300', '0315', '0372', '0375', '0400', '0401', '0402', '0406', '0410', '0430', '0431', '0464', '0475', '0479', '0481', '0482', '0485', '0511', '0513', '0514', '0515', '0521', '0523', '0534', '0537', '0545', '0565', '0573', '0575', '0576', '0577', '0582', '0583', '0586', '0589', '0590', '0605', '0613', '0666', '0306', '0316', '0318', '0319', '0322', '0323', '0326', '0328', '0329', '0330', '0331', '0332', '0337', '0338', '0340', '0341', '0342', '0343', '0344', '0345', '0346', '0348', '0351', '0352', '0353', '0354', '0355', '0362', '0366', '0370', '0371', '0373', '0374', '0376', '0380', '0386', '0387', '0398', '0405', '0563', '0568', '0569', '0462') order by nome asc
        ";
        // die($sql);
    $result = db_query($sql);
    $linhas = pg_num_rows($result);

    $caminhoArquivo = "./tmp/arquivo_movimento_voltaredonda_saae_" . $_POST['mes'] . "_" . $_POST['ano'] . ".txt";
    $conteudo = "";
    if ($linhas > 0) {
        $registro = "";
        for ($i = 0; $i < $linhas; $i++) {
            $linha = \db_utils::fieldsMemory($result, $i);
            $matricula = preg_replace('/^18/', '', $linha->matricula);
            $registro .= str_pad(substr($matricula, 0, 10), 10, '0', STR_PAD_LEFT);
            $registro .= str_pad(substr($linha->cpf, 0, 11), 11, ' ', STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->nome, 0, 50), 50, ' ', STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->orgao, 0, 3), 3, '0', STR_PAD_LEFT);
            $registro .= str_pad(substr($linha->instit, 0, 3), 3, '0', STR_PAD_LEFT);
            $rubrica = preg_replace('/^0/', '', $linha->rubrica);
            $registro .= str_pad(substr($rubrica, 0, 4), 4, '0', STR_PAD_LEFT);
            $registro .= str_pad(substr($linha->valor, 0, 9), 9, '0', STR_PAD_LEFT);
            $registro .= str_pad("", 3, '0', STR_PAD_LEFT);
            $registro .= str_pad("", 3, '0', STR_PAD_LEFT);
            $registro .= str_pad(substr($_POST['mes'], 0, 2), 2, '0', STR_PAD_LEFT);;
            $registro .= str_pad(substr($_POST['ano'], 0, 4), 4, '0', STR_PAD_LEFT);;
            $registro .= "\r\n";
        }

        $arquivo = fopen($caminhoArquivo, 'w+');
        fwrite($arquivo, $registro);
        fclose($arquivo);
    } else {
        $conteudo = "Não foram encontrados resultados para esse período. \n Certifique-se de que os campos foram preenchidos corretamente.";
        $arquivo = fopen($caminhoArquivo, 'w+');
        fwrite($arquivo, $conteudo);
        fclose($arquivo);
    }

    if ($result == false) {
        $sqlerro = true;
        $processado = false;
        echo "<script> alert('Algum erro aconteceu, favor entrar em contato com o suporte!') </script>";
    } else {
        echo "<script> alert('Relatório Gerado com sucesso.') </script>";
    }
    db_fim_transacao($sqlerro);
}
?>
<html>
<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <?php
    db_app::load("scripts.js, strings.js, prototype.js, datagrid.widget.js");
    db_app::load("widgets/messageboard.widget.js, widgets/windowAux.widget.js");
    db_app::load("estilos.css, grid.style.css");
    ?>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1">
<table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
    <tr>
        <td width="360" height="21">&nbsp;</td>
        <td width="263">&nbsp;</td>
        <td width="25">&nbsp;</td>
        <td width="140">&nbsp;</td>
    </tr>
</table>
<center>
    <style type="text/css">
        fieldset {
            border-radius: 7px;
            padding: 8px;
        }

        table.form-container th, table.form-container td {
            padding: 0px 4px;
            text-align: left;
        }
    </style>
    <br/>
    <br/>
    <form name="form1" method="post" action="" class="container">
        <fieldset>
            <legend>Relatório Retorno Zetra</legend>
            <table border="0" width="100%" class="form-container">
                <tr>
                    <td>
                            <b>Ano:</b>
                        </td>
                        <td>
                            <select name="ano">
                                <?php
                                $anoAtual = date("Y");
                                $anoInicial = 2018;

                                for ($ano = $anoAtual; $ano >= $anoInicial; $ano--) {
                                    $selected = ($ano == $anoAtual) ? 'selected' : '';
                                    echo "<option value=\"$ano\" $selected>$ano</option>";
                                }
                                ?>
                            </select>'
                        </td>
                </tr>
                <tr>
                    <td>
                        <b>Mês:</b>
                    </td>
                    <td>
                        <select name="mes">
                            <option value="01">Janeiro</option>
                            <option value="02">Fevereiro</option>
                            <option value="03">Março</option>
                            <option value="04">Abril</option>
                            <option value="05">Maio</option>
                            <option value="06">Junho</option>
                            <option value="07">Julho</option>
                            <option value="08">Agosto</option>
                            <option value="09">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </td>
                </tr>
                <?php
                echo '<td align="right" colspan = "2">';
                $aux = new cl_arquivo_auxiliar;
                $aux->cabecalho = "<strong>MATRÍCULAS SELECIONADAS</strong>";
                $aux->obrigarselecao = false;
                $aux->codigo = "rh01_regist";
                $aux->descr = "z01_nome";
                $aux->nomeobjeto = 'matriculas_selecionadas';
                $aux->funcao_js = 'js_mostra';
                $aux->funcao_js_hide = 'js_mostra1';
                $aux->func_arquivo = "func_rhpessoal.php";
                $aux->nomeiframe = "db_iframe_rhpessoal";
                $aux->executa_script_apos_incluir = "document.form1.rh01_regist.focus();";
                $aux->mostrar_botao_lancar = false;
                $aux->executa_script_lost_focus_campo = "js_insSelectmatriculas_selecionadas()";
                $aux->executa_script_change_focus = "document.form1.rh01_regist.focus();";
                $aux->localjan = "";
                $aux->db_opcao = 2;
                $aux->tipo = 2;
                $aux->top = 20;
                $aux->linhas = 10;
                $aux->vwidth = "460";
                $aux->funcao_gera_formulario();
                echo "</td>";
                db_input('matriculas_selecionadas_text', 20, 0, true, 'hidden', 3);

                echo "<script>js_insere_matri()</script>";
                ?>
            </table>
            <input id="incluir" name="incluir" type="submit" value="Processar" onclick="js_insere_matri();">
        </fieldset>
    </form>
</center>
<?php db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"), db_getsession("DB_anousu"), db_getsession("DB_instit")); ?>
</body>
</html>
<script>
    function js_insere_matri() {
        var x = document.getElementById("matriculas_selecionadas");
        var i;
        var txt = [];
        for (i = 0; i < x.length; i++) {
            txt.push(x.options[i].value);
        }
        document.getElementById('matriculas_selecionadas_text').value = txt;
    }


    function js_detectaarquivo(sArquivo) {
        var sListagem = sArquivo + "#Download arquivo ";
        js_montarlista(sListagem, "form1");
    }
</script>

<?php
if ($processado == true) {
    echo "
        <script>
            js_detectaarquivo('$nomearq');
        </script>";
}
?>



