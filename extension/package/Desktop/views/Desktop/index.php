<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $this->document->getCharset(); ?>"/>
    <title><?php echo $this->document->getTitle(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="ecidade:version" content="<?php echo $this->version; ?>">
    <meta name="ECIDADE_REQUEST_PATH" content="<?= ECIDADE_REQUEST_PATH ?>">
    <meta name="USER_ID" content="<?= $this->usuarioSistema->getCodigo(); ?>">
    <meta name="PUSHER_CONFIG" content='<?= json_encode(getPusherConfig()); ?>'>
    <?php echo $this->document->renderLinks(); ?>
    <base href="<?php echo ECIDADE_CURRENT_EXTENSION_REQUEST_PATH; ?>"/>
    <link rel="stylesheet" href="public/css/app.css">
</head>
<body><!--<div class="ribbon"><a href="#">HOMOLOGA&Ccedil;&Atilde;O</a></div>-->
<div class="topbar">
    <div style="float: left; display: flex; align-items: center; height: 35px; padding-left: 12px; gap: 10px;">
        <span style="font-weight: bold; color: #1e3a8a; font-size: 13px;">e-Cidade <span style="background: #1e3a8a; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold;">CPD-MUNICIPAL</span></span>
        <div id="btn-lgpd-topbar" onclick="Desktop.Window.create('Painel de Governança LGPD', { action: 'con4_lgpd_painel001.php', iModuloId: 1, iInstitId: <?=$instituicaoLogada?>, iAreaId: 11, lAtalhoDesktop: true })" style="cursor: pointer; background-color: #2e7d32; color: #ffffff; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.15);" title="Clique para abrir o Painel de Governança LGPD">
        <div id="btn-lgpd-topbar" style="cursor: pointer; background-color: #2e7d32; color: #ffffff; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.15);" title="Clique para abrir o Painel de Governança LGPD">
            <span>🛡️</span> Sistema Adequado à LGPD (Lei 13.709/2018)
    <div style="float: left; display: flex; align-items: center; height: 35px; padding-left: 12px; gap: 12px;">
        <span style="font-weight: bold; color: #1e3a8a; font-size: 13px; letter-spacing: -0.2px;">e-Cidade <span style="background: #1e3a8a; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold;">CPD-MUNICIPAL</span></span>
        <div id="btn-lgpd-topbar" style="cursor: pointer; background-color: #2e7d32; color: #ffffff; padding: 3px 12px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.15); line-height: 18px;" title="Clique para abrir o Painel de Governança LGPD">
            <span style="font-size: 12px;">&#128737;</span> Sistema Adequado &agrave; LGPD (Lei 13.709/2018)
        </div>
    </div>
    <div class="user-nav">
        <div class="dropdown user-nav-item settings" id="documentos-atividades">
            <div class="dropdown-toggle icon-document" id="totalUserDocuments">
            </div>
        </div>

        <div data-type="dropdown" class="dropdown user-nav-item settings">
            <div class="dropdown-toggle icon-notification" title="Notificações">
                <div class='notify' id="count-notifications" style="display: none;"></div>
            </div>

            <div class="dropdown-menu">
                <div class="loader" id="notification-loader">
                    <div class="loader-wheel"></div>
                </div>
                <ul class="notifications" id="notifications"></ul>
            </div>
        </div>

        <div data-type="dropdown" class="dropdown user-nav-item settings">
            <div class="dropdown-toggle icon-config"></div>

          <ul class="dropdown-menu">
            <li><a data-in-full-screen="false" id="fullscreen">Usar em tela cheia</a></li>
          </ul>
        </div>

        <div data-type="dropdown" class="dropdown user-nav-item user">
            <div class="dropdown-toggle">
                <span class="user-name"><?php echo $this->usuarioSistema->getLogin(); ?></span>
                <img class="user-picture" src="assets/img/topbar/user-picture-default.png">
            </div>

            <div class="dropdown-menu">
                <ul>
                    <li class="dropdown-item-no-fx">
                        <div class="profile">
                            <div class="user-info">
                                <div class="picture">
                                    <img src="<?php echo ECIDADE_REQUEST_PATH . $this->caminhoFoto; ?>">
                                </div>
                                <div class="name">
                                    <span>
                                        <?php echo \DBString::utf8_encode_all($this->usuarioSistema->getNome()); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="system-info">
                                <p>
                                    <b>Base:</b>
                                    <em class="base-name selectable">
                                        <?php echo $this->request->session()->get('DB_NBASE'); ?>
                                    </em>
                                </p>
                                <p>
                                    <b>Servidor:</b>
                                    <em class="selectable">
                                        <?php echo $this->request->session()->get('DB_servidor') . ':' . $this->request->session()->get('DB_porta'); ?>
                                    </em>
                                </p>
                            </div>
                        </div>
                    </li>
                    <li><a id="alterarSenha">Alterar Senha</a></li>
                    <li><a id="block">Bloquear</a></li>
                    <li><a href="Window/logout" id="logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="taskbar-container">
    <div class="taskbar-menu-button" title="Menu Principal">MENU</div>

    <ul class="taskbar-buttons" id="taskbar-buttons"></ul>

    <div title="Lista de janelas abertas" class="taskbar-buttons-modal" style="display:none;">
        <div class="icon">
            <span class="bar-1"></span>
            <span class="bar-2"></span>
            <span class="bar-3"></span>
        </div>

        <div class="content"></div>
    </div>
</div>

<div id="menu">
    <div class="menu-header">
        <div class="menu-actions">
            <div class="menu-action-home"><span></span></div>
            <div class="menu-action-search">
                <input name="menu-search" id="menu-search"/>
                <span class="menu-search-icon"></span>
            </div>
        </div>

        <div class="menu-breadcrumb selectable">
            <ul id="menu-breadcrumb"></ul>
        </div>

        <div class="menu-resizer"></div>

        <div class="menu-close"><span>&times;</span></div>
    </div>
    <div class="menu-content">
        <div class="menu-pager menu-pager-left disabled">
            <div class="icon"></div>
        </div>

        <div class="menu-list-container">
            <div class="menu-list-title">Instituições</div>
            <div id="instituicoes" class="menu-list"></div>
        </div>

        <div class="menu-list-container divider-left">
            <div class="menu-list-title">Áreas</div>
            <div id="areas" class="menu-list"></div>
        </div>

        <div class="menu-list-container divider-left">
            <div class="menu-list-title">Módulos</div>
            <div id="modulos" class="menu-list"></div>
        </div>

        <div class="menu-pager menu-pager-right">
            <div class="icon"></div>
        </div>
    </div>
</div>

<div id="desktop-loader" class="desktop-loader" style="transition: opacity 225ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;">
    <div class="loader-wheel"></div>
    <p id="desktop-loader-messager"></p>
</div>
<script type="text/javascript">
    var ECIDADE_REQUEST_PATH = '<?php echo ECIDADE_REQUEST_PATH; ?>';
    var ECIDADE_DESKTOP = true;
</script>
<?php
echo $this->document->renderScripts();
$instituicaoLogada = is_null($this->instituicaoUsuario) ? 1 : $this->instituicaoUsuario;
?>
<script>
    const btnDocumentos = document.getElementById("documentos-atividades");
    btnDocumentos.addEventListener('click', () => {
        var parametros = {
            action: 'con4_atividades_documentos.php',
            iInstitId: <?=$instituicaoLogada?>, // Instituição Prefeitura
            iAreaId: 4, // Area Patrimonial
            iModuloId: 604, // Módulo Protocolo
            lAtalhoDesktop: true
        }
    var btnDocumentos = document.getElementById("documentos-atividades");
    if (btnDocumentos) {
        btnDocumentos.addEventListener('click', () => {
        btnDocumentos.addEventListener('click', function() {
            var parametros = {
                action: 'con4_atividades_documentos.php',
                iInstitId: <?=$instituicaoLogada?>, // Instituição Prefeitura
                iAreaId: 4, // Area Patrimonial
                iModuloId: 604, // Módulo Protocolo
                iInstitId: <?=$instituicaoLogada?>,
                iAreaId: 4,
                iModuloId: 604,
                lAtalhoDesktop: true
            };
            if (window.Desktop && Desktop.Window) {
                Desktop.Window.create('Andamento de Documentos', parametros);
                var ic = document.querySelector("#documentos-atividades .icon-document");
                if (ic) ic.innerHTML = "";
            }

        Desktop.Window.create('Andamento de Documentos', parametros);
        document.querySelector("#documentos-atividades .icon-document").innerHTML = "";
    });
            Desktop.Window.create('Andamento de Documentos', parametros);
            document.querySelector("#documentos-atividades .icon-document").innerHTML = "";
        });
    }

    const btnLgpd = document.getElementById("btn-lgpd-topbar");
    var btnLgpd = document.getElementById("btn-lgpd-topbar");
    if (btnLgpd) {
        btnLgpd.addEventListener('click', () => {
        btnLgpd.addEventListener('click', function() {
            var parametros = {
                action: 'con4_lgpd_painel001.php',
                iInstitId: <?=$instituicaoLogada?>,
                iAreaId: 11,
                iModuloId: 1,
                lAtalhoDesktop: true
            };
            Desktop.Window.create('Painel de Governança LGPD', parametros);
            if (window.Desktop && Desktop.Window) {
                Desktop.Window.create('Painel de Governança LGPD', parametros);
            }
        });
    }
</script>
</body>
</html>
