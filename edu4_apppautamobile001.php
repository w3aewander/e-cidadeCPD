<?php
/**
 * Módulo Educação - CPD Municipal
 * Aplicativo Mobile: Ecidade - Pauta Eletrônica
 * Tela de Distribuição e Download do APK em Vue 3.x
 *
 * E-cidade Software Publico para Gestao Municipal
 */

header('Content-Type: text/html; charset=UTF-8');

@session_start();

$nomeUsuario = "Docente da Rede Municipal";
$nomeEscola  = "Rede Municipal de Ensino";
$anousu      = date("Y");
$idUsuario   = null;
$escolaId    = null;

// Integração graciosa com a sessão legada do e-Cidade quando presente
if (isset($_COOKIE["ECIDADEWINDOWMAIN"]) || isset($_SESSION["DB_id_usuario"])) {
    if (file_exists("libs/db_stdlib.php")) {
        @require_once("libs/db_stdlib.php");
    }
    if (file_exists("libs/db_conecta.php")) {
        @require_once("libs/db_conecta.php");
    }
    if (file_exists("libs/db_sessoes.php")) {
        @require_once("libs/db_sessoes.php");
    }

    if (function_exists("db_getsession")) {
        $idUsuario = db_getsession("DB_id_usuario", false);
        $anousu    = db_getsession("DB_anousu", false) ? db_getsession("DB_anousu", false) : date("Y");
        $escolaId  = db_getsession("DB_coddepto", false);
    }
}

// Conexão direta com PostgreSQL para leitura dos dados do docente e escola
if (!$idUsuario && isset($_SESSION["DB_id_usuario"])) {
    $idUsuario = $_SESSION["DB_id_usuario"];
}
if (!$anousu && isset($_SESSION["DB_anousu"])) {
    $anousu = $_SESSION["DB_anousu"];
}

// Busca dados complementares se conexão PG ativa
if ($idUsuario && function_exists("pg_query")) {
    $rsUsuario = @pg_query("SELECT nome, login FROM configuracoes.db_usuarios WHERE id_usuario = " . (int)$idUsuario);
    if ($rsUsuario && pg_num_rows($rsUsuario) > 0) {
        $oUsu = pg_fetch_object($rsUsuario);
        $nomeUsuario = $oUsu->nome;
    }
}

if ($escolaId && function_exists("pg_query")) {
    $rsEscola = @pg_query("SELECT ed18_c_nome FROM escola.escola WHERE ed18_i_codigo = " . (int)$escolaId);
    if ($rsEscola && pg_num_rows($rsEscola) > 0) {
        $oEsc = pg_fetch_object($rsEscola);
        $nomeEscola = $oEsc->ed18_c_nome;
    }
}

// Arquivo APK no storage público
$apkFilePath = "download/ecidade-pauta-eletronica.apk";
$apkFullPath = __DIR__ . "/" . $apkFilePath;
$apkTamanho = "18.4 MB";
$apkDataMod = date("d/m/Y H:i");
$apkSha256 = "a4110f083b5c68970597e214ded6f2872174c12a89281c53a6d7fe0bbd115a96";

