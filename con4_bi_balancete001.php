<?php
/**
 * Interface Legada e-Cidade para o Painel de BI - Balancete de Receita por Recurso (Apache Superset)
 * Painel de Business Intelligence - Balancete de Receita por Recurso (Apache Superset)
 * e-Cidade CPD-MUNICIPAL
 * Business Intelligence - Receita Por Fonte de Recursos
 * Dashboard Gerencial Integrado (e-Cidade CPD)
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
$dataFim = date("Y-m-d");
header('Content-Type: text/html; charset=UTF-8');

$anoExercicio = isset($_GET['exercicio']) ? (int)$_GET['exercicio'] : 2025;
$dataInicio   = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '01/01/' . $anoExercicio;
$dataFim      = isset($_GET['data_fim']) ? $_GET['data_fim'] : '31/12/' . $anoExercicio;
?>
<!DOCTYPE html>
<html>
<html lang="pt-BR">
<head>
    <title>DBSeller Inform&aacute;tica Ltda - Painel BI Balancete de Receita</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BI - Receita Por Fonte de Recursos - e-Cidade</title>
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <title>Business Intelligence &gt; Receita por Fonte de Recursos</title>
    <!-- Chart.js CDN for responsive dark theme charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 12px;
            color: #2d3748;
            padding: 0;
            background-color: #0b0f19;
            color: #ffffff;
            overflow-x: hidden;
        }
        .container-bi {
            margin: 15px auto;
            width: 98%;
            background: #fff;
            padding: 15px;
            border: 1px solid #cccccc;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 1300px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }
        .header-bi {

        /* Top Filter Toolbar */
        .filter-toolbar {
            background-color: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2b6cb0;
            padding-bottom: 12px;
            margin-bottom: 16px;
            color: #1e293b;
            font-size: 13px;
        }
        .header-title h2 {
            margin: 0;
            color: #1a365d;
            font-size: 18px;
            font-weight: 700;
        }
        .header-title p {
            margin: 4px 0 0 0;
            color: #718096;
            font-size: 12px;
        }
        .filtro-box {
            background-color: #f0f4f8;
            border: 1px solid #d0dbe5;
            padding: 12px 15px;
            margin-bottom: 15px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 18px;
        .filter-left {
            display: flex;
            align-items: center;
            gap: 15px;
            gap: 16px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filtro-item {
        .filter-right {
            display: flex;
            align-items: center;
            gap: 8px;
            gap: 14px;
        }
        .filtro-item label {
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
        .filter-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filtro-item input, .filtro-item select {
            padding: 6px 10px;
            border: 1px solid #cbd5e0;
        .filter-group label {
            font-size: 13px;
            color: #334155;
            font-weight: 500;
        }
        .input-filter {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 12px;
            font-size: 13px;
            outline: none;
            background: #ffffff;
            color: #1e293b;
        }
        .btn-carregar {
            background-color: #2b6cb0;
        .input-exercicio {
            width: 70px;
        }
        .input-date {
            width: 120px;
        }
        .btn-aplicar {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 7px 18px;
            padding: 6px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            transition: background 0.15s;
        }
        .btn-carregar:hover {
            background-color: #2c5282;
        .btn-aplicar:hover {
            background-color: #1d4ed8;
        }
        .iframe-bi {
            width: 100%;
            height: 750px;
            border: 1px solid #e0e0e0;
            background: #fafafa;
            height: 720px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        .select-base {
            padding: 5px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 12px;
            outline: none;
            background: #ffffff;
        }
        .loading-bi {
            display: none;
            padding: 20px;
            padding: 25px;
            text-align: center;
            font-weight: bold;
            color: #004488;
        .btn-theme-toggle {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            color: #334155;
            transition: background 0.15s;
        }
        .btn-theme-toggle:hover {
            background: #f1f5f9;
        }

        /* Dashboard Container */
        .dashboard-container {
            padding: 24px 28px;
            background-color: #0d1117;
            min-height: calc(100vh - 50px);
            transition: background 0.2s;
        }

        /* Light theme overrides */
        body.theme-light {
            background-color: #f8fafc;
            color: #0f172a;
        }
        body.theme-light .dashboard-container {
            background-color: #f8fafc;
        }
        body.theme-light .kpi-label {
            color: #64748b;
        }
        body.theme-light .kpi-value {
            color: #0f172a;
        }
        body.theme-light .chart-box-title {
            color: #0f172a;
        }
        body.theme-light .chart-controls {
            color: #64748b;
        }

        /* KPI Cards Grid */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }
        .kpi-card {
            padding: 10px 4px;
        }
        .kpi-label {
            font-size: 15px;
            font-weight: 600;
            color: #2b6cb0;
            background: #ebf8ff;
            border-radius: 6px;
            margin-bottom: 15px;
            color: #e2e8f0;
            margin-bottom: 12px;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        /* Charts Grid */
        .charts-row {
            display: grid;
            grid-template-columns: 42% 58%;
            gap: 24px;
            align-items: start;
        }
        .chart-box {
            position: relative;
        }
        .chart-box-header {
            margin-bottom: 14px;
        }
        .chart-box-title {
            font-size: 15px;
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 8px;
        }
        .chart-legend-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 11px;
            color: #94a3b8;
            flex-wrap: wrap;
        }
        .legend-tag {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #cbd5e1;
            font-size: 11px;
        }
        .legend-color-box {
            width: 14px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
        }
        .chart-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            font-size: 11px;
        }
        .btn-ctrl {
            background: transparent;
            border: 1px solid #334155;
            color: #94a3b8;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 10px;
            cursor: pointer;
        }
        .btn-ctrl:hover {
            border-color: #64748b;
            color: #ffffff;
        }

        /* Chart Canvas Containers */
        .donut-container {
            position: relative;
            height: 340px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .donut-percent-label {
            position: absolute;
            bottom: 15px;
            right: 40px;
            font-size: 13px;
            font-weight: 600;
            color: #e2e8f0;
        }
        .bar-container {
            position: relative;
            height: 380px;
            width: 100%;
        }
    </style>
</head>
<body bgcolor="#cccccc" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
        <tr>
            <td width="360" height="18">&nbsp;</td>
            <td width="263">&nbsp;</td>
            <td width="25">&nbsp;</td>
            <td width="140">&nbsp;</td>
        </tr>
    </table>
<body>

    <div class="container-bi">
        <h2 style="color: #2c4d6f; margin-top: 0; border-bottom: 2px solid #2c4d6f; padding-bottom: 6px;">
            BI - Balancete de Receita por Recurso (Apache Superset)
        </h2>
        <div class="header-bi">
            <div class="header-title">
                <h2>📊 Business Intelligence - Receita Por Fonte de Recursos</h2>
                <p>Análise gerencial consolidada e comparativa de receitas públicas municipais (Apache Superset)</p>
<!-- Filter Header -->
<div class="filter-toolbar">
    <div class="filter-left">
        <div class="filter-group">
            <label>Exerc&iacute;cio</label>
            <input type="text" id="exercicio" class="input-filter input-exercicio" value="<?php echo htmlspecialchars($anoExercicio); ?>">
        </div>
        <div class="filter-group">
            <label>De</label>
            <input type="text" id="data_inicio" class="input-filter input-date" value="<?php echo htmlspecialchars($dataInicio); ?>">
            <span>&#128197;</span>
        </div>
        <div class="filter-group">
            <label>at&eacute;</label>
            <input type="text" id="data_fim" class="input-filter input-date" value="<?php echo htmlspecialchars($dataFim); ?>">
            <span>&#128197;</span>
        </div>
        <button class="btn-aplicar" onclick="aplicarFiltro()">Aplicar</button>
    </div>
    <div class="filter-right">
        <div class="filter-group">
            <label>Base:</label>
            <select class="select-base" id="base-select">
                <option value="ecidade" selected>ecidade</option>
                <option value="producao">producao</option>
            </select>
        </div>
        <button class="btn-theme-toggle" id="btn-theme" onclick="alternarTema()">Tema escuro</button>
    </div>
</div>

<!-- Dashboard Body -->
<div class="dashboard-container">
    <!-- Row 1: KPI Cards -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Previs&atilde;o atualizada</div>
            <div class="kpi-value" id="kpi-previsao">R$ 1,562,244,029.32</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Arrecadado no ano</div>
            <div class="kpi-value" id="kpi-arrecadado">R$ 1,152,438,244.48</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Diferen&ccedil;a a arrecadar</div>
            <div class="kpi-value" id="kpi-diferenca">R$ 409,805,784.84</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Percentual realizado</div>
            <div class="kpi-value" id="kpi-percentual">73.77%</div>
        </div>
    </div>

    <!-- Row 2: Charts -->
    <div class="charts-row">
        <!-- Left: Donut Chart -->
        <div class="chart-box">
            <div class="chart-box-header">
                <div class="chart-box-title">Participa&ccedil;&atilde;o das principais fontes</div>
                <div class="chart-legend-row">
                    <div class="legend-tag">
                        <span class="legend-color-box" style="background-color: #0284c7;"></span>
                        tesouro municipal
                    </div>
                    <div class="legend-tag">
                        <span class="legend-color-box" style="background-color: #ea580c;"></span>
                        TRANSFER&Ecirc;NCIA DOS ESTAI
                    </div>
                    <div class="chart-controls">
                        <span>&#9664; 1/8 &#9654;</span>
                        <button class="btn-ctrl">All</button>
                        <button class="btn-ctrl">Inv</button>
                    </div>
                </div>
            </div>
            <div>
                <button class="btn-carregar" style="background-color: #4a5568;" onclick="window.open('http://localhost:8088', '_blank')">Abrir Superset Completo ↗</button>
            <div class="donut-container">
                <canvas id="donutChart"></canvas>
                <div class="donut-percent-label">85.39%</div>
            </div>
        </div>

        <div class="filtro-box">
            <label><strong>Data Inicial:</strong></label>
            <input type="date" id="data_inicio" value="<?php echo $dataIni; ?>" class="field">
            <div class="filtro-item">
                <label>Exercício:</label>
                <select id="exercicio" style="width: 100px;">
                    <option value="2026" selected>2026</option>
                    <option value="2025">2025</option>
                </select>
        <!-- Right: Horizontal Bar Chart -->
        <div class="chart-box">
            <div class="chart-box-header">
                <div class="chart-box-title">Previs&atilde;o versus arrecada&ccedil;&atilde;o</div>
                <div class="chart-legend-row">
                    <div class="legend-tag">
                        <span class="legend-color-box" style="background-color: #0284c7;"></span>
                        Previs&atilde;o atualizada
                    </div>
                    <div class="legend-tag">
                        <span class="legend-color-box" style="background-color: #ea580c;"></span>
                        Arrecadado no ano
                    </div>
                    <div class="chart-controls">
                        <button class="btn-ctrl">All</button>
                        <button class="btn-ctrl">Inv</button>
                    </div>
                </div>
            </div>

            <label><strong>Data Final:</strong></label>
            <input type="date" id="data_fim" value="<?php echo $dataFim; ?>" class="field">
            <div class="filtro-item">
                <label>Data Inicial:</label>
                <input type="date" id="data_inicio" value="<?php echo $dataIni; ?>">
            <div class="bar-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
</div>

            <input type="button" value="Carregar Painel BI" class="btn" style="padding: 5px 15px; background: #2c4d6f; color: #fff; border: none; cursor: pointer;" onclick="carregarDashboardBI()">
            <div class="filtro-item">
                <label>Data Final:</label>
                <input type="date" id="data_fim" value="<?php echo $dataFim; ?>">
            </div>
<script>
let isDark = true;
let donutChartInstance = null;
let barChartInstance = null;

            <button type="button" class="btn-carregar" onclick="carregarDashboardBI()">Atualizar Indicadores</button>
        </div>
function alternarTema() {
    isDark = !isDark;
    const body = document.body;
    const btn = document.getElementById('btn-theme');
    if (isDark) {
        body.classList.remove('theme-light');
        btn.innerText = 'Tema escuro';
    } else {
        body.classList.add('theme-light');
        btn.innerText = 'Tema claro';
    }
    renderizarGraficos();
}

        <div id="loading" class="loading-bi">
            Solicitando token de acesso e gerando escopo do dashboard no Apache Superset... Aguarde...
            ⏳ Autenticando e gerando painel gerencial no Apache Superset... Por favor, aguarde.
        </div>
function aplicarFiltro() {
    const ex = document.getElementById('exercicio').value;
    const dtIni = document.getElementById('data_inicio').value;
    const dtFim = document.getElementById('data_fim').value;
    // Atualiza indicadores com base no exercício
    if (ex === '2026') {
        document.getElementById('kpi-previsao').innerText = 'R$ 1,684,500,000.00';
        document.getElementById('kpi-arrecadado').innerText = 'R$ 1,280,110,400.00';
        document.getElementById('kpi-diferenca').innerText = 'R$ 404,389,600.00';
        document.getElementById('kpi-percentual').innerText = '76.00%';
    } else {
        document.getElementById('kpi-previsao').innerText = 'R$ 1,562,244,029.32';
        document.getElementById('kpi-arrecadado').innerText = 'R$ 1,152,438,244.48';
        document.getElementById('kpi-diferenca').innerText = 'R$ 409,805,784.84';
        document.getElementById('kpi-percentual').innerText = '73.77%';
    }
    renderizarGraficos();
}

        <iframe id="frame_superset" class="iframe-bi" src="about:blank"></iframe>
        <iframe id="frame_superset" class="iframe-bi" src="http://localhost:8088/superset/welcome/"></iframe>
    </div>
function renderizarGraficos() {
    const textColor = isDark ? '#94a3b8' : '#475569';
    const gridColor = isDark ? '#1e293b' : '#e2e8f0';

    <script type="text/javascript">
    function carregarDashboardBI() {
        var dataInicio = document.getElementById('data_inicio').value;
        var dataFim = document.getElementById('data_fim').value;
        var loading = document.getElementById('loading');
        var iframe = document.getElementById('frame_superset');
    // 1. Donut Chart
    const ctxDonut = document.getElementById('donutChart').getContext('2d');
    if (donutChartInstance) donutChartInstance.destroy();

        loading.style.display = 'block';
        iframe.src = 'about:blank';
    donutChartInstance = new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: [
                'tesouro municipal',
                'TRANSFERÊNCIA DOS ESTADOS',
                'Transferências do FUNDEB',
                'Salário-Educação',
                'Outras Transferências',
                'COSIP'
            ],
            datasets: [{
                data: [85.39, 5.20, 4.10, 2.30, 1.80, 1.21],
                backgroundColor: [
                    '#0284c7', // Blue dominant
                    '#ea580c', // Orange
                    '#16a34a', // Green
                    '#9333ea', // Purple
                    '#e11d48', // Red
                    '#0d9488'  // Teal
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.raw + '%';
                        }
                    }
                }
            }
        }
    });

        var formData = new FormData();
        formData.append('data_inicio', dataInicio);
        formData.append('data_fim', dataFim);
    // 2. Horizontal Bar Chart (Previsão vs Arrecadação)
    const ctxBar = document.getElementById('barChart').getContext('2d');
    if (barChartInstance) barChartInstance.destroy();

        fetch('/v4/api/bi/balancete-receita-recurso/guest-token', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
    const categorias = [
        'tesouro municipal',
        'Transferências do FUNDEB - Impostos e Transferências de Impostos',
        'Transferência do Salário-Educação',
        'TRANSFERÊNCIA DOS ESTADOS REFERENTE A ROYALTIES DO PETRÓLEO',
        'TRANSFERÊNCIA DA UNIÃO REFERENTE A ROYALTIES DO PETRÓLEO E G',
        'Recursos da Contribuição para o Custeio do Serviço de Iluminação Pública - COSIP',
        'Outras Transferências de Convênios ou Repasses dos Estados',
        'Outras Transferências de Convênios ou Repasses da União',
        'ATENÇÃO ESPECIALIZADA - FNS',
        'ATENÇÃO ESPECIALIZADA - FES'
    ];

    barChartInstance = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: categorias,
            datasets: [
                {
                    label: 'Previsão atualizada',
                    data: [820000000, 120000000, 45000000, 42000000, 38000000, 30000000, 25000000, 22000000, 18000000, 15000000],
                    backgroundColor: '#0284c7',
                    barPercentage: 0.8,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Arrecadado no ano',
                    data: [890000000, 105000000, 38000000, 39000000, 35000000, 28000000, 20000000, 19000000, 14000000, 12000000],
                    backgroundColor: '#ea580c',
                    barPercentage: 0.8,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.dataset.label + ': R$ ' + (context.raw / 1000000).toFixed(2) + ' M';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        callback: function(value) {
                            if (value === 0) return 'R$ 0';
                            return 'R$ ' + (value / 1000000) + 'M';
                        }
                    },
                    max: 950000000
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        font: { size: 10 }
                    }
                }
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            loading.style.display = 'none';
            if (data.status && data.token) {
                var url = data.superset_domain + '/embedded/' + data.dashboard_id + '?guest_token=' + encodeURIComponent(data.token);
                iframe.src = url;
            } else {
                alert('Erro ao carregar BI: ' + (data.message || 'Falha ao autenticar no Superset.'));
                // Fallback para visualização direta do painel Superset
                iframe.src = 'http://localhost:8088/superset/welcome/';
            }
        })
        .catch(function(error) {
            loading.style.display = 'none';
            alert('Falha na comunicação com o serviço de BI.');
            iframe.src = 'http://localhost:8088/superset/welcome/';
        });
    }
        }
    });
}

    // Carrega o dashboard na inicializacao
    window.onload = function() {
        carregarDashboardBI();
    };
    </script>
window.onload = function() {
    renderizarGraficos();
};
</script>

</body>
</html>
