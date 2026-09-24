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
require_once(modification("libs/db_conecta".".php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification('libs/db_sql.php'));
require_once(modification("dbforms/db_funcoes.php"));
include(modification("dbforms/db_classesgenericas.php"));

$oPost = db_utils::postMemory($_POST);
$oGet  = db_utils::postMemory($_GET);

$instituicao = db_getsession("DB_instit");

try {
    if ($instituicao != 45) {
        throw new \BusinessException('Esse Relatório é de uso exclusivo da instituição SERVICO AUTONOMO DE AGUA E ESGOTO.');
    }
} catch (Exception $exception) {
    return db_redireciona("db_erros.php?fechar=true&db_erro={$exception->getMessage()}");
}
if (isset($oPost->incluir)) {
    db_inicio_transacao();
    $sqlerro = false;
    $processado = true;
    $nomearq = "./tmp/arquivo_margem_gerado_saae_".$_POST['mes']."_".$_POST['ano'].".txt";
    $swhere = "";
    if(!empty($matriculas_selecionadas_text) ){
        $swhere = " AND rh02_regist in (".$matriculas_selecionadas_text.")";
    }

    $sql = "
        select '000' || substring(rh01_regist::text from 4) as matricula,
        z01_cgccpf as cpf,
        to_ascii(z01_nome,'latin1') as nome,
        rh01_instit as instit,
        rh26_orgao as orgao,
        rh01_nasc as datanasc,
        rh01_admiss as dataadmiss,
        rh05_recis as dataresc,
        r14_rubric as rubrica,
        rh30_descr as regime,
        rh55_descr as lotacao,
        round(r14_valor,2) as margem,
        z01_ident as identidade
        ";

    $sql .= "
    from gerfsal
        inner join rhpessoalmov on r14_regist = rh02_regist and r14_anousu = rh02_anousu and r14_mesusu = rh02_mesusu and rh02_instit = r14_instit
        inner join rhregime on rh30_codreg = rh02_codreg and rh30_instit = rh02_instit
        inner join rhlota on r70_codigo = rh02_lota and r70_instit = rh02_instit
         left join rhlotaexe on rh26_codigo = rh02_lota
        inner join rhpeslocaltrab on rh56_seqpes = rh02_seqpes and rh56_princ = 't'
        inner join rhlocaltrab on  rh55_codigo = rh56_localtrab and rh55_instit = rh02_instit
        inner join rhpessoal on rh02_regist = rh01_regist and rh02_instit = rh01_instit
        left join rhpesrescisao on rh05_seqpes = rh02_seqpes
        inner join cgm on rh01_numcgm = z01_numcgm
        inner join pontofs on r10_regist = r14_regist and r10_instit = r14_instit and r10_mesusu = r14_mesusu and r10_anousu = r14_anousu and r10_rubric = r14_rubric
        where r14_anousu = ".$_POST['ano']."
            and r14_mesusu = ".$_POST['mes']."
            and rh02_instit = ". $instituicao ."
            $swhere
            and r14_rubric in ('R803','R804') order by nome asc
        ";
        // die($sql);
    $result = db_query($sql);
    $linhas = pg_num_rows($result);
    $caminhoArquivo = "./tmp/arquivo_margem_gerado_saae_".$_POST['mes']."_".$_POST['ano'].".txt";
    $arquivo = fopen($caminhoArquivo, "w");

    if ($linhas > 0) {
        $registro = "";
        for ($i = 0; $i < $linhas; $i++) {
            $linha = \db_utils::fieldsMemory($result, $i);
            $matricula = preg_replace('/^18/', '', $linha->matricula);
            $registro .= str_pad(substr($matricula, 0, 10), 10, '0',STR_PAD_LEFT);
            $registro .= str_pad(substr($linha->cpf, 0, 11), 11, ' ',STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->nome, 0, 50), 50, ' ',STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->orgao, 0, 3), 3, '0',STR_PAD_LEFT);
            $registro .= str_pad(substr($linha->instit, 0, 3), 3, '0',STR_PAD_LEFT);
            $margem = $linha->margem;
            $registro .= str_pad(substr($margem, 0, 10), 10, '0',STR_PAD_LEFT);
            $nascimento = date('dmY',strtotime($linha->datanasc));

            $registro .= str_pad(substr($nascimento, 0, 8), 8, ' ',STR_PAD_RIGHT);
            $admissao = date('dmY', strtotime($linha->dataadmiss));
            $registro .= str_pad(substr($admissao, 0, 8), 8, ' ',STR_PAD_RIGHT);
            $rescisao = "";
            if (!empty($linha->dataresc)) {
                $rescisao = date('dmY', $linha->dataresc);
            }
            $registro .= str_pad(substr($rescisao, 0, 8), 8, ' ',STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->regime, 0, 40), 40, ' ',STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->lotacao, 0, 40), 40, ' ',STR_PAD_RIGHT);
            $registro .= str_pad(substr($linha->identidade, 0, 15), 35, ' ',STR_PAD_RIGHT);

            $registro .= "\r\n";
        }
        $arquivo = fopen($caminhoArquivo, 'w+');
        fwrite($arquivo, $registro);
        fclose($arquivo);
    } else {
        fwrite($arquivo, "Não foram encontrados resultados para esse período. \n Certifique-se de que os campos foram preenchidos corretamente.");
    }
    fclose($arquivo);

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
    <body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" >
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
                    <legend>Relatório Margem Zetra</legend>
                    <table border="0" width="100%" class="form-container">
                        <tr>
                            <td>
                                <b>Ano:</b>
                            </td>
                            <td>
                            <select name="ano">
                                <?php
                                $sqlAnos    = "select distinct r11_anousu from cfpess where r11_instit = " . db_getsession('DB_instit') . " order by 1 desc";
                                $resultAnos = db_query($sqlAnos);
                                while ($anos = pg_fetch_object($resultAnos)) {
                                    echo "<option value=\"$anos->r11_anousu\">$anos->r11_anousu</option>";
                                }
                                ?>
                            </select>
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
                        $aux                                  = new cl_arquivo_auxiliar;
                        $aux->cabecalho                       = "<strong>MATRÍCULAS SELECIONADAS</strong>";
                        $aux->obrigarselecao                  = false;
                        $aux->codigo                          = "rh01_regist";
                        $aux->descr                           = "z01_nome";
                        $aux->nomeobjeto                      = 'matriculas_selecionadas';
                        $aux->funcao_js                       = 'js_mostra';
                        $aux->funcao_js_hide                  = 'js_mostra1';
                        $aux->func_arquivo                    = "func_rhpessoal.php";
                        $aux->nomeiframe                      = "db_iframe_rhpessoal";
                        $aux->executa_script_apos_incluir     = "document.form1.rh01_regist.focus();";
                        $aux->mostrar_botao_lancar            = false;
                        $aux->executa_script_lost_focus_campo = "js_insSelectmatriculas_selecionadas()";
                        $aux->executa_script_change_focus     = "document.form1.rh01_regist.focus();";
                        $aux->localjan                        = "";
                        $aux->db_opcao                        = 2;
                        $aux->tipo                            = 2;
                        $aux->top                             = 20;
                        $aux->linhas                          = 10;
                        $aux->vwidth                          = "460";
                        $aux->funcao_gera_formulario();
                        echo "</td>";
                        db_input('matriculas_selecionadas_text',20,0,true,'hidden',3);

                        echo"<script>js_insere_matri()</script>";
                        ?>
                    </table>
                        <input id="incluir" name="incluir" type="submit" value="Processar" onclick="js_insere_matri();">
                </fieldset>
            </form>
        </center>
        <?php db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));?>
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

    function js_detectaarquivo(sArquivo){
      var sListagem = sArquivo + "#Download arquivo ";
      js_montarlista(sListagem,"form1");
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