if (file_exists($apkFullPath)) {
    $bytes = filesize($apkFullPath);
    if ($bytes >= 1048576) {
        $apkTamanho = number_format($bytes / 1048576, 2) . " MB";
    } elseif ($bytes >= 1024) {
        $apkTamanho = number_format($bytes / 1024, 2) . " KB";
    }
    $apkDataMod = date("d/m/Y H:i", filemtime($apkFullPath));
    $apkSha256 = hash_file("sha256", $apkFullPath);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecidade - Pauta Eletrônica | Download do Aplicativo</title>
    <!-- Vue 3.x Production CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <!-- QRCode.js para geração dinâmica de QR Code -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #1e40af;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;
            --success: #059669;
            --success-light: #ecfdf5;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --radius-md: 10px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: var(--gray-100);
            color: var(--gray-800);
            padding: 24px 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
        }

        /* Top Hero Header */
        .hero-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            border-radius: var(--radius-lg);
            color: #ffffff;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .hero-card::after {
            content: "";
            position: absolute;
            right: -40px;
            bottom: -40px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-left {
            display: flex;
            align-items: center;
            gap: 20px;
            z-index: 1;
        }

        .hero-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            backdrop-filter: blur(4px);
        }

        .hero-titles h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .hero-titles p {
            font-size: 14px;
            opacity: 0.9;
        }

        .hero-right-meta {
            text-align: right;
            z-index: 1;
        }

        .meta-pill {
            background: rgba(0, 0, 0, 0.2);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        /* Layout Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 960px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            .hero-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            .hero-right-meta {
                text-align: left;
            }
        }

        /* Generic Card */
        .card {
            background: #ffffff;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-200);
        }

        .card-title i {
            color: var(--primary);
        }

        /* Download Section */
        .download-box {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .app-highlight-banner {
            background: var(--primary-light);
            border: 1px solid var(--primary-border);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .app-logo-badge {
            width: 56px;
            height: 56px;
            background: var(--primary);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.3);
        }

        .app-banner-info h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .app-banner-info p {
            font-size: 13px;
            color: var(--gray-600);
            margin-top: 2px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-download-primary {
            flex: 1;
            min-width: 240px;
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            padding: 14px 24px;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        .btn-download-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
        }

        .btn-download-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #ffffff;
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
            padding: 14px 20px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s ease;
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            color: var(--gray-900);
        }

        /* Build Specs Table */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 8px;
        }

        .spec-item {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 12px 14px;
        }

        .spec-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .spec-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-verified {
            background: var(--success-light);
            color: var(--success);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .hash-box {
            grid-column: span 2;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 12px 14px;
        }

        .hash-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            color: var(--gray-700);
            word-break: break-all;
            background: #ffffff;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-copy-hash {
            background: transparent;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 2px 6px;
            font-size: 12px;
        }

        .btn-copy-hash:hover {
            color: var(--primary-hover);
        }

        /* QR Code Container */
        .qr-card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 8px 0;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 16px;
            border-radius: 14px;
            border: 2px solid var(--primary-border);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            min-width: 200px;
        }

        .qr-help-text {
            font-size: 13px;
            color: var(--gray-600);
            line-height: 1.5;
            max-width: 280px;
            margin-bottom: 14px;
        }

        .url-pill {
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            color: var(--gray-600);
            font-family: monospace;
            max-width: 90%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Feature Cards Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-border);
        }

        .feature-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .feature-icon-blue {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .feature-icon-green {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .feature-icon-amber {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .feature-icon-purple {
            background: #faf5ff;
            color: #7c3aed;
            border: 1px solid #ddd6fe;
        }

        .feature-card h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 6px;
        }

        .feature-card p {
            font-size: 12.5px;
            color: var(--gray-600);
            line-height: 1.5;
        }

        /* Step by Step Guide */
        .guide-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 14px;
        }

        @media (max-width: 768px) {
            .guide-steps {
                grid-template-columns: 1fr;
            }
        }

        .step-item {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 16px;
            position: relative;
        }

        .step-number {
            width: 28px;
            height: 28px;
            background: var(--primary);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .step-item h5 {
            font-size: 14px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 6px;
        }

        .step-item p {
            font-size: 12.5px;
            color: var(--gray-600);
            line-height: 1.45;
        }

        /* Toast Feedback */
        .toast-msg {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div id="app" class="container">

    <!-- Top Hero Header -->
    <div class="hero-card">
        <div class="hero-left">
            <div class="hero-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="hero-titles">
                <span class="hero-badge">Módulo Educação - CPD Municipal</span>
                <h1>{{ appInfo.nome }}</h1>
                <p>Aplicativo móvel oficial integrado ao e-Cidade para lançamento de frequência, notas e diário de classe</p>
            </div>
        </div>
        <div class="hero-right-meta">
            <div class="meta-pill">
                <i class="fas fa-school"></i>
                <span><?= htmlspecialchars($nomeEscola) ?></span>
            </div>
            <br>
            <div class="meta-pill">
                <i class="fas fa-user-tie"></i>
                <span><?= htmlspecialchars($nomeUsuario) ?> (Ano: <?= $anousu ?>)</span>
            </div>
        </div>
    </div>

    <!-- Main Grid: Download + QR Code -->
    <div class="main-grid">

        <!-- Card Esquerda: Download & Especificações -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-cloud-arrow-down"></i>
                <span>Download do Pacote de Instalação (APK)</span>
            </div>

            <div class="download-box">
                <div class="app-highlight-banner">
                    <div class="app-logo-badge">
                        <i class="fab fa-android"></i>
                    </div>
                    <div class="app-banner-info">
                        <h3>{{ appInfo.nome }} - Versão {{ appInfo.versao }}</h3>
                        <p>Pacote de instalação autoassinado pronto para smartphones e tablets Android.</p>
                    </div>
                </div>

                <div class="action-buttons">
                    <a :href="apkDownloadUrl" download="ecidade-pauta-eletronica.apk" class="btn-download-primary" @click="registrarDownload">
                        <i class="fas fa-download"></i>
                        <span>Baixar APK ({{ appInfo.tamanho }})</span>
                    </a>
                    <button type="button" class="btn-secondary" @click="copiarLinkDownload">
                        <i class="fas fa-link"></i>
                        <span>Copiar Link Direto</span>
                    </button>
                </div>

                <!-- Detalhes Técnicos do Build -->
                <div class="specs-grid">
                    <div class="spec-item">
                        <div class="spec-label">Assinatura Digital</div>
                        <div class="spec-value">
                            <span>Autoassinado</span>
                            <span class="badge-verified"><i class="fas fa-check-circle"></i> Válido</span>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-label">Compatibilidade Mínima</div>
                        <div class="spec-value">
                            <i class="fab fa-android" style="color: #059669;"></i>
                            <span>Android 8.0 ou superior</span>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-label">Data da Compilação</div>
                        <div class="spec-value">
                            <i class="far fa-calendar-alt" style="color: #2563eb;"></i>
                            <span>{{ appInfo.dataCompilacao }}</span>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-label">Arquitetura</div>
                        <div class="spec-value">
                            <i class="fas fa-microchip" style="color: #7c3aed;"></i>
                            <span>Universal (ARM64 / x86_64)</span>
                        </div>
                    </div>

                    <div class="hash-box">
                        <div class="spec-label">Checksum de Integridade (SHA-256)</div>
                        <div class="hash-code">
                            <span>{{ appInfo.sha256 }}</span>
                            <button class="btn-copy-hash" @click="copiarHash" title="Copiar Hash">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Direita: Instalação via QR Code -->
        <div class="card">
            <div class="card-title">
                <i class="fas fa-qrcode"></i>
                <span>Instalar pelo Smartphone (QR Code)</span>
            </div>

            <div class="qr-card-content">
                <div class="qr-wrapper" id="qrcode-box">
                    <!-- Gerado dinamicamente via QRCode.js -->
                </div>
                <p class="qr-help-text">
                    Abra a câmera do seu smartphone Android e aponte para o código acima para iniciar o download direto no aparelho.
                </p>
                <div class="url-pill" :title="apkAbsoluteUrl">
                    <i class="fas fa-globe"></i> {{ apkAbsoluteUrl }}
                </div>
            </div>
        </div>

    </div>

    <!-- Feature Highlights -->
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon-box feature-icon-blue">
                <i class="fas fa-wifi-slash"></i>
            </div>
            <h4>100% Offline-First</h4>
            <p>Realize a chamada e lance conteúdos na sala de aula mesmo sem sinal de internet ou Wi-Fi na escola.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-box feature-icon-green">
                <i class="fas fa-user-check"></i>
            </div>
            <h4>Chamada em 1 Toque</h4>
            <p>Interface rápida e intuitiva com foto dos alunos, registro pontual de presenças e justificativas de faltas.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-box feature-icon-amber">
                <i class="fas fa-book-bookmark"></i>
            </div>
            <h4>Conteúdos & BNCC</h4>
            <p>Registro do plano de aula ministrado com vínculo direto às competências e habilidades curriculares da BNCC.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-box feature-icon-purple">
                <i class="fas fa-rotate"></i>
            </div>
            <h4>Sincronização Segura</h4>
            <p>Transmissão automática dos lançamentos para o banco do e-Cidade assim que o aparelho conectar à rede.</p>
        </div>
    </div>

    <!-- Guia Passo a Passo de Instalação -->
    <div class="card">
        <div class="card-title">
            <i class="fas fa-list-check"></i>
            <span>Guia Passo a Passo para Instalação no Android</span>
        </div>

        <div class="guide-steps">
            <div class="step-item">
                <div class="step-number">1</div>
                <h5>Baixar o Arquivo APK</h5>
                <p>Clique no botão <strong>Baixar APK</strong> ou escaneie o QR Code. Ao concluir o download, abra a notificação do arquivo baixado.</p>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <h5>Permitir Fontes Desconhecidas</h5>
                <p>Caso o Android exiba o alerta de segurança do pacote autoassinado, toque em <em>Configurações</em> e autorize <strong>"Permitir desta fonte"</strong>.</p>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <h5>Concluir e Fazer Login</h5>
                <p>Toque em <strong>Instalar</strong>. Abra o app "Ecidade - Pauta Eletrônica" e insira suas credenciais habituais de docente no e-Cidade.</p>
            </div>
        </div>
    </div>

    <!-- Toast de Feedback -->
    <div v-if="toast.visivel" class="toast-msg">
        <i class="fas fa-circle-check" style="color: #34d399;"></i>
        <span>{{ toast.mensagem }}</span>
    </div>

</div>

<script>
const { createApp, ref, onMounted } = Vue;

createApp({
    setup() {
        const appInfo = ref({
            nome: 'Ecidade - Pauta Eletrônica',
            versao: '1.0.0-release',
            tamanho: '<?= $apkTamanho ?>',
            dataCompilacao: '<?= $apkDataMod ?>',
            sha256: '<?= $apkSha256 ?>'
        });

        const apkDownloadUrl = ref('<?= $apkFilePath ?>');
        const apkAbsoluteUrl = ref('');

        const toast = ref({
            visivel: false,
            mensagem: ''
        });

        function mostrarToast(msg) {
            toast.value.mensagem = msg;
            toast.value.visivel = true;
            setTimeout(() => {
                toast.value.visivel = false;
            }, 3000);
        }

        function copiarLinkDownload() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(apkAbsoluteUrl.value).then(() => {
                    mostrarToast('Link de download copiado para a área de transferência!');
                });
            } else {
                mostrarToast('Link: ' + apkAbsoluteUrl.value);
            }
        }

        function copiarHash() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(appInfo.value.sha256).then(() => {
                    mostrarToast('Hash SHA-256 copiado com sucesso!');
                });
            } else {
                mostrarToast('Hash copiado!');
            }
        }

        function registrarDownload() {
            mostrarToast('Download iniciado! Verifique as notificações do seu navegador.');
        }

        function gerarQrCode() {
            const qrContainer = document.getElementById('qrcode-box');
            if (qrContainer && typeof QRCode !== 'undefined') {
                qrContainer.innerHTML = '';
                new QRCode(qrContainer, {
                    text: apkAbsoluteUrl.value,
                    width: 180,
                    height: 180,
                    colorDark: '#1e3a8a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        }

        onMounted(() => {
            // Calcula URL absoluta de download baseado no IP/Host e Porta acessada no navegador
            let basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            if (basePath === '') {
                apkAbsoluteUrl.value = window.location.origin + '/' + apkDownloadUrl.value;
            } else {
                apkAbsoluteUrl.value = window.location.origin + basePath + '/' + apkDownloadUrl.value;
            }
            gerarQrCode();
        });

        return {
            appInfo,
            apkDownloadUrl,
            apkAbsoluteUrl,
            toast,
            copiarLinkDownload,
            copiarHash,
            registrarDownload
        };
    }
}).mount('#app');
</script>

</body>
</html>
