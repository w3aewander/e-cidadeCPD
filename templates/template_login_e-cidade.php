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

?>
<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <title>e-Cidade</title>
    <meta charset="iso-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Expires" CONTENT="0">

    <link href="imagens/ecidade/favicon.png" rel="icon" type="image/png" />
    <link href="estilos/jQueryUI/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css"/>
    <link href="estilos/login.css" rel="stylesheet" type="text/css"/>
    <link href="assets/fontawesome/css/all.min.css" rel="stylesheet" type="text/css"/>

    <script language="JavaScript" type="text/javascript" src="scripts/md5.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/jquery-2.1.1.min.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/jquery-ui-1.10.4.custom.min.js"></script>

    <style>
      html, body {
        height: 100%;
        width: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
      }

      /* Container flutuante elegantemente posicionado e centralizado no lado direito */
      .login-wrapper {
        position: fixed;
        top: 0;
        right: 6%;
        bottom: 0;
        width: 375px;
        max-width: 90vw;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        box-sizing: border-box;
        pointer-events: none;
      }

      .container {
        pointer-events: auto;
        position: relative !important;
        top: auto !important;
        right: auto !important;
        bottom: auto !important;
        left: auto !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 360px !important;
        min-height: auto !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border-radius: 20px !important;
        padding: 20px 22px 14px !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        box-sizing: border-box !important;
      }

      .logo-ecidade {
        margin: 0 auto 4px auto !important;
        display: block !important;
        max-width: 175px !important;
        height: auto !important;
      }

      .container form {
        border-top: 1px solid #e2e8f0 !important;
        padding-top: 10px !important;
        margin-top: 6px !important;
      }

      .form-group {
        margin-bottom: 8px;
        width: 100%;
        text-align: left;
      }

      .form-group label {
        display: block !important;
        float: none !important;
        width: 100% !important;
        margin: 0 0 3px 0 !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        color: #475569 !important;
        line-height: normal !important;
      }

      .input-icon-wrapper {
        position: relative;
        width: 100%;
      }

      .input-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 12px;
        pointer-events: none;
      }

      .form-control {
        width: 100% !important;
        max-width: 100% !important;
        float: none !important;
        height: 34px !important;
        padding: 6px 10px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 7px !important;
        background-color: #f8fafc !important;
        font-size: 13px !important;
        color: #1e293b !important;
        box-sizing: border-box !important;
        transition: all 0.2s ease-in-out !important;
      }

      .form-control.with-icon {
        padding-left: 32px !important;
      }

      .form-control:focus {
        background-color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        outline: none !important;
      }

      .btn-primary-login {
        width: 100% !important;
        height: 36px !important;
        float: none !important;
        margin: 10px 0 0 0 !important;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        border: none !important;
        border-radius: 7px !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25) !important;
        text-align: center !important;
        line-height: 36px !important;
        box-sizing: border-box !important;
      }

      .btn-primary-login:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
        box-shadow: 0 5px 14px rgba(37, 99, 235, 0.35) !important;
        transform: translateY(-1px);
      }

      .btn-primary-login:active {
        transform: translateY(0);
      }

      .btn-loading-login {
        width: 100% !important;
        height: 36px !important;
        float: none !important;
        margin: 10px 0 0 0 !important;
        background: #94a3b8 !important;
        color: #ffffff !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        border: none !important;
        border-radius: 7px !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        text-align: center !important;
        line-height: 36px !important;
        box-sizing: border-box !important;
      }

      .container-forgot-password {
        margin: 8px 0 0 0 !important;
        width: 100% !important;
        float: none !important;
      }

      .btn-forgot-password {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        color: #1e40af !important;
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        border-radius: 7px !important;
        padding: 6px 10px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: all 0.2s ease-in-out !important;
        cursor: pointer !important;
        width: 100% !important;
        box-sizing: border-box !important;
      }

      .btn-forgot-password:hover {
        background-color: #dbeafe !important;
        color: #1d4ed8 !important;
        border-color: #93c5fd !important;
        text-decoration: none !important;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.12) !important;
      }

      .container-lgpd-badge {
        margin: 8px 0 0 0 !important;
        width: 100% !important;
        float: none !important;
      }

      .lgpd-card {
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        background: rgba(240, 253, 244, 0.85) !important;
        border: 1px solid #bbf7d0 !important;
        border-left: 3px solid #16a34a !important;
        border-radius: 7px !important;
        padding: 5px 8px !important;
        box-sizing: border-box !important;
        text-align: left !important;
      }

      .lgpd-icon {
        color: #16a34a !important;
        font-size: 13px !important;
        flex-shrink: 0 !important;
      }

      .lgpd-content {
        display: flex !important;
        flex-direction: column !important;
        line-height: 1.2 !important;
      }

      .lgpd-title {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        color: #14532d !important;
        letter-spacing: 0.1px !important;
      }

      .lgpd-text {
        font-size: 9px !important;
        color: #166534 !important;
        margin-top: 1px !important;
      }

      #testaLogin {
        width: 100% !important;
        margin: 6px 0 0 0 !important;
        color: #dc2626 !important;
        font-weight: 600 !important;
        text-align: center !important;
        display: block !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
      }

      .login-footer {
        margin-top: 8px !important;
        text-align: center !important;
        width: 100% !important;
      }

      .microsist-logo {
        max-width: 95px !important;
        height: auto !important;
        opacity: 0.85 !important;
        transition: opacity 0.2s ease-in-out !important;
        display: inline-block !important;
      }

      .microsist-logo:hover {
        opacity: 1 !important;
      }

      /* Modal de Recuperacao */
      .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 99999;
        align-items: center;
        justify-content: center;
      }

      .modal-content {
        background: #ffffff;
        border-radius: 12px;
        width: 90%;
        max-width: 460px;
        padding: 25px 30px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.35);
        position: relative;
        text-align: left;
        font-family: Arial, sans-serif;
        box-sizing: border-box;
      }

      .modal-header {
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 14px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .modal-header h3 {
        margin: 0;
        color: #1e293b;
        font-size: 19px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #94a3b8;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        transition: color 0.2s;
      }

      .modal-close:hover {
        color: #334155;
      }

      .modal-body label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-top: 14px;
        margin-bottom: 6px;
      }

      .modal-body input[type="text"], .modal-body input[type="email"] {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
        outline: none;
        transition: all 0.2s;
        background: #f8fafc;
      }

      .modal-body input:focus {
        border-color: #2563eb;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
      }

      .modal-footer {
        margin-top: 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
      }

      .btn-cancelar {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
      }

      .btn-cancelar:hover {
        background: #e2e8f0;
      }

      .btn-enviar-recup {
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, box-shadow 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
      }

      .btn-enviar-recup:hover {
        background: #1d4ed8;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
      }

      .btn-enviar-recup:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
      }

      .modal-alert {
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 14px;
        display: none;
        line-height: 1.45;
      }

      .modal-alert-info {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
      }

      .modal-alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
      }

      .modal-alert-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
      }
    </style>
  </head>

  <body class="<?php echo $sClassAtiva;?>">

    <div class="login-wrapper">
      <div class="container">

        <a href="http://www.softwarepublico.gov.br/ver-comunidade?community_id=15315976" title="Entre na comunidade e-Cidade no Portal do Software P&uacute;blico." target="_blank">
          <img class="logo-ecidade" src="imagens/ecidade/login/logotipo_ecidade.png" alt="e-Cidade"/>
        </a>

        <form method="post" name="form1">

          <div class="access-fields">

            <?php if (isset($DB_CONEXAO)) { ?>
              <input id="servidor" name="servidor" type="hidden" value="<?=@$servidor?>"/>
              <input id="port"     name="port"     type="hidden" value="<?=@$port?>"/>
              <input id="user"     name="user"     type="hidden" value="<?=@$user?>"/>
              <input id="senh"     name="senh"     type="hidden" value="<?=@$senh?>"/>
              <input id="base"     name="base"     type="hidden" value="<?=@$base?>"/>

              <div class="form-group">
                <label for="serv">Host:</label>
                <select name="serv" id="serv" class="form-control">
                  <option name="condicaoservidor" value="">Selecione um servidor</option>
                  <?php for ($iInd = 0; $iInd < count($DB_CONEXAO); $iInd++) { ?>
                    <option name="condicaoservidor" value="<?=$iInd?>"><?=$DB_CONEXAO[$iInd]["SERVIDOR"].":".$DB_CONEXAO[$iInd]["PORTA"] ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="form-group">
                <label for="basename">Base:</label>
                <input type="text" name="basename" id="basename" class="form-control" onclick="this.value=''"/>
                <input type="hidden" name="idbasename" id="idbasename"/>
              </div>
            <?php } ?>

            <div class="form-group">
              <label for="usu_login">Usu&aacute;rio / Login:</label>
              <div class="input-icon-wrapper">
                <i class="fa fa-user input-icon"></i>
                <input name="login" id="usu_login" type="text" class="form-control with-icon" placeholder="Informe seu login"/>
              </div>
            </div>

            <div class="form-group">
              <label for="usu_senha">Senha:</label>
              <div class="input-icon-wrapper">
                <i class="fa fa-lock input-icon"></i>
                <input name="senha" id="usu_senha" type="password" class="form-control with-icon" placeholder="Informe sua senha"/>
              </div>
            </div>

            <div id="captcha" class="container-captcha <?php echo($lCaptcha ? '' : 'container-captcha-hide'); ?>">
              <?php include('captcha.php'); ?>
            </div>

            <input name="btnlogar" id="btnlogar" type="button" value="Entrar" class="btn-primary-login"/>

            <button name="btnloading" id="btnloading" class="btn-loading-login" disabled style="display: none;">
              <i class="fa fa-spinner fa-spin"></i> Entrando...
            </button>

            <div class="container-forgot-password">
              <a href="javascript:void(0)" onclick="abrirModalRecuperacao()" class="btn-forgot-password">
                <i class="fa fa-key"></i> Esqueci minha senha / Recuperar acesso
              </a>
            </div>

            <div class="container-lgpd-badge">
              <div class="lgpd-card">
                <i class="fa fa-shield-alt lgpd-icon"></i>
                <div class="lgpd-content">
                  <span class="lgpd-title">Privacidade &amp; Seguran&ccedil;a</span>
                  <span class="lgpd-text">Ambiente em conformidade com a <strong>LGPD</strong> (Lei n&ordm; 13.709/2018).</span>
                </div>
              </div>
            </div>

          </div>

          <span id="testaLogin"></span>

          <div class="login-footer">
            <a href="https://www.cpd-municipal.com.br/" target="_blank" title="CPD Municipal / Microsist">
              <img src="templates/microsist-logo.png" class="microsist-logo" alt="Microsist">
            </a>
          </div>

        </form>

      </div>
    </div>

    <!-- Modal de Recuperacao de Senha -->
    <div id="modal-recuperacao" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3><i class="fa fa-unlock-alt" style="color: #2563eb;"></i> Recupera&ccedil;&atilde;o de Senha</h3>
          <button type="button" class="modal-close" onclick="fecharModalRecuperacao()">&times;</button>
        </div>
        <div class="modal-body">
          <div id="modal-alert" class="modal-alert"></div>
          <p style="font-size: 13px; color: #64748b; line-height: 1.45; margin-bottom: 12px;">
            Informe seu usu&aacute;rio e o e-mail cadastrado no sistema para receber as instru&ccedil;&otilde;es seguras de redefini&ccedil;&atilde;o de senha.
          </p>

          <label for="recup_login">Usu&aacute;rio / Login:</label>
          <input type="text" id="recup_login" placeholder="Ex: nome.sobrenome"/>

          <label for="recup_email">E-mail Cadastrado:</label>
          <input type="email" id="recup_email" placeholder="seuemail@municipio.gov.br"/>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancelar" onclick="fecharModalRecuperacao()">Cancelar</button>
          <button type="button" id="btn-enviar-recup" class="btn-enviar-recup" onclick="solicitarRecuperacaoSenha()">
            <i class="fa fa-paper-plane"></i> Enviar Link de Recupera&ccedil;&atilde;o
          </button>
        </div>
      </div>
    </div>

  </body>
  <script src="scripts/classes/http/http.js"></script>
  <script type="text/javascript">

  const btnLogar = document.getElementById('btnlogar');
  const btnLoading = document.getElementById('btnloading');

  function abrirModalRecuperacao(loginPreenchido, mensagemAlerta) {
    const modal = document.getElementById('modal-recuperacao');
    const inputLogin = document.getElementById('recup_login');
    const inputEmail = document.getElementById('recup_email');
    const modalAlert = document.getElementById('modal-alert');

    inputLogin.value = loginPreenchido || document.getElementById('usu_login').value || '';
    inputEmail.value = '';

    if (mensagemAlerta) {
      modalAlert.className = 'modal-alert modal-alert-danger';
      modalAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + mensagemAlerta;
      modalAlert.style.display = 'block';
    } else {
      modalAlert.style.display = 'none';
    }

    modal.style.display = 'flex';
    if (!inputLogin.value) {
      inputLogin.focus();
    } else {
      inputEmail.focus();
    }
  }

  function fecharModalRecuperacao() {
    document.getElementById('modal-recuperacao').style.display = 'none';
  }

  function solicitarRecuperacaoSenha() {
    const login = document.getElementById('recup_login').value.trim();
    const email = document.getElementById('recup_email').value.trim();
    const btnEnviar = document.getElementById('btn-enviar-recup');
    const modalAlert = document.getElementById('modal-alert');

    if (!login || !email) {
      modalAlert.className = 'modal-alert modal-alert-danger';
      modalAlert.innerHTML = '<i class="fa fa-exclamation-circle"></i> Por favor, preencha o login e o e-mail cadastrado.';
      modalAlert.style.display = 'block';
      return;
    }

    btnEnviar.disabled = true;
    btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Enviando...';
    modalAlert.style.display = 'none';

    const formData = new FormData();
    formData.append('login', login);
    formData.append('email', email);

    fetch('v4/auth/esqueci-senha', {
      method: 'POST',
      body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      btnEnviar.disabled = false;
      btnEnviar.innerHTML = '<i class="fa fa-paper-plane"></i> Enviar Link de Recupera&ccedil;&atilde;o';

      if (data.error) {
        modalAlert.className = 'modal-alert modal-alert-danger';
        modalAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + (data.message || 'Erro ao processar solicita&ccedil;&atilde;o.');
        modalAlert.style.display = 'block';
      } else {
        modalAlert.className = 'modal-alert modal-alert-success';
        modalAlert.innerHTML = '<i class="fa fa-check-circle"></i> ' + data.message;
        modalAlert.style.display = 'block';
        document.getElementById('recup_email').value = '';
      }
    })
    .catch(function(err) {
      btnEnviar.disabled = false;
      btnEnviar.innerHTML = '<i class="fa fa-paper-plane"></i> Enviar Link de Recupera&ccedil;&atilde;o';
      modalAlert.className = 'modal-alert modal-alert-danger';
      modalAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Falha de comunica&ccedil;&atilde;o com o servidor.';
      modalAlert.style.display = 'block';
    });
  }

  $( "#basename" ).autocomplete({
    source: function( request, response ) {

      $.ajax({
        url: "BuscaBase.RPC.php",
        data: {
          string   : $("#basename").val(),
          servidor : $("#serv").val()
        },
        type: "post",
        dataType: "json",
        success: function( data ) {
          response( $.map( data, function( item ) {
            return {
              label: decodeURIComponent( item.label ),
              value: decodeURIComponent( item.label ),
              codigo: decodeURIComponent( item.cod )
            }
          }));
        }
      });
    },
    minLength: 3,
    select: function( event, ui ) {

      if (ui.item.value == 0) {

        $('#basename').val('');
        return false;
      }

      aDadosConexao = decodeURIComponent( ui.item.codigo ).split(':');

      $('#servidor').val( aDadosConexao[0] );
      $('#port').val( aDadosConexao[1] );
      $('#user').val( aDadosConexao[2] );
      $('#senh').val( tagString(aDadosConexao[3]) );
      $('#base').val( aDadosConexao[4] );

      ui.item.value = ui.item.label;
    }
  });

  async function js_acessar_dbportal() {

      btnLogar.style.display = 'none';
      btnLoading.style.display = 'block';

      const login = document.getElementById('usu_login').value;
      const senha = document.getElementById('usu_senha').value;

      const formData = new FormData();
      formData.append('DB_HOST', $('#servidor').val() || '');
      formData.append('DB_DATABASE', $('#base').val() || '');
      formData.append('DB_PORT', $('#port').val() || '');
      formData.append('DB_USERNAME', $('#user').val() || '');
      formData.append('DB_PASSWORD', $('#senh').val() || '');
      formData.append('username', login);
      formData.append('password', senha ? senha : '');

      if ($('#captcha')) {
          formData.append('conteudoCaptcha', $('#ct_captcha').val());
      }
      const response = await HttpClient.post('v4/login', { body: formData, reportProgress: false });
      btnLogar.style.display = 'block';
      btnLoading.style.display = 'none';

      if (response.error) {
          renderErro(response);
          return;
      }

      localStorage.removeItem('ecidade@user_logout');
      localStorage.setItem('ecidade@user_token', JSON.stringify({
          access_token: response.data.access_token,
          expires_at: Date.now() + response.data.expires_in * 1000,
          refresh_token: response.data.refresh_token,
      }));

      $('#testaLogin').html('');
      $('#usu_senha').val('');

      location.href = 'extension/desktop';
  }

  function renderErro(response) {
      if (response.code === 'E_CAPTCHA') {
          document.getElementById('captcha').classList.remove('container-captcha-hide');
          reloadCaptcha();
      }

      if (response.code === 'E_MAX_ATTEMPTS') {
          abrirModalRecuperacao(
            response.usuario || $('#usu_login').val(),
            response.message || 'Voc&ecirc; excedeu o limite de 3 tentativas de login. Seu acesso foi temporariamente bloqueado.'
          );
      }

      document.getElementById('testaLogin').innerHTML = response.message;
  }

  $(document).ready(function() {

    $('#btnlogar').on('click', function(event) {
      js_acessar_dbportal();
    });

    $('#usu_senha').on('keyup', function(event){

      if (event.keyCode == 13) {
        js_acessar_dbportal();
      }
    });

    $('#ct_captcha').on('keyup', function(event){

      if (event.keyCode == 13) {
        js_acessar_dbportal();
      }
    });

    $("#usu_login").focus();
    js_verifica_cookie();
  });

  </script>
</html>
