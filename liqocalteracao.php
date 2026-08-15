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
require_once(modification("std/db_stdClass.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_liborcamento.php"));
require_once(modification("libs/db_libcontabilidade.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_orcdotacao_classe.php"));
require_once(modification("classes/db_orctiporec_classe.php"));
require_once(modification("classes/db_empempenho_classe.php"));
require_once(modification("classes/db_empelemento_classe.php"));

require_once(modification("classes/db_pagordem_classe.php"));
require_once(modification("classes/db_pagordemele_classe.php"));
require_once(modification("classes/db_pagordemnota_classe.php"));
require_once(modification("classes/db_pagordemval_classe.php"));
require_once(modification("classes/db_pagordemrec_classe.php"));
require_once(modification("classes/db_pagordemtiporec_classe.php"));

require_once(modification("classes/db_empnota_classe.php"));
require_once(modification("classes/db_empnotaele_classe.php"));
require_once(modification("classes/db_tabrec_classe.php"));
require_once(modification("classes/db_conplanoreduz_classe.php"));
require_once(modification("classes/db_conlancam_classe.php"));
require_once(modification("classes/db_conlancamemp_classe.php"));
require_once(modification("classes/db_conlancamdoc_classe.php"));
require_once(modification("classes/db_conlancamele_classe.php"));
require_once(modification("classes/db_conlancamnota_classe.php"));
require_once(modification("classes/db_conlancamcgm_classe.php"));
require_once(modification("classes/db_conlancamdot_classe.php"));
require_once(modification("classes/db_conlancamval_classe.php"));
require_once(modification("classes/db_conlancamlr_classe.php"));
require_once(modification("classes/db_conlancamcompl_classe.php"));
require_once(modification("classes/db_conlancamord_classe.php"));

require_once(modification("classes/empenho.php"));

$clempnota = new cl_empnota;
$clempnotaele = new cl_empnotaele;
$clpagordem = new cl_pagordem;
$clpagordemele = new cl_pagordemele;
$clpagordemnota = new cl_pagordemnota;
$clpagordemval = new cl_pagordemval;
$clpagordemrec = new cl_pagordemrec;
$clempempenho = new cl_empempenho;
$clempelemento = new cl_empelemento;
$clorcdotacao = new cl_orcdotacao;
$clorctiporec = new cl_orctiporec;
$cltabrec = new cl_tabrec;
$clconplanoreduz = new cl_conplanoreduz;
$cltranslan = new cl_translan;
$clconlancam = new cl_conlancam;
$clconlancamemp = new cl_conlancamemp;
$clconlancamdoc = new cl_conlancamdoc;
$clconlancamele = new cl_conlancamele;
$clconlancamnota = new cl_conlancamnota;
$clconlancamcgm = new cl_conlancamcgm;
$clconlancamdot = new cl_conlancamdot;
$clconlancamval = new cl_conlancamval;
$clconlancamlr = new cl_conlancamlr;
$clconlancamcompl = new cl_conlancamcompl;
$clconlancamord = new cl_conlancamord;
$clpagordemtiporec = new cl_pagordemtiporec;
$clempenho = new empenho; // rotinas para liquidação de empenho

parse_str($_SERVER["QUERY_STRING"]);
db_postmemory($_POST);

function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

