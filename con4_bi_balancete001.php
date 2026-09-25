<?php
/**
 * Interface e-Cidade para o Painel de BI - Balancete de Receita por Recurso (Apache Superset)
 * e-Cidade CPD-MUNICIPAL
 */
header('Content-Type: text/html; charset=UTF-8');

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");

if (!isset($_SESSION["DB_modulo"]) || empty($_SESSION["DB_modulo"])) {
    $_SESSION["DB_modulo"] = 209;
}
if (!isset($_SESSION["DB_nomemod"]) || empty($_SESSION["DB_nomemod"])) {
    $_SESSION["DB_nomemod"] = "Contabilidade";
}

require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

$anousu = db_getsession("DB_anousu", false) ?: date("Y");
$dataIni = $anousu . "-01-01";
$dataFim = $anousu . "-12-31";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Intelligence &gt; Receita Por Fonte de Recursos</title>
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <script type="text/javascript" src="public/js/bi/superset-embedded-sdk.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background-color: #f7f9fb;
            color: #31546b;
        }
        .bi-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }
        .filter-toolbar {
            height: 48px;
            min-height: 48px;
            background-color: #ffffff;
            border-bottom: 1px solid #d7e0e5;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #31546b;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            z-index: 10;
        }
        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .filter-form label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        .filter-form input, .filter-form select {
            padding: 4px 8px;
            border: 1px solid #b8c7d1;
            border-radius: 4px;
            background-color: #fff;
            color: #31546b;
            font-size: 13px;
        }
        .filter-form input[type="number"] {
            width: 70px;
        }
        .btn-apply {
            padding: 5px 14px;
            border: 0;
            border-radius: 4px;
            background-color: #397da8;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-apply:hover {
            background-color: #2b6285;
        }
        .right-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-theme {
            padding: 5px 12px;
            border: 1px solid #b8c7d1;
            border-radius: 4px;
            background: #fff;
            color: #31546b;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-theme:hover {
            background: #e2e8f0;
        }
        .dashboard-wrapper {
            flex: 1;
            position: relative;
            width: 100%;
            height: calc(100vh - 48px);
            background-color: #f7f9fb;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(247, 249, 251, 0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 14px;
            color: #31546b;
            z-index: 5;
        }
        .spinner {
            width: 36px;
            height: 36px;
            border: 4px solid #d7e0e5;
            border-top: 4px solid #397da8;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #superset-embed-mount {
            width: 100%;
            height: 100%;
        }
        #superset-embed-mount iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }
    </style>
</head>
<body>
<div class="bi-container">
    <div class="filter-toolbar">
        <form id="biFilterForm" class="filter-form" onsubmit="event.preventDefault(); carregarDashboardBI();">
            <label>Exercício:
                <input id="bi_exercicio" type="number" min="2000" max="<?php echo date('Y') + 1; ?>" value="<?php echo $anousu; ?>" required>
            </label>
            <label>De:
                <input id="bi_data_inicio" type="date" value="<?php echo $dataIni; ?>" required>
            </label>
            <label>até:
                <input id="bi_data_fim" type="date" value="<?php echo $dataFim; ?>" required>
            </label>
            <button type="submit" class="btn-apply">Aplicar</button>
        </form>
        <div class="right-controls">
            <label style="display:flex; align-items:center; gap:6px; font-weight:500;">Base:
                <select id="bi_database" onchange="carregarDashboardBI();">
                    <option value="ecidade" selected>ecidade</option>
                    <option value="dbpadrao">dbpadrao</option>
                </select>
            </label>
            <button id="btnThemeToggle" type="button" class="btn-theme" onclick="alternarTema();">Tema escuro</button>
        </div>
    </div>
    <div class="dashboard-wrapper">
        <div id="loadingOverlay" class="loading-overlay">
            <div class="spinner"></div>
            <div id="loadingText">Carregando painel BI Superset...</div>
        </div>
        <div id="superset-embed-mount"></div>
    </div>
</div>

<script type="text/javascript">
var currentEmbeddedDashboard = null;
var currentThemeMode = 'default';

function alternarTema() {
    var btn = document.getElementById('btnThemeToggle');
    currentThemeMode = (currentThemeMode === 'default') ? 'dark' : 'default';
    btn.textContent = (currentThemeMode === 'default') ? 'Tema escuro' : 'Tema claro';
    if (currentEmbeddedDashboard && currentEmbeddedDashboard.setThemeMode) {
        currentEmbeddedDashboard.setThemeMode(currentThemeMode);
    }
}

document.getElementById('bi_exercicio').addEventListener('change', function() {
    var ano = this.value;
    document.getElementById('bi_data_inicio').value = ano + '-01-01';
    document.getElementById('bi_data_fim').value = ano + '-12-31';
});

async function carregarDashboardBI() {
    var loading = document.getElementById('loadingOverlay');
    var loadingText = document.getElementById('loadingText');
    var mountPoint = document.getElementById('superset-embed-mount');
    
    loading.style.display = 'flex';
    loadingText.textContent = 'Autenticando e gerando escopo do painel no Apache Superset...';

    var exercicio = document.getElementById('bi_exercicio').value;
    var dataInicio = document.getElementById('bi_data_inicio').value;
    var dataFim = document.getElementById('bi_data_fim').value;
    var base = document.getElementById('bi_database').value;

    try {
        var response = await fetch('/v4/api/bi/balancete-receita-recurso/guest-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                exercicio: parseInt(exercicio, 10),
                periodoInicial: dataInicio,
                periodoFinal: dataFim,
                base: base
            })
        });

        if (!response.ok) {
            throw new Error('Falha ao obter token de acesso do BI (HTTP ' + response.status + ')');
        }

        var data = await response.json();
        if (!data.token) {
            throw new Error(data.message || 'Token do Superset não retornado');
        }

        mountPoint.innerHTML = '';

        if (window.supersetEmbeddedSdk && window.supersetEmbeddedSdk.embedDashboard) {
            currentEmbeddedDashboard = await window.supersetEmbeddedSdk.embedDashboard({
                id: data.dashboardUuid,
                supersetDomain: data.supersetUrl,
                mountPoint: mountPoint,
                fetchGuestToken: function() { return Promise.resolve(data.token); },
                dashboardUiConfig: {
                    hideTitle: true,
                    hideChartControls: true,
                    hideTab: true
                }
            });

            if (currentEmbeddedDashboard && currentEmbeddedDashboard.setThemeMode) {
                currentEmbeddedDashboard.setThemeMode(currentThemeMode);
            }
        } else {
            var iframe = document.createElement('iframe');
            iframe.src = data.supersetUrl + '/embedded/' + data.dashboardUuid + '?guest_token=' + encodeURIComponent(data.token);
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.border = '0';
            mountPoint.appendChild(iframe);
        }

        loading.style.display = 'none';
    } catch (err) {
        loadingText.innerHTML = '<span style="color:#e53e3e;font-weight:bold;">Painel BI indisponível: ' + err.message + '</span>';
        console.error('Erro ao carregar BI:', err);
    }
}

window.onload = function() {
    carregarDashboardBI();
};
</script>
</body>
</html>
