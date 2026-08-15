<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2014  DBSeller Servicos de Informatica             
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


require_once("libs/db_stdlib.php");
require_once("libs/db_conecta".".php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_utils.php");
require_once("dbforms/db_funcoes.php");
require_once("classes/db_rhpessoal_classe.php");

db_postmemory($HTTP_POST_VARS);

$clrhpessoal = new cl_rhpessoal;
$rotulocampo = new rotulocampo;

$rotulocampo->label("rh01_regist");
$rotulocampo->label("rh02_seqpes");
$rotulocampo->label("z01_nome");
$texto = "
CERTIFICO que a Lei nº 531 de 18/01/1985, assegura aos servidores do Município de Niterói aposentadorias voluntárias, por invalidez e compulsórias, e pensão por morte com aproveitamento de tempo de contribuição para o Regime Geral de Previdência Social ou para outro Regime Próprio de Previdência Social, na forma da contagem recíproca, conforme Lei Federal nº 6.226, de 14/07/1975, com alteração dada pela lei Federal nº 6.864 de 01/12/1980.
";
?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <style type="text/css">
      #datai{
        width: 78px;
      }
    </style>
    <?
      db_app::load('scripts.js, strings.js, prototype.js, estilos.css, datagrid.widget.js, AjaxRequest.js');

    ?>
  </head>
  <body class="body-default">
    <div class="container">
      <form name="form1" method="post" action="" >
        <fieldset>
          <legend>Certidão de Tempo de Contribuição</legend>

          <table>
            <tr>
              <td nowrap title="<?php echo $Trh01_regist; ?>">
                <label class="bold" for="rh01_regist" id="lbl_rh01_regist">
                  <?php db_ancora($Srh01_regist . ':', "js_pesquisarh01_regist(true);", 1); ?>
                </label>
              </td>
              <td>
                <?php
                  db_input('rh01_regist', 8, $Irh01_regist, true, 'text', 1, " onchange='js_pesquisarh01_regist(false);'");
                  db_input('z01_nome', 30, $Iz01_nome, true, 'text', 3, '');
                ?>
              </td>
            </tr>

            <tr>
              <td nowrap title="Órgão">
                <label class="bold" for="rh01_regist" id="lbl_rh01_regist">
                   Órgão:
                </label>
              </td>
              <td>
                <?php
                  db_input('orgao', 30, 0 , true, 'text', 1, "");
                ?>
              </td>
            </tr>

            <tr>
              <td nowrap title="Processo">
                <label class="bold" for="rh01_regist" id="lbl_rh01_regist">
                   Processo:
                </label>
              </td>
              <td>
                <?php
                  db_input('processo', 30, 0 , true, 'text', 1, "");
                ?>
              </td>
            </tr>


            <tr>
              <td nowrap title="Certidão">
                <label class="bold" for="rh01_regist" id="lbl_rh01_regist">
                   Nº Certidão:
                </label>
              </td>
              <td>
                <?php
                  db_input('certidao', 30, 0 , true, 'text', 1, "");
                ?>
              </td>
            </tr>



            <tr>
              <td nowrap title="Certidão">
                <label class="bold" for="rh01_regist" id="lbl_rh01_regist">
                   Local e Data:
                </label>
              </td>
              <td>
                <?php
                  db_input('localdata', 30, 0 , true, 'text', 1, "");
                ?>
              </td>
            </tr>

            <tr>
              <td nowrap title="Processo">
                <label class="bold" for="rh01_regist" id="lbl_rh01_regist">
                   Discriminação das Faltas, Licenças, Penalidades <br> ou outros dados constantes do assentamento:
                </label>
              </td>
               <td>
                <?php
                  db_textarea('texto', 5, 50 , 0, 'text', 1, "");
                ?>
              </td>
            </tr>

          <tr>
            

          </tr>
          </table>
        </fieldset>

        <input name="relatorio" id="relatorio" type="button" value="Processar" onclick="js_Salvar();" >

      </form>
    </div>

    <?php
      db_menu( db_getsession("DB_id_usuario"),
               db_getsession("DB_modulo"),
               db_getsession("DB_anousu"),
               db_getsession("DB_instit") );
    ?>
  </body>
  <script>
    var sMensagens = "recursoshumanos.rh.rec2_certcomprobatorio.";
    var RPC        = 'rec4_certidaotempo.RPC.php';
    function js_limparCampos(){

      $('rh01_regist').value = '';
      $('z01_nome').value    = '';
      $('datai').value       = '';
      $('numcert').value     = '';
      $('emissor').value     = '';
      $('z01_nomeemissor').value = '';
    }

    function js_emite(codigo) {

      if (isNaN(document.form1.rh01_regist.value)) {

        alert( _M( sMensagens + "matricula_invalida") );
        $('rh01_regist').value = '';
        document.form1.rh01_regist.focus();
        return false
      }

      if (document.form1.rh01_regist.value == '' || isNaN(document.form1.rh01_regist.value)) {
        alert( _M( sMensagens + "matricula_obrigatoria") );
        document.form1.rh01_regist.focus();
        return false
      }


      qry  = "?regist="+ document.form1.rh01_regist.value;
      qry += "&codigocertidao="+codigo;
      jan = window.open( 'rec2_certidaocontribuicao002.php' + qry,
                         '',
                         'width=' + (screen.availWidth-5) + ',height='+(screen.availHeight-40) + ',scrollbars=1,location=0 ' );
      jan.moveTo(0,0);

    }


    function js_pesquisarh01_regist(mostra) {

      if (mostra == true) {
        js_OpenJanelaIframe( 'top.corpo',
                             'db_iframe_rhpessoal',
                             'func_rhpessoal.php?funcao_js=parent.js_mostrapessoal1|rh01_regist|z01_nome&instit=<?=(db_getsession("DB_instit"))?>',
                             'Pesquisa',
                             true );
      } else {

        if (document.form1.rh01_regist.value != '') {
          js_OpenJanelaIframe( 'top.corpo',
                               'db_iframe_rhpessoal',
                               'func_rhpessoal.php?testarescisao=true&pesquisa_chave=' + document.form1.rh01_regist.value
                               + '&funcao_js=parent.js_mostrapessoal&instit=<?=(db_getsession("DB_instit"))?>',
                               'Pesquisa',
                               false );
        } else {
          document.form1.z01_nome.value = '';
        }
      }
    }

    function js_mostrapessoal(chave, erro) {

      document.form1.z01_nome.value = chave;
      if (erro == true) {

        document.form1.rh01_regist.focus();
        document.form1.rh01_regist.value = '';
      } else {
      }
      js_Busca();
    }

    function js_mostrapessoal1(chave1, chave3) {

      document.form1.rh01_regist.value = chave1;
      document.form1.z01_nome.value    = chave3;
      db_iframe_rhpessoal.hide();
      js_Busca();
    }
    

