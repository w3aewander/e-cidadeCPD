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
require_once(modification("libs/db_utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

//MODULO: issqn
$clrotulo = new rotulocampo;
$clrotulo->label("q205_inscr");
$clrotulo->label("q205_cep");
$clrotulo->label("q205_bairro");
$clrotulo->label("q205_logr");
$clrotulo->label("q205_bairromanual");
$clrotulo->label("q205_logrmanual");
$clrotulo->label("q205_compl");
$clrotulo->label("q205_dest");
$clrotulo->label("q205_municipal");

if ($inscricao != "") {
  $sSqlIss = "select q205_dest as destinatario, q205_codigo as codigo, q205_cep as cep, ";
  $sSqlIss .= " q205_bairro as bairro, q205_rua as rua, q205_bairronome as bairronome, ";
  $sSqlIss .= " q205_ruanome as ruanome, q205_num as numero, q205_compl as complemento, ";
  $sSqlIss .= " q205_dest as destinatario, q205_municipal as municipal ";
  $sSqlIss .= "   from issbaseendereco ";
  $sSqlIss .= "   left join issqn.issbase on issbase.q02_inscr = issbaseendereco.q205_inscr ";
  $sSqlIss .= "  inner join cgm on cgm.z01_numcgm = issbase.q02_numcgm ";
  $sSqlIss .= "   where issbaseendereco.q205_inscr = " . (int) $inscricao;

  $rsIss = db_query($sSqlIss);
  $iLinhasIss = pg_num_rows($rsIss);
  $db_opcao = 1;
  $codigo = null;

  if ($iLinhasIss > 0) {
    db_fieldsmemory($rsIss, 0);
    $db_opcao = 2;
    if ($municipal == 't') {
      $sSqlIss = "select ruastipo.j88_sigla || ' ' || ruas.j14_nome as ruadescricao from ruas left join ruastipo on j88_codigo = j14_tipo where ruas.j14_codigo = $rua";
      db_fieldsmemory(db_query($sSqlIss), 0);
      $sSqlIss = "select j13_descr as bairrodescricao from bairro where bairro.j13_codi = $bairro";
      db_fieldsmemory(db_query($sSqlIss), 0);
    }
  }

  $usuario = db_getsession("DB_id_usuario");
}
//************************************************************************************************//
?>
<html>

<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/classes/http/http.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body class="body-default">
  <div class="container">
    <form name="form1">
      <input type="hidden" value="true" name="atualiza_endereco">
      <fieldset>
        <legend>Endereço de Entrega</legend>

        <!--
        ********************************************************************************************************************
        ***************************************** CONTAINER DADOS DO ENDERECO **********************************************
        ********************************************************************************************************************
        -->

        <table class="form-container" border="0" width="500px">
          <tr>
            <td>
              Inscrição Municipal:
            </td>
            <td>
              <span>
                <?php
                db_input('inscricao', 8, true, true, 'text', 3);
                db_input('codigo', 8, true, true, 'hidden', 3);
                db_input('usuario', 8, true, true, 'hidden', 3);
                ?>
              </span>
              <span style="font-weight: bold; margin-left: 45px;">
                Endereço Municipal?
              </span>
              <span>
                <select id="municipal" name="municipal" style="width: 76px">
                  <option value="1" <?php echo ($municipal === 't') ? 'selected' : ''; ?>>Sim</option>
                  <option value="0" <?php echo ($municipal === 'f') ? 'selected' : ''; ?>>Não</option>
                </select>
              </span>
            </td>
          </tr>
          <tr>
            <td>
              Destinatário:
            </td>
            <td>
              <?php
              db_input('destinatario', 55, false, false, 'text', $db_opcao, "", "", "#E6E4F1", 50);
              ?>
            </td>
          </tr>
          <tr id="trRua">

          </tr>
          <tr>
            <td>
              Número:
            </td>
            <td>
              <span>
                <?php
                db_input('numero', 8, false, false, 'text', $db_opcao, "", "", "", 10);
                ?>
              </span>
              <span style="font-weight: bold; margin-left: 137px;">
                CEP:
              </span>
              <span>
                <?php
                db_input('cep', 8, false, false, 'text', $db_opcao, "", "", "", "", 8);
                ?>
              </span>
            </td>
          </tr>
          <tr id="trBairro">

          </tr>
          <tr>
            <td>
              Complemento:
            </td>
            <td>
              <?php
              db_input('complemento', 55, false, false, 'text', $db_opcao, "", "", "#E6E4F1", 100);
              ?>
            </td>
          </tr>
        </table>
      </fieldset>
      <input id="acao" name="acao" type="button" value="<?= $db_opcao == 1 ? "Incluir" : "Alterar" ?>" />
    </form>
  </div>
</body>

</html>

<script>

  function js_checa() {
    if (!document.form1.numero.value) {
      alert("Campo número é obrigatório!");
      document.form1.numero.focus();
      return false;

    } else if (!document.form1.cep.value) {
      alert("Campo CEP é obrigatório!");
      document.form1.cep.focus();
      return false;

    } else if (document.form1.cep.value.length != 8) {
      alert("Digite um CEP válido!");
      document.form1.cep.focus();
      return false;
    }

    if (document.form1.municipal.value == '0') {
      if (!document.form1.ruanome.value) {
        alert("Campo logradouro é obrigatório!");
        document.form1.ruanome.focus();
        return false;

      } else if (!document.form1.bairronome.value) {
        alert("Campo bairro é obrigatório!");
        document.form1.bairronome.focus();
        return false;
      }
      return true;
    }

    if (!document.form1.rua.value) {
      alert("Campo logradouro é obrigatório!");
      document.form1.rua.focus();
      return false;

    } else if (!document.form1.bairro.value) {
      alert("Campo bairro é obrigatório!");
      document.form1.bairro.focus();
      return false;
    }

    return true;
  }

  document.getElementById("acao").addEventListener('click', () => {
    if (!js_checa()) {
      return;
    }
    const data = new FormData();
    data.append('codigo', $F('codigo'));
    data.append('inscricao', $F('inscricao'));
    data.append('cep', $F('cep'));
    data.append('numero', $F('numero'));
    data.append('complemento', $F('complemento'));
    data.append('destinatario', $F('destinatario'));
    data.append('municipal', $F('municipal'));
    data.append('usuario', $F('usuario'));

    if (document.form1.municipal.value == '0') {
      data.append('bairro', $F('bairronome'));
      data.append('rua', $F('ruanome'));
    } else {
      data.append('bairro', $F('bairro'));
      data.append('rua', $F('rua'));
    }

    HttpClient.post('iss1_issbaseendereco.RPC.php', { body: data }).then(response => {
      if (response.erro) {
        alert(response.mensagem);
        return;
      }
      window.location.reload();
    });
  });

  const dropdown = document.getElementById('municipal');
  document.addEventListener('DOMContentLoaded', () => {
    dropdown.addEventListener('change', () => {

      const valor = dropdown.value;

      if (valor === '1') {
        trRua.innerHTML = `<td align="right" nowrap title="logradouro">
                            <b>
                              <?php
                              db_ancora("Logradouro:", ' js_mostraruas(true); ', 2)
                                ?>
                            </b>
                        </td>
                        <td align="left" nowrap>
                            <?php
                            db_input("rua", 8, 0, true, 'text', 2, " onchange='js_mostraruas(false);'")
                              ?>
                            <?php
                            db_input("ruadescricao", 40, 0, true, 'text', 3, "", "", "#FFFFFF");
                            ?>
                        </td`

        trBairro.innerHTML = `<td align="right" nowrap title="bairro">
                                <b>
                                    <?php
                                    db_ancora("Bairro:", "js_bairro(true);", 2);
                                    ?>
                                </b>
                            </td>
                            <td>
                                <?php
                                db_input('bairro', 8, 0, true, 'text', 2, " onchange='js_bairro(false);'")
                                  ?>
                                <?php
                                db_input('bairrodescricao', 40, 0, true, 'text', 3, "", "", "#FFFFFF")
                                  ?>
                            </td>`;
        return;
      }

      trRua.innerHTML = `<td>
                      Logradouro:
                    </td>
                    <td>
                      <?php
                      db_input('ruanome', 55, false, false, 'text', $db_opcao, "", "", "", "", 100);
                      ?>
                    </td`

      trBairro.innerHTML = `<td>
                            Bairro:
                          </td>
                          <td>
                            <?php
                            db_input('bairronome', 55, false, false, 'text', $db_opcao, "", "", "", "", 30);
                            ?>
                          </td>`;
    })

    dropdown.dispatchEvent(new Event('change'))
  });

  function js_bairro(mostra) {
    if (mostra == true) {
      js_OpenJanelaIframe('', 'db_iframe_bairros', 'func_bairro.php?funcao_js=parent.js_preenchebairro|j13_codi|j13_descr', 'Pesquisa', true);
    } else {
      js_OpenJanelaIframe('', 'db_iframe_bairros', 'func_bairro.php?funcao_js=parent.js_preenchebairro1&pesquisa_chave=' + document.form1.bairro.value, 'Pesquisa', false);
    }
  }

  function js_preenchebairro(chave, chave1) {
    document.form1.bairro.value = chave;
    document.form1.bairrodescricao.value = chave1;
    db_iframe_bairros.hide();
  }

  function js_preenchebairro1(chave, erro) {
    document.form1.bairrodescricao.value = chave;
    if (erro == true) {
      document.form1.bairro.focus();
      document.form1.bairro.value = '';
    }
    db_iframe_bairros.hide();
  }

  function js_mostraruas(mostra) {
    if (mostra == true) {
      js_OpenJanelaIframe('', 'db_iframe_logradouro', 'func_ruas.php?rural=1&funcao_js=parent.js_preencheruas|j14_codigo|j14_nome', 'Pesquisa', true);
    } else {
      js_OpenJanelaIframe('', 'db_iframe_logradouro', 'func_ruas.php?rural=1&funcao_js=parent.js_preencheruas1&pesquisa_chave=' + document.form1.rua.value, 'Pesquisa', false);
    }
  }

  function js_preencheruas(chave, chave1) {
    document.form1.rua.value = chave;
    document.form1.ruadescricao.value = chave1;
    db_iframe_logradouro.hide();
  }

  function js_preencheruas1(chave, chave1, erro) {
    document.form1.ruadescricao.value = chave1;
    if (erro != undefined) {
      document.form1.rua.focus();
      document.form1.rua.value = '';
    }
    db_iframe_logradouro.hide();
  }

</script>