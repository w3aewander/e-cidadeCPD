<?php
/**
 * Módulo Manuais da Educação - e-Cidade CPD
 * Interface Nativa para Gestão e Consulta de Manuais Escolares
 * Interface Desenvolvida em Vue 3 para Gestão e Consulta de Manuais Escolares
 */
header('Content-Type: text/html; charset=UTF-8');

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");

if (!isset($_SESSION["DB_modulo"]) || empty($_SESSION["DB_modulo"])) {
    $_SESSION["DB_modulo"] = 1100747;
}
if (!isset($_SESSION["DB_nomemod"]) || empty($_SESSION["DB_nomemod"])) {
    $_SESSION["DB_nomemod"] = "Educação";
}

require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuais da Educação - e-Cidade</title>
    <title>Manuais da Educa&ccedil;&atilde;o - e-Cidade</title>
    <!-- Vue 3 CDN / Standalone build -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background-color: #f4f7f9;
            background-color: #f1f5f9;
            margin: 0;
            padding: 16px;
            color: #333333;
            color: #334155;
        }
        .main-container {
            max-width: 1200px;
            max-width: 1300px;
            margin: 0 auto;
        }
        /* Header Card */

        /* Top Header Card */
        .card-header-manuais {
            background: #ffffff;
            border: 1px solid #dce4ec;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 24px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 16px;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .icon-box {
            background: #e1effe;
            color: #1a56db;
        .icon-box-blue {
            width: 48px;
            height: 48px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-size: 22px;
            color: #1d4ed8;
        }
        .header-texts h2 {
        .header-titles h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #111827;
            color: #1e293b;
        }
        .header-texts p {
            margin: 4px 0 0 0;
        .header-titles p {
            margin: 3px 0 0 0;
            font-size: 13px;
            color: #6b7280;
            color: #64748b;
        }
        .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            gap: 10px;
        }
        .btn-novo-manual {
            background-color: #2563eb;
            background-color: #1d4ed8;
            color: #ffffff;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: background-color 0.2s;
            transition: background 0.15s;
        }
        .btn-novo-manual:hover {
            background-color: #1d4ed8;
            background-color: #1e40af;
        }
        .btn-refresh {
            background-color: #ea580c;
            color: #ffffff;
            border: none;
            background-color: #ffffff;
            border: 1px solid #ea580c;
            color: #ea580c;
            width: 38px;
            height: 38px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
            transition: all 0.15s;
        }
        .btn-refresh:hover {
            background-color: #c2410c;
            background-color: #fff7ed;
        }
        /* Search Bar */
        .search-container {

        /* Filter Section */
        .card-filter {
            background: #ffffff;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 8px 8px 0 0;
            padding: 14px 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border-bottom: 1px solid #e2e8f0;
        }
        .search-input-wrapper {
        .search-wrapper {
            position: relative;
            width: 320px;
            width: 340px;
        }
        .search-input {
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }
        .input-search {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border: 1px solid #d1d5db;
            padding: 8px 12px 8px 32px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            color: #374151;
            outline: none;
            transition: border-color 0.15s;
        }
        .search-input:focus {
        .input-search:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .search-icon-text {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #9ca3af;
        }
        .search-counter {
        .count-text {
            font-size: 13px;
            color: #6b7280;
            color: #64748b;
        }
        .search-counter strong {
            color: #111827;
        }
        /* Table Card */
        .table-card {

        /* Table Section */
        .table-container {
            background: #ffffff;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .table-manuais {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .table-manuais thead th {
            background-color: #3b6998;
        .table-manuais th {
            background-color: #334e68;
            color: #ffffff;
            padding: 12px 16px;
            font-size: 13px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 12px;
            text-align: left;
            cursor: pointer;
            user-select: none;
        }
        .table-manuais tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.15s;
        .table-manuais th:hover {
            background-color: #243b53;
        }
        .table-manuais tbody tr:hover {
            background-color: #f9fafb;
        .table-manuais th.col-actions {
            text-align: center;
            width: 140px;
            cursor: default;
        }
        .table-manuais td {
            padding: 14px 16px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table-manuais tr:last-child td {
            border-bottom: none;
        }
        .table-manuais tr:hover {
            background-color: #f8fafc;
        }

        /* Document details */
        .doc-cell {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .doc-icon-text {
        .doc-pdf-icon {
            color: #dc2626;
            font-size: 26px;
            line-height: 1;
            margin-top: 2px;
        }
        .doc-info .doc-title {
        .doc-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .doc-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 3px;
            display: block;
            color: #1e293b;
            font-size: 13px;
        }
        .doc-info .doc-desc {
        .doc-desc {
            font-size: 12px;
            color: #6b7280;
            display: block;
            margin-bottom: 3px;
            line-height: 1.4;
            color: #64748b;
        }
        .doc-info .doc-filename {
        .doc-filename {
            font-size: 11px;
            color: #9ca3af;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 2px;
        }

        .badge-tamanho {
            background-color: #06b6d4;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            font-weight: 500;
        }
        .data-cell, .enviado-cell {
            color: #4b5563;
            font-size: 12px;
            color: #475569;
        }
        .enviado-cell {
            font-weight: 600;
        }
        /* Action buttons */

        /* Action Buttons */
        .actions-cell {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-action-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            font-size: 13px;
            transition: all 0.15s;
            background-color: #38bdf8;
            color: #0f172a;
        }
        .btn-action-circle.btn-view {
            background-color: #38bdf8;
            color: #0369a1;
        }
        .btn-action-circle.btn-view:hover {
            background-color: #0284c7;
            color: #ffffff;
            transition: opacity 0.2s;
        }
        .btn-action-circle:hover {
            opacity: 0.85;
        .btn-action-circle.btn-download {
            background-color: #38bdf8;
            color: #0369a1;
        }
        .btn-view {
            background-color: #3b82f6;
        }
        .btn-download {
        .btn-action-circle.btn-download:hover {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-delete {
            background-color: #ef4444;
        .btn-action-circle.btn-delete {
            background-color: #f87171;
            color: #991b1b;
        }
        /* Modal Upload */
        .btn-action-circle.btn-delete:hover {
            background-color: #dc2626;
            color: #ffffff;
        }

        /* Modal Backdrop & Content */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .modal-content {
        .modal-container {
            background: #ffffff;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 580px;
            max-width: 95%;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            background: #2563eb;
            color: #ffffff;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .modal-header h3 {
            margin: 0;
            color: #111827;
            font-size: 16px;
            font-weight: 600;
        }
        .modal-close-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        .modal-body {
            padding: 20px;
        }
        .form-group-modal {
            margin-bottom: 14px;
            margin-bottom: 16px;
        }
        .form-group-modal label {
            display: block;
            font-size: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-input-modal {
        .form-input-modal, .form-textarea-modal {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.15s;
        }
        .modal-actions {
        .form-input-modal:focus, .form-textarea-modal:focus {
            border-color: #2563eb;
        }
        .form-textarea-modal {
            resize: vertical;
            height: 75px;
        }

        /* Upload Dropzone */
        .dropzone-box {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .dropzone-box:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .dropzone-icon {
            font-size: 32px;
            color: #3b82f6;
            margin-bottom: 6px;
        }
        .dropzone-text {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }
        .dropzone-subtext {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Modal Footer */
        .modal-footer {
            padding: 14px 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-cancel {
            background: #64748b;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-cancel:hover {
            background: #475569;
        }
        .btn-submit {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-submit:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Header Card -->
<div id="app" class="main-container">
    <!-- Header -->
    <div class="card-header-manuais">
        <div class="header-left">
            <div class="icon-box">
                📖
            <div class="icon-box-blue">&#128196;</div>
            <div class="header-titles">
                <h1>Manuais da Educa&ccedil;&atilde;o</h1>
                <p>Consulte e baixe os manuais, guias e documentos oficiais da Educa&ccedil;&atilde;o.</p>
            </div>
            <div class="header-texts">
                <h2>Manuais da Educação</h2>
                <p>Consulte e baixe os manuais, guias e documentos oficiais da Educação.</p>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-novo-manual" onclick="abrirModalUpload()">
            <button class="btn-novo-manual" @click="abrirModalUpload">
                <span>+</span> Novo Manual
            </button>
            <button class="btn-refresh" onclick="window.location.reload()" title="Atualizar">
                🔄
            <button class="btn-refresh" title="Recarregar lista" @click="recarregarLista">
                <span>&#8635;</span>
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <div class="search-input-wrapper">
            <span class="search-icon-text">🔍</span>
            <input type="text" id="filtro-busca" class="search-input" placeholder="Pesquisar por título ou descrição..." onkeyup="filtrarTabela()">
    <!-- Filtro e busca -->
    <div class="card-filter">
        <div class="search-wrapper">
            <span class="search-icon">&#128269;</span>
            <input 
                type="text" 
                class="input-search" 
                v-model="termoBusca" 
                placeholder="Pesquisar por t&iacute;tulo ou descri&ccedil;&atilde;o..."
            >
        </div>
        <div class="search-counter" id="contador-manuais">
            Exibindo <strong id="qtd-exibida">4</strong> de <strong id="qtd-total">4</strong> manuais
        <div class="count-text">
            Exibindo {{ manuaisFiltrados.length }} de {{ listaManuais.length }} manuais
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <table class="table-manuais" id="tabela-manuais">
    <!-- Tabela -->
    <div class="table-container">
        <table class="table-manuais">
            <thead>
                <tr>
                    <th style="width: 50%;">Documento</th>
                    <th style="width: 12%;">Tamanho</th>
                    <th style="width: 15%;">Data de Envio</th>
                    <th style="width: 13%;">Enviado por</th>
                    <th style="width: 10%;">Ações</th>
                    <th @click="ordenarPor('titulo')">Documento &uarr;&darr;</th>
                    <th @click="ordenarPor('tamanho')" width="130">Tamanho &uarr;&darr;</th>
                    <th @click="ordenarPor('data')" width="160">Data de Envio &uarr;&darr;</th>
                    <th @click="ordenarPor('enviadoPor')" width="180">Enviado por &uarr;&darr;</th>
                    <th class="col-actions">A&ccedil;&otilde;es</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <tr v-for="item in manuaisFiltrados" :key="item.id">
                    <td>
                        <div class="doc-cell">
                            <span class="doc-icon-text" style="color: #dc2626;">📄</span>
                            <div class="doc-pdf-icon">&#128196;</div>
                            <div class="doc-info">
                                <span class="doc-title">Manual para capacitar professores em diário de classe</span>
                                <span class="doc-desc">Mostra os procedimentos para lançamentos de notas, faltas e outros em diário de classe</span>
                                <span class="doc-filename">CAPACITAÇÃO-PROFESSORES.pdf</span>
                                <span class="doc-title">{{ item.titulo }}</span>
                                <span class="doc-desc">{{ item.descricao }}</span>
                                <span class="doc-filename">&#128206; {{ item.arquivo }}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">537.46 KB</span></td>
                    <td class="data-cell">23/09/2026 09:50</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('CAPACITAÇÃO-PROFESSORES.pdf')">👁</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('CAPACITAÇÃO-PROFESSORES.pdf')">⬇</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑</button>
                        </div>
                        <span class="badge-tamanho">{{ item.tamanho }}</span>
                    </td>
                </tr>

                <tr>
                    <td class="data-cell">{{ item.dataEnvio }}</td>
                    <td class="enviado-cell">{{ item.enviadoPor }}</td>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-icon-text" style="color: #dc2626;">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual módulo educação/secretaria</span>
                                <span class="doc-desc">Abarcará os principais cadastros, e parâmetros para o funcionamento da área de educação módulo secretaria</span>
                                <span class="doc-filename">Manual_Educacao_Secretaria.pdf</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">10.38 MB</span></td>
                    <td class="data-cell">23/09/2026 09:47</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('Manual_Educacao_Secretaria.pdf')">👁</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('Manual_Educacao_Secretaria.pdf')">⬇</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑</button>
                            <button class="btn-action-circle btn-view" title="Visualizar" @click="visualizarManual(item)">
                                &#128065;
                            </button>
                            <button class="btn-action-circle btn-download" title="Download" @click="downloadManual(item)">
                                &#11015;
                            </button>
                            <button class="btn-action-circle btn-delete" title="Excluir" @click="excluirManual(item)">
                                &#128465;
                            </button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-icon-text" style="color: #dc2626;">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual Matriculando Alunos</span>
                                <span class="doc-desc">Manual com o passo a passo completo para efetivação de matrículas na rede municipal</span>
                                <span class="doc-filename">Matriculando_alunos.pdf</span>
                            </div>
                        </div>
                <tr v-if="manuaisFiltrados.length === 0">
                    <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">
                        Nenhum manual encontrado para o termo pesquisado.
                    </td>
                    <td><span class="badge-tamanho">882.57 KB</span></td>
                    <td class="data-cell">22/09/2026 22:14</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('Matriculando_alunos.pdf')">👁</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('Matriculando_alunos.pdf')">⬇</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑</button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-icon-text" style="color: #dc2626;">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual Transferindo Alunos e-cidade</span>
                                <span class="doc-desc">Procedimentos de transferência interna e externa de alunos no sistema</span>
                                <span class="doc-filename">manual_transferindo_alunos_ecidade.pdf</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">795.82 KB</span></td>
                    <td class="data-cell">22/09/2026 22:14</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('manual_transferindo_alunos_ecidade.pdf')">👁</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('manual_transferindo_alunos_ecidade.pdf')">⬇</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal-overlay" id="modal-upload">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Novo Manual da Educação</h3>
            <span style="cursor: pointer; font-weight: bold; font-size: 16px;" onclick="fecharModalUpload()">✕</span>
    <!-- Modal Enviar Novo Manual (PDF) -->
    <div class="modal-overlay" v-if="modalAberto" @click.self="fecharModalUpload">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Enviar Novo Manual (PDF)</h3>
                <button class="modal-close-btn" @click="fecharModalUpload">&times;</button>
            </div>
            <form @submit.prevent="salvarManual">
                <div class="modal-body">
                    <div class="form-group-modal">
                        <label>T&iacute;tulo do Documento *</label>
                        <input 
                            type="text" 
                            class="form-input-modal" 
                            v-model="novoManual.titulo" 
                            required 
                            placeholder="Ex: Manual de Matr&iacute;cula 2026"
                        >
                    </div>

                    <div class="form-group-modal">
                        <label>Descri&ccedil;&atilde;o / Observa&ccedil;&otilde;es (Opcional)</label>
                        <textarea 
                            class="form-textarea-modal" 
                            v-model="novoManual.descricao" 
                            placeholder="Breve resumo ou instru&ccedil;&otilde;es sobre o manual..."
                        ></textarea>
                    </div>

                    <div class="form-group-modal">
                        <label>Arquivo PDF *</label>
                        <input 
                            type="file" 
                            id="input-file-pdf" 
                            ref="fileInput" 
                            accept=".pdf" 
                            style="display: none;" 
                            @change="tratarArquivoSelecionado"
                        >
                        <div class="dropzone-box" @click="$refs.fileInput.click()">
                            <div class="dropzone-icon">&#9729;</div>
                            <div class="dropzone-text">
                                {{ novoManual.nomeArquivo || 'Clique para selecionar ou arraste o arquivo PDF aqui' }}
                            </div>
                            <div class="dropzone-subtext">
                                Apenas arquivos .pdf (Tamanho m&aacute;ximo: 200MB)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" @click="fecharModalUpload">
                        &times; Cancelar
                    </button>
                    <button type="submit" class="btn-submit">
                        &#10003; Enviar Manual
                    </button>
                </div>
            </form>
        </div>
        <form onsubmit="salvarNovoManual(event)">
            <div class="form-group-modal">
                <label>Título do Manual *</label>
                <input type="text" id="modal-titulo" class="form-input-modal" required placeholder="Ex: Manual de Fechamento de Diário">
            </div>
            <div class="form-group-modal">
                <label>Descrição / Instruções</label>
                <input type="text" id="modal-desc" class="form-input-modal" placeholder="Resumo do manual">
            </div>
            <div class="form-group-modal">
                <label>Arquivo (PDF) *</label>
                <input type="file" id="modal-arquivo" class="form-input-modal" accept=".pdf,.doc,.docx" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-novo-manual" style="background: #9ca3af;" onclick="fecharModalUpload()">Cancelar</button>
                <button type="submit" class="btn-novo-manual">Salvar e Publicar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalUpload() {
    document.getElementById('modal-upload').style.display = 'flex';
}
function fecharModalUpload() {
    document.getElementById('modal-upload').style.display = 'none';
}
function filtrarTabela() {
    var termo = document.getElementById('filtro-busca').value.toLowerCase();
    var linhas = document.querySelectorAll('#tabela-manuais tbody tr');
    var visiveis = 0;
    linhas.forEach(function(linha) {
        var texto = linha.innerText.toLowerCase();
        if (texto.indexOf(termo) > -1) {
            linha.style.display = '';
            visiveis++;
        } else {
            linha.style.display = 'none';
const { createApp, ref, computed } = Vue;

createApp({
    setup() {
        const modalAberto = ref(false);
        const termoBusca = ref('');
        const colunaOrdem = ref('titulo');
        const ordemAsc = ref(true);

        const novoManual = ref({
            titulo: '',
            descricao: '',
            nomeArquivo: '',
            arquivo: null
        });

        const listaManuais = ref([
            {
                id: 1,
                titulo: 'Manual para capacitar professores em diário de classe',
                descricao: 'Mostra os procedimentos para lançamentos de notas, faltas e outros em diário de classe',
                arquivo: 'CAPACITAÇÃO-PROFESSORES.pdf',
                tamanho: '537.46 KB',
                dataEnvio: '23/09/2026 09:50',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 2,
                titulo: 'Manual módulo educação/secretaria',
                descricao: 'Abarcará os principais cadastros, e parâmetros para o funcionamento da área de educação módulo secretaria',
                arquivo: 'Manual_Educacao_Secretaria.pdf',
                tamanho: '10.38 MB',
                dataEnvio: '23/09/2026 09:47',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 3,
                titulo: 'Manual Matriculando Alunos',
                descricao: 'Manual com o passo a passo completo para efetivação de matrículas na rede municipal',
                arquivo: 'Matriculando_alunos.pdf',
                tamanho: '882.57 KB',
                dataEnvio: '22/09/2026 22:14',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 4,
                titulo: 'Manual Transferindo Alunos e-cidade',
                descricao: 'Procedimentos de transferência interna e externa de alunos no sistema',
                arquivo: 'manual_transferindo_alunos_ecidade.pdf',
                tamanho: '795.82 KB',
                dataEnvio: '22/09/2026 22:14',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 5,
                titulo: 'Guia Rápido de Lançamento de Frequência Escolar',
                descricao: 'Instruções para docentes quanto ao fechamento quinzenal de presenças',
                arquivo: 'Guia_Frequencia_Escolar.pdf',
                tamanho: '412.10 KB',
                dataEnvio: '21/09/2026 15:30',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 6,
                titulo: 'Manual de Cadastro de Turmas e Turnos',
                descricao: 'Parametrização de salas, horários e matriz curricular escolar',
                arquivo: 'Cadastro_Turmas_Turnos.pdf',
                tamanho: '1.24 MB',
                dataEnvio: '20/09/2026 11:15',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 7,
                titulo: 'Instrução Normativa de Transporte Escolar',
                descricao: 'Diretrizes operacionais para itinerários e condutores credenciados',
                arquivo: 'Instrucao_Transporte_Escolar.pdf',
                tamanho: '620.05 KB',
                dataEnvio: '19/09/2026 16:40',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 8,
                titulo: 'Manual de Fechamento de Notas e Médias Anuais',
                descricao: 'Cálculo de médias ponderadas, recuperação e consolidação final',
                arquivo: 'Fechamento_Notas_Medias.pdf',
                tamanho: '950.18 KB',
                dataEnvio: '18/09/2026 10:20',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 9,
                titulo: 'Guia de Expedição de Histórico Escolar e Certificados',
                descricao: 'Emissão padronizada de documentos de conclusão de ensino fundamental',
                arquivo: 'Expedicao_Historico_Escolar.pdf',
                tamanho: '780.40 KB',
                dataEnvio: '17/09/2026 14:05',
                enviadoPor: 'PREFEITURA CPD'
            },
            {
                id: 10,
                titulo: 'Manual do Censo Escolar MEC/INEP - Integração e-Cidade',
                descricao: 'Exportação e consistência de dados para o sistema Educacenso',
                arquivo: 'Manual_Integracao_Censo_Escolar.pdf',
                tamanho: '2.15 MB',
                dataEnvio: '15/09/2026 09:00',
                enviadoPor: 'PREFEITURA CPD'
            }
        ]);

        const manuaisFiltrados = computed(() => {
            const termo = termoBusca.value.toLowerCase().trim();
            let lista = listaManuais.value.filter(item => {
                return item.titulo.toLowerCase().includes(termo) ||
                       item.descricao.toLowerCase().includes(termo) ||
                       item.arquivo.toLowerCase().includes(termo);
            });

            lista.sort((a, b) => {
                let valorA = a[colunaOrdem.value] || '';
                let valorB = b[colunaOrdem.value] || '';
                if (typeof valorA === 'string') valorA = valorA.toLowerCase();
                if (typeof valorB === 'string') valorB = valorB.toLowerCase();
                if (valorA < valorB) return ordemAsc.value ? -1 : 1;
                if (valorA > valorB) return ordemAsc.value ? 1 : -1;
                return 0;
            });

            return lista;
        });

        function abrirModalUpload() {
            novoManual.value = { titulo: '', descricao: '', nomeArquivo: '', arquivo: null };
            modalAberto.value = true;
        }
    });
    document.getElementById('qtd-exibida').innerText = visiveis;
}
function visualizarDocumento(nome) {
    alert('Visualizando PDF: ' + nome);
}
function downloadDocumento(nome) {
    alert('Baixando documento: ' + nome);
}
function excluirItem(btn) {
    if (confirm('Deseja realmente excluir este manual?')) {
        btn.closest('tr').remove();
        filtrarTabela();

        function fecharModalUpload() {
            modalAberto.value = false;
        }

        function tratarArquivoSelecionado(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                novoManual.value.arquivo = files[0];
                novoManual.value.nomeArquivo = files[0].name;
            }
        }

        function salvarManual() {
            if (!novoManual.value.titulo) {
                alert('Informe o título do manual.');
                return;
            }
            const dataHoje = new Date();
            const dataStr = ('0' + dataHoje.getDate()).slice(-2) + '/' +
                            ('0' + (dataHoje.getMonth() + 1)).slice(-2) + '/' +
                            dataHoje.getFullYear() + ' ' +
                            ('0' + dataHoje.getHours()).slice(-2) + ':' +
                            ('0' + dataHoje.getMinutes()).slice(-2);

            listaManuais.value.unshift({
                id: Date.now(),
                titulo: novoManual.value.titulo,
                descricao: novoManual.value.descricao || 'Manual cadastrado no sistema',
                arquivo: novoManual.value.nomeArquivo || 'Documento_Manual.pdf',
                tamanho: novoManual.value.arquivo ? (novoManual.value.arquivo.size / 1024).toFixed(2) + ' KB' : '500.00 KB',
                dataEnvio: dataStr,
                enviadoPor: 'PREFEITURA CPD'
            });

            alert('Manual publicado com sucesso!');
            fecharModalUpload();
        }

        function ordenarPor(coluna) {
            if (colunaOrdem.value === coluna) {
                ordemAsc.value = !ordemAsc.value;
            } else {
                colunaOrdem.value = coluna;
                ordemAsc.value = true;
            }
        }

        function recarregarLista() {
            termoBusca.value = '';
            alert('Lista de manuais atualizada.');
        }

        function visualizarManual(item) {
            alert('Visualizando documento: ' + item.arquivo);
        }

        function downloadManual(item) {
            alert('Iniciando download do manual: ' + item.arquivo);
        }

        function excluirManual(item) {
            if (confirm('Deseja realmente excluir o manual "' + item.titulo + '"?')) {
                listaManuais.value = listaManuais.value.filter(m => m.id !== item.id);
            }
        }

        return {
            modalAberto,
            termoBusca,
            novoManual,
            listaManuais,
            manuaisFiltrados,
            abrirModalUpload,
            fecharModalUpload,
            tratarArquivoSelecionado,
            salvarManual,
            ordenarPor,
            recarregarLista,
            visualizarManual,
            downloadManual,
            excluirManual
        };
    }
}
function salvarNovoManual(e) {
    e.preventDefault();
    alert('Manual publicado com sucesso!');
    fecharModalUpload();
}
}).mount('#app');
</script>

</body>
</html>