function js_Salvar(){

    iMatricula = $('rh01_regist').value;
    iOrgao     = $('orgao').value;
    iProcesso  = $('processo').value;
    iTexto     = $('texto').value;
    iCertidao  = $('certidao').value;
    iLocal     = $('localdata').value;
    new AjaxRequest(
      RPC,
      {exec:'lSalvar', matricula: iMatricula, 
                           orgao: iOrgao, 
                        processo: iProcesso , 
                           texto: iTexto,
                        certidao: iCertidao,
                           local: iLocal} ,
      function (oRetorno, lErro) {

        if (lErro) {
          alert(oRetorno.mensagem.urlDecode());
        } else {
          js_emite(oRetorno.codigo);
        }
      }
    ).setMessage('Aguarde, Processando...').asynchronous(false).execute();
}

function js_Busca(){

    iMatricula =  $('rh01_regist').value;
    new AjaxRequest(
      RPC,
      {exec:'lBusca', matricula: iMatricula } ,
      function (oRetorno, lErro) {

        if (lErro) {
          alert(oRetorno.mensagem.urlDecode());
        } else {
          if( oRetorno.codigo != null ){
            var r = confirm("Já existe uma Certidão para esse Servidor!\nDeseja Reemitir a última impressa?");
            if (r == true) {
              js_emite(oRetorno.codigo);
            } else {
              return false;
            }
          }
        }
      }
    ).setMessage('Aguarde, Buscando...').asynchronous(false).execute();
}

</script>
</html>
