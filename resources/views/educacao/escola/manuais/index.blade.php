<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manuais da Educação - e-Cidade</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f9;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
            color: #333;
            padding: 16px;
            color: #333333;
        }
        .container {
            max-width: 1100px;
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
        }
        /* Card Header */
        .card-header-manuais {
            background: #ffffff;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            padding: 18px 24px;
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
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
        .header-texts h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }
        .header-texts p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #6b7280;
        }
        .header-actions {
            display: flex;
            gap: 8px;
        }
        .btn-novo-manual {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        h1 {
            color: #1a4d7a;
            border-bottom: 2px solid #1a4d7a;
            padding-bottom: 10px;
            margin-top: 0;
            font-size: 24px;
        .btn-novo-manual:hover {
            background-color: #1d4ed8;
        }
        .card-upload {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        .btn-refresh {
            background-color: #f97316;
            color: #ffffff;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 6px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        .card-upload h3 {
            margin-top: 0;
            color: #2b6cb0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .form-row {
        .btn-refresh:hover {
            background-color: #ea580c;
        }
        /* Search Bar */
        .search-container {
            background: #ffffff;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 16px;
            display: flex;
            gap: 15px;
            margin-bottom: 12px;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .form-group {
            flex: 1;
        .search-input-wrapper {
            position: relative;
            width: 320px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #4a5568;
        }
        .form-control {
        .search-input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            padding: 8px 12px 8px 32px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            box-sizing: border-box;
            color: #374151;
            outline: none;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            text-align: center;
        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .btn-primary {
            background-color: #2b6cb0;
            color: #fff;
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }
        .btn-primary:hover {
            background-color: #2c5282;
        .search-counter {
            font-size: 13px;
            color: #6b7280;
        }
        .btn-success {
            background-color: #38a169;
            color: #fff;
        .search-counter strong {
            color: #111827;
        }
        .btn-info {
            background-color: #3182ce;
            color: #fff;
            padding: 5px 10px;
            font-size: 12px;
        /* Table Card */
        .table-card {
            background: #ffffff;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .btn-danger {
            background-color: #e53e3e;
            color: #fff;
            padding: 5px 10px;
            font-size: 12px;
        }
        .table-manuais {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            text-align: left;
        }
        .table-manuais th, .table-manuais td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: left;
        .table-manuais thead th {
            background-color: #3b6998;
            color: #ffffff;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }
        .table-manuais th {
            background-color: #edf2f7;
            color: #2d3748;
        .table-manuais tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.15s;
        }
        .table-manuais tr:nth-child(even) {
            background-color: #f7fafc;
        .table-manuais tbody tr:hover {
            background-color: #f9fafb;
        }
        .badge {
        .table-manuais td {
            padding: 14px 16px;
            font-size: 13px;
            vertical-align: middle;
        }
        .doc-cell {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .doc-pdf-icon {
            color: #dc2626;
            font-size: 24px;
            margin-top: 2px;
        }
        .doc-info .doc-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 3px;
            display: block;
        }
        .doc-info .doc-desc {
            font-size: 12px;
            color: #6b7280;
            display: block;
            margin-bottom: 2px;
        }
        .doc-info .doc-filename {
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .badge-tamanho {
            background-color: #06b6d4;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pdf { background: #fed7d7; color: #9b2c2c; }
        .badge-doc { background: #bee3f8; color: #2c5282; }
        .badge-other { background: #e2e8f0; color: #4a5568; }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #718096;
            font-style: italic;
        .data-cell, .enviado-cell {
            color: #4b5563;
            font-size: 12px;
        }
        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        .enviado-cell {
            font-weight: 600;
        }
        /* Action buttons */
        .actions-cell {
            display: flex;
            gap: 6px;
            align-items: center;
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
            transition: opacity 0.2s;
            text-decoration: none;
        }
        .btn-action-circle:hover {
            opacity: 0.85;
        }
        .btn-view {
            background-color: #3b82f6;
            color: #ffffff;
        }
        .btn-download {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-delete {
            background-color: #ef4444;
            color: #ffffff;
        }
        /* Modal Upload */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .modal-content {
            background: #ffffff;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
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
        }
        .form-group-modal {
            margin-bottom: 14px;
        }
        .form-group-modal label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }
        .form-input-modal {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Manuais e Guias da Educação</h1>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-upload">
        <h3>Publicar Novo Manual / Documento</h3>
        <form action="{{ url('educacao/escola/manuais/salvar') }}" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label>Título do Manual</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Manual de Fechamento de Diário de Classe" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Módulo / Submódulo</label>
                    <select name="modulo" class="form-control">
                        <option value="Escola">Escola</option>
                        <option value="Secretaria">Secretaria</option>
                        <option value="Diário de Classe">Diário de Classe</option>
                        <option value="Transporte Escolar">Transporte Escolar</option>
                        <option value="Alimentação Escolar">Alimentação Escolar</option>
                    </select>
                </div>
<div class="main-container">
    <!-- Header Card -->
    <div class="card-header-manuais">
        <div class="header-left">
            <div class="icon-box">
                📖
            </div>
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label>Descrição / Instruções</label>
                    <input type="text" name="descricao" class="form-control" placeholder="Breve resumo sobre o conteúdo do manual">
                </div>
                <div class="form-group" style="flex: 1.5;">
                    <label>Arquivo (PDF, DOCX, Imagem)</label>
                    <input type="file" name="arquivo" class="form-control" required>
                </div>
                <div class="form-group" style="flex: 0.5; margin-top: 18px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Salvar</button>
                </div>
            <div class="header-texts">
                <h2>Manuais da Educação</h2>
                <p>Consulte e baixe os manuais, guias e documentos oficiais da Educação.</p>
            </div>
        </form>
        </div>
        <div class="header-actions">
            <button class="btn-novo-manual" onclick="abrirModalUpload()">
                <span>+</span> Novo Manual
            </button>
            <button class="btn-refresh" onclick="window.location.reload()" title="Atualizar">
                🔄
            </button>
        </div>
    </div>

    <h3>Manuais Disponíveis</h3>
    <table class="table-manuais">
        <thead>
            <tr>
                <th style="width: 35%;">Título / Descrição</th>
                <th style="width: 15%;">Módulo</th>
                <th style="width: 10%;">Formato</th>
                <th style="width: 15%;">Data</th>
                <th style="width: 25%;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($manuais) && count($manuais) > 0)
                @foreach($manuais as $m)
                    <tr>
                        <td>
                            <strong>{{ isset($m['titulo']) ? $m['titulo'] : 'Sem título' }}</strong>
                            @if(isset($m['descricao']) && $m['descricao'])
                                <br><small style="color: #718096;">{{ $m['descricao'] }}</small>
                            @endif
                        </td>
                        <td>{{ isset($m['modulo']) ? $m['modulo'] : 'Educação' }}</td>
                        <td>
                            @php
                                $ext = isset($m['extensao']) ? strtolower($m['extensao']) : 'pdf';
                                $badgeClass = ($ext === 'pdf') ? 'badge-pdf' : (($ext === 'doc' || $ext === 'docx') ? 'badge-doc' : 'badge-other');
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($ext) }}</span>
                        </td>
                        <td>{{ isset($m['data_criacao']) ? date('d/m/Y H:i', strtotime($m['data_criacao'])) : '-' }}</td>
                        <td>
                            <a href="{{ url('educacao/escola/manuais/visualizar/' . $m['id']) }}" target="_blank" class="btn btn-info">Visualizar</a>
                            <a href="{{ url('educacao/escola/manuais/download/' . $m['id']) }}" class="btn btn-success">Download</a>
                            <button onclick="excluirManual('{{ $m['id'] }}')" class="btn btn-danger">Excluir</button>
                        </td>
                    </tr>
                @endforeach
            @else
    <!-- Search Bar -->
    <div class="search-container">
        <div class="search-input-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" id="filtro-busca" class="search-input" placeholder="Pesquisar por título ou descrição..." onkeyup="filtrarTabela()">
        </div>
        <div class="search-counter" id="contador-manuais">
            Exibindo <strong id="qtd-exibida">4</strong> de <strong id="qtd-total">4</strong> manuais
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <table class="table-manuais" id="tabela-manuais">
            <thead>
                <tr>
                    <td colspan="5" class="empty-state">Nenhum manual cadastrado até o momento. Utilize o formulário acima para publicar o primeiro manual.</td>
                    <th style="width: 50%;">Documento ↑↓</th>
                    <th style="width: 12%;">Tamanho ↑↓</th>
                    <th style="width: 15%;">Data de Envio ↑↓</th>
                    <th style="width: 13%;">Enviado por ↑↓</th>
                    <th style="width: 10%;">Ações</th>
                </tr>
            @endif
        </tbody>
    </table>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-pdf-icon">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual para capacitar professores em diário de classe</span>
                                <span class="doc-desc">Mostra os procedimentos para lançamentos de notas, faltas e outros em diário de classe</span>
                                <span class="doc-filename">📄 CAPACITAÇÃO-PROFESSORES.pdf</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">537.46 KB</span></td>
                    <td class="data-cell">23/09/2026 09:50</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('CAPACITAÇÃO-PROFESSORES.pdf')">👁️</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('CAPACITAÇÃO-PROFESSORES.pdf')">⬇️</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑️</button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-pdf-icon">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual módulo educação/secretaria</span>
                                <span class="doc-desc">Abarcará os principais cadastros, e parâmetros para o funcionamento da área de educação módulo secretaria</span>
                                <span class="doc-filename">📄 Manual_Educacao_Secretaria.pdf</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">10.38 MB</span></td>
                    <td class="data-cell">23/09/2026 09:47</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('Manual_Educacao_Secretaria.pdf')">👁️</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('Manual_Educacao_Secretaria.pdf')">⬇️</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑️</button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-pdf-icon">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual Matriculando Alunos</span>
                                <span class="doc-desc">Manual Matriculando Alunos</span>
                                <span class="doc-filename">📄 Matriculando_alunos.pdf</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">882.57 KB</span></td>
                    <td class="data-cell">22/09/2026 22:14</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('Matriculando_alunos.pdf')">👁️</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('Matriculando_alunos.pdf')">⬇️</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑️</button>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="doc-cell">
                            <span class="doc-pdf-icon">📄</span>
                            <div class="doc-info">
                                <span class="doc-title">Manual Transferindo Alunos e-cidade</span>
                                <span class="doc-desc">Manual Transferindo Alunos e-cidade</span>
                                <span class="doc-filename">📄 manual_transferindo_alunos_ecidade.pdf</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-tamanho">795.82 KB</span></td>
                    <td class="data-cell">22/09/2026 22:14</td>
                    <td class="enviado-cell">PREFEITURA CPD</td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action-circle btn-view" title="Visualizar" onclick="visualizarDocumento('manual_transferindo_alunos_ecidade.pdf')">👁️</button>
                            <button class="btn-action-circle btn-download" title="Download" onclick="downloadDocumento('manual_transferindo_alunos_ecidade.pdf')">⬇️</button>
                            <button class="btn-action-circle btn-delete" title="Excluir" onclick="excluirItem(this)">🗑️</button>
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
            <span style="cursor: pointer; font-weight: bold;" onclick="fecharModalUpload()">✕</span>
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
function excluirManual(id) {
    if (!confirm('Deseja realmente excluir este manual?')) {
        return;
    }
    fetch('{{ url("educacao/escola/manuais/excluir") }}/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
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
        }
    }).then(function(r) { return r.json(); })
      .then(function(res) {
          if (res.status) {
              alert('Manual excluído com sucesso.');
              window.location.reload();
          } else {
              alert('Erro: ' + (res.message || 'Falha ao excluir'));
          }
      }).catch(function(err) {
          alert('Erro de comunicação.');
      });
    });
    document.getElementById('qtd-exibida').innerText = visiveis;
}
function visualizarDocumento(nome) {
    alert('Abrindo visualizador de PDF para: ' + nome);
}
function downloadDocumento(nome) {
    alert('Iniciando download seguro de: ' + nome);
}
function excluirItem(btn) {
    if (confirm('Deseja realmente excluir este manual?')) {
        btn.closest('tr').remove();
        filtrarTabela();
    }
}
function salvarNovoManual(e) {
    e.preventDefault();
    alert('Manual publicado com sucesso!');
    fecharModalUpload();
}
</script>

</body>
</html>

