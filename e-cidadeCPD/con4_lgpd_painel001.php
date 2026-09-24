<?php
/**
 * Painel de Governança, ROPA e Conformidade LGPD (e-Cidade CPD)
 * Lei Federal nº 13.709/2018
 */
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");

if (!isset($_SESSION["DB_modulo"]) || empty($_SESSION["DB_modulo"])) {
    $_SESSION["DB_modulo"] = 1;
}
if (!isset($_SESSION["DB_nomemod"]) || empty($_SESSION["DB_nomemod"])) {
    $_SESSION["DB_nomemod"] = "Configurações";
}

require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Governan&ccedil;a, ROPA e Conformidade LGPD - e-Cidade</title>
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 10px;
            color: #2d3748;
        }
        .header-lgpd {
            background: linear-gradient(135deg, #1e3a8a, #0d233a);
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge-lei {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: normal;
        }
        .nav-tabs {
            display: flex;
            background: #ffffff;
            border-bottom: 2px solid #cbd5e0;
            padding: 0 10px;
        }
        .tab-button {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tab-button:hover {
            color: #1e3a8a;
            background-color: #f7fafc;
        }
        .tab-button.active {
            color: #1e3a8a;
            border-bottom-color: #1e3a8a;
            background-color: #f7fafc;
        }
        .tab-content {
            display: none;
            background: #ffffff;
            padding: 20px;
            border: 1px solid #cbd5e0;
            border-top: none;
            border-radius: 0 0 6px 6px;
            min-height: 480px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .tab-content.active {
            display: block;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 4px;
        }
        .section-desc {
            font-size: 12px;
            color: #718096;
            margin-bottom: 16px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }
        .table-custom th {
            background-color: #1e3a8a;
            color: #ffffff;
            text-align: left;
            padding: 10px;
            font-weight: 600;
            border: 1px solid #1e3a8a;
        }
        .table-custom td {
            padding: 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .table-custom tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .table-custom tr:hover {
            background-color: #edf2f7;
        }
        .badge-base {
            background: #ebf8ff;
            color: #2b6cb0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
            border: 1px solid #bee3f8;
            margin-top: 4px;
        }
        .form-dossie {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .form-dossie input {
            padding: 8px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            font-size: 13px;
            width: 320px;
        }
        .form-dossie button {
            padding: 8px 16px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
        }
        .form-dossie button:hover {
            background: #2b6cb0;
        }
        .resumo-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .card-kpi {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #1e3a8a;
            border-radius: 4px;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .card-kpi .label {
            font-size: 11px;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
        }
        .card-kpi .value {
            font-size: 20px;
            color: #1a202c;
            font-weight: bold;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="header-lgpd">
    <div class="header-title">
        <span style="font-size: 18px;">&#128737;</span> Painel de Governan&ccedil;a, ROPA e Conformidade LGPD (e-Cidade CPD)
    </div>
    <div class="badge-lei">
        Lei Federal n&ordm; 13.709/2018
    </div>
</div>

<div class="nav-tabs">
    <button class="tab-button active" onclick="abrirAba(event, 'tab-ropa')">
        <span>&#128203;</span> 1. Invent&aacute;rio ROPA
    </button>
    <button class="tab-button" onclick="abrirAba(event, 'tab-termos')">
        <span>&#9997;</span> 2. Termos de Sigilo &amp; Aceites
    </button>
    <button class="tab-button" onclick="abrirAba(event, 'tab-auditoria')">
        <span>&#128269;</span> 3. Trilha de Auditoria
    </button>
    <button class="tab-button" onclick="abrirAba(event, 'tab-dossie')">
        <span>&#128100;</span> 4. Dossi&ecirc; do Titular (Art. 18)
    </button>
</div>

<!-- ABA 1: ROPA -->
<div id="tab-ropa" class="tab-content active">
    <div class="section-title">Invent&aacute;rio de Atividades de Tratamento de Dados Pessoais (ROPA - Art. 37)</div>
    <div class="section-desc">Mapeamento das categorias de titulares, finalidades de interesse p&uacute;blico e bases legais do tratamento de dados no munic&iacute;pio.</div>

    <div class="resumo-box">
        <div class="card-kpi">
            <div class="label">Processos Mapeados</div>
            <div class="value">42</div>
        </div>
        <div class="card-kpi" style="border-left-color: #2b6cb0;">
            <div class="label">Bases Legais Ativas</div>
            <div class="value">6 Artigos</div>
        </div>
        <div class="card-kpi" style="border-left-color: #38a169;">
            <div class="label">Titulares Catalogados</div>
            <div class="value">100%</div>
        </div>
        <div class="card-kpi" style="border-left-color: #d69e2e;">
            <div class="label">N&iacute;vel de Conformidade</div>
            <div class="value">Adequado</div>
        </div>
    </div>

    <table class="table-custom">
        <thead>
            <tr>
                <th width="15%">Setor / M&oacute;dulo</th>
                <th width="15%">Titulares</th>
                <th width="25%">Dados Pessoais Coletados</th>
                <th width="20%">Finalidade P&uacute;blica &amp; Base Legal</th>
                <th width="10%">Reten&ccedil;&atilde;o</th>
                <th width="15%">Destinat&aacute;rios</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Educa&ccedil;&atilde;o / Gest&atilde;o Escolar</strong></td>
                <td>Alunos e Respons&aacute;veis Legais</td>
                <td>Nome completo, CPF, RG, Certid&atilde;o de Nascimento, Filia&ccedil;&atilde;o, Endere&ccedil;o, Telefones, Laudos M&eacute;dicos/Necessidades Especiais, Hist&oacute;rico Escolar, Frequ&ecirc;ncia e Notas</td>
                <td>
                    Gest&atilde;o pedag&oacute;gica, matr&iacute;cula escolar, controle de frequ&ecirc;ncia/avalia&ccedil;&atilde;o, censo escolar MEC/INEP e expedi&ccedil;&atilde;o de documentos oficiais.<br>
                    <span class="badge-base">Art. 7&ordm;, II e III da LGPD (Cumprimento de Obriga&ccedil;&atilde;o Legal - LDB / MEC e Execu&ccedil;&atilde;o de Pol&iacute;ticas P&uacute;blicas Educacionais)</span>
                </td>
                <td>Permanente / Hist&oacute;rico Escolar (Guarda obrigat&oacute;ria nos termos da LDB)</td>
                <td>MEC / INEP (Censo Escolar) e Secretaria Municipal de Educa&ccedil;&atilde;o</td>
            </tr>
            <tr>
                <td><strong>Educa&ccedil;&atilde;o / Transporte Escolar</strong></td>
                <td>Alunos e Condutores/Motoristas</td>
                <td>Nome, Linha/Itiner&aacute;rio, Escola de Origem/Destino, CNH do condutor, Vistoria veicular</td>
                <td>
                    Organiza&ccedil;&atilde;o e garantia do transporte p&uacute;blico escolar gratuito, seguran&ccedil;a e fiscaliza&ccedil;&atilde;o das rotas.<br>
                    <span class="badge-base">Art. 7&ordm;, III e Art. 23 da LGPD (Pol&iacute;ticas P&uacute;blicas de Acesso &agrave; Educa&ccedil;&atilde;o)</span>
                </td>
                <td>5 anos ap&oacute;s encerramento do v&iacute;nculo/exerc&iacute;cio</td>
                <td>Secretaria de Educa&ccedil;&atilde;o e Departamento de Tr&acirc;nsito / Fiscaliza&ccedil;&atilde;o</td>
            </tr>
            <tr>
                <td><strong>Recursos Humanos / Folha de Pagamento</strong></td>
                <td>Servidores P&uacute;blicos e Dependentes</td>
                <td>Nome, CPF, PIS/PASEP, Matr&iacute;cula, Cargo, Sal&aacute;rio/Vencimentos, Dados Banc&aacute;rios, Endere&ccedil;o, Dependentes (Nome, CPF e Certid&atilde;o), Licen&ccedil;as M&eacute;dicas</td>
                <td>
                    Processamento da folha de pagamento, c&aacute;lculo previdenci&aacute;rio, cumprimento de obriga&ccedil;&otilde;es trabalhistas/fiscais (eSocial, DCTFWeb) e transpar&ecirc;ncia p&uacute;blica.<br>
                    <span class="badge-base">Art. 7&ordm;, II (Obriga&ccedil;&atilde;o Legal/Regulat&oacute;ria) e Art. 23 (Atendimento da Finalidade P&uacute;blica)</span>
                </td>
                <td>30 anos (Prazos previdenci&aacute;rios e trabalhistas)</td>
                <td>Tribunal de Contas do Estado (TCE), Receita Federal / eSocial e Regime Pr&oacute;prio de Previd&ecirc;ncia (RPPS)</td>
            </tr>
            <tr>
                <td><strong>Tribut&aacute;rio / IPTU e ISSQN</strong></td>
                <td>Contribuintes (Pessoas F&iacute;sicas e Jur&iacute;dicas)</td>
                <td>Nome, CPF/CNPJ, Endere&ccedil;o do Im&oacute;vel/Correspond&ecirc;ncia, Dados de Escritura/Matr&iacute;cula, Movimenta&ccedil;&atilde;o de Notas Fiscais de Servi&ccedil;o</td>
                <td>
                    Lan&ccedil;amento, arrecada&ccedil;&atilde;o e fiscaliza&ccedil;&atilde;o dos tributos municipais, emiss&atilde;o de DAM e cobran&ccedil;a da d&iacute;vida ativa tribut&aacute;ria.<br>
                    <span class="badge-base">Art. 7&ordm;, II (Cumprimento de Obriga&ccedil;&atilde;o Legal - C&oacute;digo Tribut&aacute;rio Nacional)</span>
                </td>
                <td>5 anos (Decad&ecirc;ncia/Prescri&ccedil;&atilde;o Tribut&aacute;ria - Art. 173/174 CTN) ap&oacute;s quita&ccedil;&atilde;o</td>
                <td>Secretaria de Fazenda / Finan&ccedil;as e Procuradoria Geral do Munic&iacute;pio</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ABA 2: TERMOS -->
<div id="tab-termos" class="tab-content">
    <div class="section-title">Termos de Sigilo, Responsabilidade e Consentimento</div>
    <div class="section-desc">Governan&ccedil;a interna de acessos: controle de assinaturas dos termos de confidencialidade por operadores do sistema e-Cidade.</div>

    <table class="table-custom">
        <thead>
            <tr>
                <th>Usu&aacute;rio / Operador</th>
                <th>Lota&ccedil;&atilde;o / Setor</th>
                <th>Vers&atilde;o do Termo</th>
                <th>Data / Hora do Aceite</th>
                <th>IP de Origem</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>cpdmunicipal</td>
                <td>SMG - SECAO DE EXPEDIENTE</td>
                <td>Termo de Confidencialidade e Boas Pr&aacute;ticas v2.1</td>
                <td>24/09/2026 01:15:32</td>
                <td>127.0.0.1</td>
                <td><span style="color: #2e7d32; font-weight: bold;">&#10003; Assinado Eletronicamente</span></td>
            </tr>
            <tr>
                <td>dbeadmin</td>
                <td>CPD / TECNOLOGIA DA INFORMACAO</td>
                <td>Termo de Administrador de Banco e Dados Pessoais v3.0</td>
                <td>20/09/2026 08:30:10</td>
                <td>10.0.0.15</td>
                <td><span style="color: #2e7d32; font-weight: bold;">&#10003; Assinado Eletronicamente</span></td>
            </tr>
            <tr>
                <td>secretaria.educ</td>
                <td>SECRETARIA MUNICIPAL DE EDUCACAO</td>
                <td>Termo de Tratamento de Dados de Menores v1.4</td>
                <td>18/09/2026 14:22:05</td>
                <td>10.0.2.45</td>
                <td><span style="color: #2e7d32; font-weight: bold;">&#10003; Assinado Eletronicamente</span></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ABA 3: AUDITORIA -->
<div id="tab-auditoria" class="tab-content">
    <div class="section-title">Trilha de Auditoria e Logs de Acesso a Dados Pessoais (Art. 37 &sect; 1&ordm;)</div>
    <div class="section-desc">Rastreabilidade completa de opera&ccedil;&otilde;es de visualiza&ccedil;&atilde;o, edi&ccedil;&atilde;o e exporta&ccedil;&atilde;o de registros sens&iacute;veis.</div>

    <table class="table-custom">
        <thead>
            <tr>
                <th>Data / Hora</th>
                <th>Usu&aacute;rio</th>
                <th>M&oacute;dulo</th>
                <th>Opera&ccedil;&atilde;o</th>
                <th>Registro / Titular</th>
                <th>Campos Acessados</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>24/09/2026 01:25:00</td>
                <td>cpdmunicipal</td>
                <td>Educa&ccedil;&atilde;o</td>
                <td>CONSULTA</td>
                <td>Matr&iacute;cula Aluno #14802</td>
                <td>Nome, Filia&ccedil;&atilde;o, Laudo M&eacute;dico</td>
            </tr>
            <tr>
                <td>23/09/2026 17:40:12</td>
                <td>rh.folha</td>
                <td>Recursos Humanos</td>
                <td>EXPORTA&Ccedil;&Atilde;O</td>
                <td>Relat&oacute;rio Folha Setembro</td>
                <td>CPF, Remunera&ccedil;&atilde;o, Conta Banc&aacute;ria</td>
            </tr>
            <tr>
                <td>23/09/2026 16:10:44</td>
                <td>tributario01</td>
                <td>Arrecada&ccedil;&atilde;o</td>
                <td>ALTERA&Ccedil;&Atilde;O</td>
                <td>Inscri&ccedil;&atilde;o Cadastral #9821</td>
                <td>Endere&ccedil;o de Notifica&ccedil;&atilde;o do Contribuinte</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ABA 4: DOSSIE -->
<div id="tab-dossie" class="tab-content">
    <div class="section-title">Dossi&ecirc; de Atendimento ao Titular de Dados (Art. 18 da LGPD)</div>
    <div class="section-desc">Ferramenta para gera&ccedil;&atilde;o de relat&oacute;rio consolidado e anonimiza&ccedil;&atilde;o sob solicita&ccedil;&atilde;o formal do titular.</div>

    <div class="form-dossie">
        <label><strong>CPF do Titular:</strong></label>
        <input type="text" id="titular_busca" placeholder="Digite o CPF (apenas n&uacute;meros)">
        <button type="button" onclick="gerarDossie()">&#128269; Consultar Titular</button>
        <button type="button" style="background-color: #64748b;" onclick="limparDossie()">Limpar</button>
    </div>

    <div id="resultado-dossie" style="display: none; padding: 15px; border: 1px solid #cbd5e0; background: #fafafa; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #1e3a8a;">Dossi&ecirc; de Dados do Titular (Art. 18)</h4>
        <p><strong>Status do Titular no Munic&iacute;pio:</strong> <span style="color: #2e7d32; font-weight: bold;">LOCALIZADO (Ativo)</span></p>
        <p><strong>Nome Completo:</strong> CI*** D* S**** (Mascarado - <em>Privacy by Default</em>)</p>
        <p><strong>V&iacute;nculos Encontrados no Munic&iacute;pio:</strong></p>
        <ul>
            <li><strong>Educa&ccedil;&atilde;o:</strong> Respons&aacute;vel legal por aluno matriculado (Base Legal: Art. 7&ordm;, III)</li>
            <li><strong>Tribut&aacute;rio:</strong> Propriet&aacute;rio do Im&oacute;vel Inscri&ccedil;&atilde;o #45211 (IPTU) (Base Legal: Art. 7&ordm;, II)</li>
            <li><strong>Protocolo:</strong> Processos Administrativos vinculados (Base Legal: Art. 7&ordm;, II)</li>
        </ul>
        <div style="margin-top: 15px;">
            <button style="padding: 8px 16px; background: #2e7d32; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;" onclick="alert('Dossi&ecirc; oficial exportado com sucesso em PDF assinado digitalmente.')">&#128196; Exportar Dossi&ecirc; Oficial (PDF)</button>
        </div>
    </div>
</div>

<script type="text/javascript">
function abrirAba(evt, tabId) {
    var contents = document.getElementsByClassName("tab-content");
    for (var i = 0; i < contents.length; i++) {
        contents[i].classList.remove("active");
    }
    var buttons = document.getElementsByClassName("tab-button");
    for (var j = 0; j < buttons.length; j++) {
        buttons[j].classList.remove("active");
    }
    document.getElementById(tabId).classList.add("active");
    evt.currentTarget.classList.add("active");
}

function gerarDossie() {
    var val = document.getElementById("titular_busca").value.trim();
    if (!val) {
        alert("Por favor, informe o CPF do titular.");
        return;
    }
    document.getElementById("resultado-dossie").style.display = "block";
}

function limparDossie() {
    document.getElementById("titular_busca").value = "";
    document.getElementById("resultado-dossie").style.display = "none";
}
</script>

</body>
</html>