function buscaSeqAtestador($codnota){
    $sql = pg_query("SELECT e169_sequencial, e169_numcgm FROM empnotaatestador WHERE e169_empnota = {$codnota}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["e169_sequencial"];
}

function voltaTipos(){
    $sql = pg_query("SELECT DISTINCT e178_sigfistipodocliquidacao, e177_descr FROM empnotasigfistipodocliquidacao INNER JOIN sigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial ORDER BY e178_sigfistipodocliquidacao");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}

function verificaExiste($seqnota){
    $sql = pg_query("SELECT * FROM empnotasigfistipodocliquidacao WHERE e178_empnota = {$seqnota}");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}
$tiposliq = voltaTipos();

//testa($_SESSION); die("Confere");

if($_POST){
    
        //testa($_POST);
        $seqnota = $_POST["e69_codnota"];
        $tipon = $_POST["tiponovo"];
        $seqatestador = buscaSeqAtestador($seqnota);
        
        $verifica = verificaExiste($seqnota);        

        if(!$verifica){
            $cgmatestador = ($_POST["z01_numcgm"]) ? $_POST["z01_numcgm"] : $_POST["cgmatual"];            
            
            pg_query("INSERT INTO empnotasigfistipodocliquidacao VALUES(nextval('empnotasigfistipodocliquidacao_e178_sequencial_seq'), {$seqnota}, {$tipon})");
            //var_dump("INSERT INTO empnotasigfistipodocliquidacao VALUES(nextval('empnotasigfistipodocliquidacao_e178_sequencial_seq'), {$seqnota}, {$tipon})"); echo "<br>";
            
            if(empty($cgmatestador)){
                pg_query("INSERT INTO empnotaatestador(e169_sequencial, e169_empnota) VALUES(nextval('empnotaatestador_e169_sequencial_seq'), {$seqnota})");
                //var_dump("INSERT INTO empnotaatestador(e169_sequencial, e169_empnota) VALUES(nextval('empnotaatestador_e169_sequencial_seq'), {$seqnota})"); echo "<br>";
            }else{
                pg_query("INSERT INTO empnotaatestador VALUES(nextval('empnotaatestador_e169_sequencial_seq'), {$seqnota}, {$cgmatestador})");
                //var_dump("INSERT INTO empnotaatestador VALUES(nextval('empnotaatestador_e169_sequencial_seq'), {$seqnota}, {$cgmatestador})"); echo "<br>";
            }
        }
        //var_dump("UPDATE empnotasigfistipodocliquidacao SET e178_sigfistipodocliquidacao = {$tipon} WHERE e178_empnota = {$seqnota}"); echo "<br>";
        
        if(pg_query("UPDATE empnotasigfistipodocliquidacao SET e178_sigfistipodocliquidacao = {$tipon} WHERE e178_empnota = {$seqnota}")){
            $login = db_getsession("DB_login");
            $data = date("Y-m-d",db_getsession("DB_datausu"));
            $seqempenho = $_POST["e60_numemp"];
            $novotipo = $tipon;
            $novocgm = ($_POST["z01_numcgm"]) ? $_POST["z01_numcgm"] : $_POST["cgmatual"];
            
            //echo "INSERT INTO controletipos(login, data, empenho, novotipo) VALUES('{$login}', '{$data}', {$seqempenho}, {$novotipo})";
            //echo "UPDATE empnotaatestador SET e169_numcgm = {$novocgm} WHERE e169_sequencial = {$seqatestador}";
            
            pg_query("INSERT INTO controletipos(login, data, empenho, novotipo) VALUES('{$login}', '{$data}', {$seqempenho}, {$novotipo})");
            pg_query("UPDATE empnotaatestador SET e169_numcgm = {$novocgm} WHERE e169_sequencial = {$seqatestador}");
            echo "<script>alert('Tipo de Liquidação alterado.');</script>";
        }
        //echo "<script>alert('Tipo de Liquidação alterado.');</script>";    
    
    
}

?>
<html>
<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <script type="text/javascript" src="scripts/prototype.js"></script>
    <script type="text/javascript" src="scripts/strings.js"></script>
    <script type="text/javascript" src="scripts/notaliquidacao.js"></script>
    <script type="text/javascript" src="scripts/widgets/windowAux.widget.js"></script>
    <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
    <script type="text/javascript" src="scripts/dates.js"></script>
    <script type="text/javascript" src="scripts/widgets/DBHint.widget.js"></script>
    <script type="text/javascript" src="ext/javascript/prototype.maskedinput.js"></script>
    <script type="text/javascript" src='extension/package/Desktop/assets/vendors/alertify/alertify.js'></script>
    <script type="text/javascript" src="scripts/components/AlertaConfirmaDataFinanceiro.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <link href="estilos/grid.style.css" rel="stylesheet" type="text/css">
    <link type="text/css" href="extension/package/Desktop/assets/vendors/alertify/themes/alertify.core.css"
          rel="stylesheet"/>
    <link type="text/css" href="extension/package/Desktop/assets/vendors/alertify/themes/alertify.bootstrap.css"
          rel="stylesheet"/>
    <style>
        
    </style>
</head>

<div class="container">
    <form method="post" action="" id="formao">
        <fieldset>
            <legend>Alteração de Tipo de Documento de Liquidação</legend>

            <table border="0" class="form-container">
            <tr>
                <td><label for="e60_numemp">Seq. Empenho:</label></td>
                <td><input style="background-color: #DEB887;" type="text" name="e60_numemp" id="e60_numemp" readonly></td>
            </tr>

            <tr>
                <td><label for="e60_codemp">Nº Empenho:</label></td>
                <td><input style="background-color: #DEB887;" type="text" name="e60_codemp" id="e60_codemp" readonly></td>
            </tr>

            <tr>
                <td><label for="tipoatual">Tipo de Documento - Atual:</label></td>
                <td><input style="background-color: #DEB887;" type="text" name="tipoatual" id="tipoatual" readonly size="35"></td>
            </tr>

            <tr>
                <td><label for="atestadoratual">Atestador - Atual:</label></td>
                <td><input style="background-color: #DEB887;" type="text" name="atestadoratual" id="atestadoratual" readonly size="35"></td>
            </tr>

            <tr>
                <td><label for="tiponovo">Novo Tipo de Documento</label></td>
                <td><select id="tiponovo" name="tiponovo">
                    <option value="0">Selecione</option>
                    <?php foreach ($tiposliq as $tipo) : ?>
                        <option value="<?=$tipo['e178_sigfistipodocliquidacao']?>"><?=$tipo['e177_descr']?></option>
                    <?php endforeach; ?>
                </select>
                </td>
            </tr>

            <?php /* ?>
            <tr>
                <td><label for="atestadornovo">Novo Atestador</label></td>
                <td><input type="text" name="atestadornovo" id="atestadornovo" size="35" value="" autocomplete="off" maxlength="" onblur="" onfocus="" onkeypress="" onkeyup="js_ValidaCampos(this,1,'cgm:','f','f',event);" onkeydown="" oninput="js_ValidaCampos(this,1,'cgm:','f','f',event);" style="">
                </td>
            </tr>
            <?php */ ?>


            <tr>
                <td><?php db_ancora("Novo Atestador", 'js_pesquisaCgm(true);', 1); ?></td>
                <td>
                    <?php
                    db_input('z01_numcgm', 5, 0, true, 'text', 1, 'onchange="js_pesquisaCgm(false);"');
                    db_input('z01_nome_cgm', 30, 0, true, 'text', 3);
                    ?>
                </td>
            </tr>





        <input type="hidden" name="e69_codnota" id="e69_codnota">
        <input type="hidden" name="cgmatual" id="cgmatual">

        <tr>
            <td><input type="button" value="Pesquisar" onclick="js_pesquisa()"></td>
            <td><input type="button" value="Alterar" disabled id="bota" onclick="valida()"></td>
        </tr>
  </table>

            
        </fieldset>
        
    </form>
</div>
<?php
db_menu();
?>

<script>
    function js_pesquisa() {
        js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_empempenho', 'func_empempenhosigfis.php?funcao_js=parent.js_preenchepesquisa|e69_numemp|e60_codemp|e60_anousu|e177_descr|e69_codnota|z01_numcgm|z01_nome|z01_cgccpf', 'Pesquisa', true);
    }

    function js_preenchepesquisa(chave, chave2, chave3, chave4, chave6, chave7, chave8, chave9) {
        db_iframe_empempenho.hide();
        document.getElementById("e60_numemp").value = chave;
        document.getElementById("e60_codemp").value = chave2+"/"+chave3;
        document.getElementById("tipoatual").value = chave4;
        document.getElementById("e69_codnota").value = chave6;

        document.getElementById("atestadoratual").value = chave7+" - "+chave8;
        document.getElementById("cgmatual").value = chave7;
        

        document.getElementById("bota").disabled = false;

    }

    function valida(){
        var valor = document.getElementById("tiponovo").value;

        if(valor == 0){
            alert('Selecione um Tipo de Liquidação.');
            return false;
        }
        document.getElementById('formao').submit();
    }

    function js_pesquisaCgm(mostra) {

    if (mostra == true) {
        js_OpenJanelaIframe('CurrentWindow.corpo',
                                'db_iframe_cgm',
                                'func_cgm.php?funcao_js=parent.js_retornoPesquisaCgm|z01_numcgm|z01_nome',
                                'Pesquisa CGM',
                                true);
    } else {
        var iCgmFormulario = $F('z01_numcgm');
        if (iCgmFormulario == ''){
            $('z01_nome_cgm').value = '';
        } else {
            js_OpenJanelaIframe('CurrentWindow.corpo',
                                    'db_iframe_cgm',
                                    'func_cgm.php?pesquisa_chave='+iCgmFormulario+
                                    '&funcao_js=parent.js_retornoPesquisaCgm',
                                    'Pesquisa CGM',
                                    false);
        }
    }
}


function js_retornoPesquisaCgm() {

    db_iframe_cgm.hide();
    
    if (arguments[0] == true) {
        $('z01_numcgm').value   = '';
        $('z01_nome_cgm').value = arguments[1];
    } else if (arguments[0] == false) {
        $('z01_nome_cgm').value = arguments[1];
    } else {
        $('z01_numcgm').value   = arguments[0];
        $('z01_nome_cgm').value = arguments[1];        
    }
}


            // Lancador Atestadores        
            //oDBLancadorAtestadores.setParametrosPesquisa('func_cgm.php', ['z01_numcgm','z01_nome']);
            
</script>

<?php if(empty($_POST)) : ?>    
    <script>js_pesquisa();</script>;
<?php else: ?>
    <script>
    document.getElementById("z01_numcgm").value = "";
    document.getElementById("z01_nome_cgm").value = "";
</script>;
<?php endif; ?>

</body>
</html>

