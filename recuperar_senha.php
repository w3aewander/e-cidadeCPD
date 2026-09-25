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
 */

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="iso-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Cidade - Redefini&ccedil;&atilde;o de Senha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 440px;
            padding: 35px 30px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            max-height: 60px;
            max-width: 100%;
        }
        h2 {
            color: #1e293b;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        p.subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.45;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper input {
            width: 100%;
            padding: 12px 40px 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            background: #f8fafc;
        }
        .input-wrapper input:focus {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .input-wrapper .toggle-pwd {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
        }
        .btn-submit {
            width: 100%;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: #1d4ed8;
        }
        .btn-submit:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: left;
            line-height: 1.45;
            display: none;
        }
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2563eb;
            font-size: 14px;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .password-rules {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo-container">
        <img src="imagens/ecidade/login/logotipo_ecidade.png" alt="e-Cidade Logo" onerror="this.style.display='none'">
    </div>

    <h2>Redefini&ccedil;&atilde;o de Senha</h2>
    <p class="subtitle" id="form-subtitle">Informe sua nova senha para reativar o acesso ao e-Cidade.</p>

    <div id="alert-box" class="alert"></div>

    <?php if (empty($token)): ?>
        <div class="alert alert-danger" style="display: block;">
            <i class="fa fa-exclamation-triangle"></i> Link de recupera&ccedil;&atilde;o n&atilde;o informado ou inv&aacute;lido. Solicite um novo link na tela de login.
        </div>
        <a href="login.php" class="back-link"><i class="fa fa-arrow-left"></i> Voltar para a tela de Login</a>
    <?php else: ?>
        <form id="form-redefinir" onsubmit="return handleRedefinirSenha(event)">
            <input type="hidden" id="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="nova_senha">Nova Senha:</label>
                <div class="input-wrapper">
                    <input type="password" id="nova_senha" required placeholder="Digite no m&iacute;nimo 6 caracteres" minlength="6">
                    <i class="fa fa-eye toggle-pwd" onclick="togglePasswordVisibility('nova_senha', this)"></i>
                </div>
                <div class="password-rules">M&iacute;nimo de 6 caracteres.</div>
            </div>

            <div class="form-group">
                <label for="confirmacao_senha">Confirmar Nova Senha:</label>
                <div class="input-wrapper">
                    <input type="password" id="confirmacao_senha" required placeholder="Repita a nova senha" minlength="6">
                    <i class="fa fa-eye toggle-pwd" onclick="togglePasswordVisibility('confirmacao_senha', this)"></i>
                </div>
            </div>

            <button type="submit" id="btn-submit" class="btn-submit">
                <i class="fa fa-key"></i> Salvar Nova Senha
            </button>
        </form>

        <a href="login.php" class="back-link" id="link-voltar"><i class="fa fa-arrow-left"></i> Voltar para a tela de Login</a>
    <?php endif; ?>
</div>

<script>
function togglePasswordVisibility(inputId, icon) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.className = "fa fa-eye-slash toggle-pwd";
    } else {
        input.type = "password";
        icon.className = "fa fa-eye toggle-pwd";
    }
}

function showAlert(message, type) {
    var alertBox = document.getElementById('alert-box');
    alertBox.className = 'alert alert-' + type;
    alertBox.innerHTML = message;
    alertBox.style.display = 'block';
}

function hideAlert() {
    var alertBox = document.getElementById('alert-box');
    alertBox.style.display = 'none';
}

function handleRedefinirSenha(event) {
    event.preventDefault();
    hideAlert();

    var token = document.getElementById('token').value;
    var novaSenha = document.getElementById('nova_senha').value;
    var confirmacaoSenha = document.getElementById('confirmacao_senha').value;
    var btnSubmit = document.getElementById('btn-submit');

    if (novaSenha.length < 6) {
        showAlert('<i class="fa fa-exclamation-circle"></i> A senha deve ter no m&iacute;nimo 6 caracteres.', 'danger');
        return false;
    }

    if (novaSenha !== confirmacaoSenha) {
        showAlert('<i class="fa fa-exclamation-circle"></i> A confirma&ccedil;&atilde;o n&atilde;o confere com a nova senha.', 'danger');
        return false;
    }

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Salvando nova senha...';

    var formData = new FormData();
    formData.append('token', token);
    formData.append('nova_senha', novaSenha);
    formData.append('confirmacao_senha', confirmacaoSenha);

    fetch('v4/auth/redefinir-senha', {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa fa-key"></i> Salvar Nova Senha';

        if (data.error) {
            showAlert('<i class="fa fa-exclamation-triangle"></i> ' + (data.message || 'Erro ao redefinir senha.'), 'danger');
        } else {
            showAlert('<i class="fa fa-check-circle"></i> ' + data.message, 'success');
            document.getElementById('form-redefinir').style.display = 'none';
            document.getElementById('form-subtitle').style.display = 'none';
            
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000);
        }
    })
    .catch(function(error) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa fa-key"></i> Salvar Nova Senha';
        showAlert('<i class="fa fa-exclamation-triangle"></i> Falha de comunica&ccedil;&atilde;o com o servidor.', 'danger');
    });

    return false;
}
</script>

</body>
</html>
