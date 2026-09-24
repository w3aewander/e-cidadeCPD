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

require_once modification('libs/db_stdlib.php');
require_once modification('libs/db_conecta.php');
require_once modification('libs/db_sessoes.php');
require_once modification('libs/db_usuariosonline.php');
require_once modification('libs/db_utils.php');
require_once modification('libs/db_app.utils.php');
require_once modification('dbforms/db_funcoes.php');
$instituicao = InstituicaoRepository::getInstituicaoByCodigo(db_getsession('DB_instit'));
$departamento = db_getsession('DB_coddepto');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Microsist</title>
    <meta charset="iso-8859-1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link type="text/css" href="assets/bootstrap-table/css/bootstrap.min.css" rel="stylesheet">
    <link type="text/css" href="assets/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link type="text/css" href="estilos.css" rel="stylesheet">
    <script rel="script" type="text/javascript" src="scripts/scripts.js"></script>
    <script type="text/javascript" src="scripts/strings.js"></script>
    <script rel="script" type="text/javascript" src="scripts/classes/http/http.js"></script>
    <script type="text/javascript" src="scripts/widgets/DBLookUp.widget.js"></script>
    <script type="text/javascript" src="scripts/widgets/windowAux.widget.js"></script>
    <script type="text/javascript" src="scripts/prototype.js"></script>
    <script type="text/javascript" src="scripts/widgets/Input/DBInput.widget.js"></script>
    <script type="text/javascript" src="scripts/widgets/Input/DBInputDate.widget.js"></script>
    <style>
        select:disabled, input:disabled {
            color: dimgrey;
        }
    </style>
</head>
<body>
<div>
    <div class="container">
        <fieldset>
            <legend>Plano de Contratações</legend>
            <fieldset>
                <table class="form-container">
                    <tr>
                        <td>
                            <label for="orgao">Órgão:</label>
                        </td>
                        <td>
                            <input type="text" id="orgao" style="margin-left: 52px" class="field-size9" disabled>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="codigoUnidade">Unidade Compradora:</label>
                        </td>
                        <td>
                            <select id="codigoUnidade" name="codigoUnidade" style="width: 384px; margin-left: 52px">
                                <option value="0" selected disabled>Selecione</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="anoPCA">Ano PCA:</label>
                        </td>
                        <td>
                            <input
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1')"
                                id="anoPCA"
                                maxlength="4"
                                style="margin-left: 52px"
                                class="field-size9"
                            >
                        </td>
                    </tr>
                </table>
            </fieldset>
            <div class="subcontainer">
                <fieldset>
                    <table class="form-container">
                        <tr>
                            <td>
                                <label for="unidadeEcidadeRequisitante">Unidade E-Cidade:</label>
                            </td>
                            <td>
                                <select name="unidadeEcidadeRequisitante" id="unidadeEcidadeRequisitante">
                                    <option value="sim" selected>Sim</option>
                                    <option value="nao" >Não</option>
                                </select>
                            </td>
                        </tr>
                        <tr id="trUnidadeRequisitante">
                            <td>
                                <label for="ac16_coddepto" id="ancoraUnidadeRequisitante">Unidade Requisitante:</label>
                            </td>
                            <td>
                                <input type="text" id="coddepto" value="<?= $departamento ?>">
                                <input id="descrdepto" style="width: 296px" maxlength="255">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="text" id="departamentoSessao" value="<?= $departamento ?>" hidden>
                            </td>
                        </tr>
                        <tr id="trUnidadeRequisitanteTexto" hidden>
                            <td>
                                <label  id="unidadeRequisitante">Unidade Requisitante:</label>
                            </td>
                            <td>
                                <input type="text" id="unidadeRequisitanteTexto" maxlength="255" class="field-size9">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="catalogoMaterialServico">Catálogo de materiais/serviços:</label>
                            </td>
                            <td>
                                <select name="catalogoMaterialServico" id="catalogoMaterialServico">
                                    <option value="1">Outros</option>
                                    <option value="2">CNBS</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="pc16_codmater" id="ancoraMaterial">Código do Material/Serviço:</label>
                            </td>
                            <td>
                                <input type="text" id="pc01_codmater">
                                <input type="text" id="pc01_descrmater" style="width: 296px">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="categoriaItem">Categoria do Item do PCA:</label>
                            </td>
                            <td>
                                <select name="categoriaItem" id="categoriaItem">
                                    <option value="0" disabled selected>Selecione</option>
                                    <option value="1">Material</option>
                                    <option value="2">Serviço</option>
                                    <option value="3">Obras</option>
                                    <option value="4">Serviços de Engenharia</option>
                                    <option value="5">Soluções de TIC</option>
                                    <option value="6">Locação de Imóveis</option>
                                    <option value="7">Alienação/Concessão/Permissão</option>
                                    <option value="8">Obras e Serviços de Engenharia</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="unidadeEcidade">Unidade E-Cidade:</label>
                            </td>
                            <td>
                                <select name="unidadeEcidade" id="unidadeEcidade">
                                    <option value="sim" selected>Sim</option>
                                    <option value="nao">Não</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="unidadeFornecimento">Unidade de Fornecimento:</label>
                            </td>
                            <td>
                                <select name="unidadeFornecimento" id="unidadeFornecimento" hidden>
                                    <option value="0">Selecione</option>
                                </select>
                                <input type="text" class="field-size9" id="unidadeFornecimentoTexto" maxlength="255">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="quantidade">Quantidade:</label>
                            </td>
                            <td>
                                <input type="text" id="quantidade" class="field-size9">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="valorUnitario">Valor Unitário:</label>
                            </td>
                            <td>
                                <input id="valorUnitario" class="field-size9" maxlength="21">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="valorTotal">Valor Total:</label>
                            </td>
                            <td>
                                <input id="valorTotal" class="field-size9" maxlength="21" disabled>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="valorOrcamento">Valor Orçamento Exercício:</label>
                            </td>
                            <td>
                                <input id="valorOrcamento" class="field-size9" maxlength="21">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="dataDesejada">Data Desejada:</label>
                            </td>
                            <td>
                                <input id="dataDesejada">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="hidden" id="cnpj" value="<?= $instituicao->getCNPJ(); ?>">
                            </td>
                            <td>
                                <input type="text" id="codigoGrupo" hidden>
                                <input type="text" id="descricaoGrupo" hidden>
                                <input type="text" id="classificacaoCatalogo" hidden>
                            </td>
                        </tr>
                    </table>
                </fieldset>
        </fieldset>
    </div>
</div>
<div class="subcontainer">
    <button class="btn btn-light" id="btnAdicionar">
        <i class="fa fa-plus-circle" aria-hidden="true"></i>
        Adicionar
    </button>
    <button class="btn btn-light" id="btnSalvar">
        <i class="fa fa-save" aria-hidden="true"></i>
        Salvar
    </button>
</div>
</div>
<div class="subcontainer">
    <fieldset id="ctnTable" style="width: 1500px">
        <legend>Itens do Plano</legend>
        <table id="table-itens"
               class="table table-sm"
               data-height="300"
               data-virtual-scroll="true"
               data-remember-order="true"
               style="width: 100%;">
        </table>
    </fieldset>
</div>

<script type="text/javascript" src="scripts/classes/bootstrapTable/detailFormaterTable.js"></script>
<script type="text/javascript" src="assets/jquery/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="assets/bootstrap-table/bootstrap-table.min.js"></script>
<script type="text/javascript" src="assets/bootstrap-table/locale/bootstrap-table-pt-BR.min.js"></script>
<script rel="script" type="text/javascript" src="scripts/session.js"></script>
<script>
    $.noConflict();
    const codigoGrupo = document.getElementById('codigoGrupo');
    const classificacaoCatalogo = document.getElementById('classificacaoCatalogo');
    const descricaoGrupo = document.getElementById('descricaoGrupo');
    const categoriaItem = document.getElementById('categoriaItem');
    const catalogoMaterialServico = document.getElementById('catalogoMaterialServico');
    const codigoUnidade = document.getElementById('codigoUnidade');
    const cnpj = document.getElementById('cnpj');
    const orgao = document.getElementById('orgao');
    const dataDesejada = new DBInputDate(document.getElementById("dataDesejada"));
    const tabelaItens = jQuery('#table-itens');
    const ancoraMaterial = document.getElementById('ancoraMaterial');
    const codigoMaterial = document.getElementById('pc01_codmater');
    const descricaoMaterial = document.getElementById('pc01_descrmater');
    const ancoraUnidadeRequisitante = document.getElementById('ancoraUnidadeRequisitante');
    const trUnidadeRequisitante = document.getElementById('trUnidadeRequisitante');
    const trUnidadeRequisitanteTexto = document.getElementById('trUnidadeRequisitanteTexto');
    const unidadeRequisitanteTexto = document.getElementById('unidadeRequisitanteTexto');
    const codigoDepartamento = document.getElementById('coddepto');
    const unidadeFornecimento = document.getElementById('unidadeFornecimento');
    const unidadeFornecimentoTexto = document.getElementById('unidadeFornecimentoTexto');
    const descricaoDepartamento = document.getElementById('descrdepto');
    const unidadeEcidadeRequisitante = document.getElementById('unidadeEcidadeRequisitante');
    const quantidade = document.getElementById('quantidade');
    const departamentoSessao = document.getElementById('departamentoSessao');
    const valorUnitario = document.getElementById('valorUnitario');
    const btnAdicionar = document.getElementById('btnAdicionar');
    const btnSalvar = document.getElementById('btnSalvar');
    const valorTotal = document.getElementById('valorTotal');
    const valorOrcamento = document.getElementById('valorOrcamento');
    const unidadeEcidade = document.getElementById('unidadeEcidade');
    const anoPCA = document.getElementById('anoPCA');
    const routes = {};
    let unidades = 0;
    let campo1Timeout;
    let item = 0;

    valorUnitario.addEventListener('change', () => {
        if (quantidade.value !== '' && valorUnitario.value !== '') {
            let quantidadeItem = parseFloat(quantidade.value.replace('.', '').replace(',', '.'));
            let valorUnitarioItem = parseFloat(valorUnitario.value.replace('.', '').replace(',', '.'));
            let total = (quantidadeItem * valorUnitarioItem);
            valorTotal.value = total.toLocaleString(
                'pt-BR',
                {style: 'decimal', useGrouping: 'true', minimumFractionDigits: '4', maximumFractionDigits: '4'}
            );
        }
    })

    unidadeEcidadeRequisitante.addEventListener('change', () => {
        codigoDepartamento.hidden = true;
        descricaoDepartamento.hidden = true;
        unidadeRequisitanteTexto.hidden = false;
        trUnidadeRequisitanteTexto.hidden = false;
        trUnidadeRequisitante.hidden = true;
        if (unidadeEcidadeRequisitante.value === 'sim') {
            trUnidadeRequisitanteTexto.hidden = true;
            trUnidadeRequisitante.hidden = false;
            codigoDepartamento.hidden = false;
            descricaoDepartamento.hidden = false;
            unidadeRequisitanteTexto.value = '';
            unidadeRequisitanteTexto.hidden = true;
        }
    })

    quantidade.addEventListener('input', listenerValor);

    valorUnitario.addEventListener('input', listenerValor);

    valorTotal.addEventListener('input', listenerValor);

    valorOrcamento.addEventListener('input', listenerValor);

    unidadeEcidade.addEventListener('change', () => {
        unidadeFornecimento.hidden = true;
        unidadeFornecimentoTexto.hidden = false;

        if (unidadeEcidade.value === 'sim') {
            unidadeFornecimento.hidden = false;
            unidadeFornecimentoTexto.hidden = true;
            unidadeFornecimentoTexto.value = '';
            buscaUnidadeFornecimento();
        }
    });

    btnAdicionar.addEventListener('click', () => {
        if (!validarCampos()) {
            return;
        }
        item++;
        let unidade = unidadeFornecimento.selectedOptions[0].textContent;
        let unidadeRequisitanteDescricao = unidadeRequisitanteTexto.value;
        if (unidadeEcidade.value === 'nao') {
            unidade = unidadeFornecimentoTexto.value;
        }
        if (unidadeEcidadeRequisitante.value === 'sim') {
            unidadeRequisitanteDescricao = descricaoDepartamento.value;
        }
        const itens = {
            numeroItem: item,
            categoriaItemPca: categoriaItem.value,
            descricao: descricaoMaterial.value,
            unidadeFornecimento: unidade,
            quantidade: quantidade.value,
            valorUnitario: valorUnitario.value,
            valorTotal: valorTotal.value,
            valorOrcamentoExercicio: valorOrcamento.value,
            unidadeRequisitante: unidadeRequisitanteDescricao,
            dataDesejada: dataDesejada.value,
            catalogo: catalogoMaterialServico.value,
            classificacaoCatalogo: classificacaoCatalogo.value,
            categoriaItemDescricao: categoriaItem.selectedOptions[0].textContent,
            codigoItem: codigoMaterial.value,
            classificacaoSuperiorCodigo: codigoGrupo.value,
            classificacaoSuperiorNome: descricaoGrupo.value,
            catalogoDescr: catalogoMaterialServico.selectedOptions[0].textContent,
        }
        tabelaItens.bootstrapTable('insertRow', {
            index: item,
            row: itens
        });
        limparCampos();
    })

    btnSalvar.addEventListener('click', () => {
        let data = tabelaItens.bootstrapTable('getData')
        if (data.length < 1) {
            alert('O envio do PCA requer que seja enviado pelo menos um item.');
            return;
        }
        if (data.length > 1000) {
            alert('O envio do PCA requer no máximo 1000 itens.');
            return;
        }

        let itens = [];

        for (const item of tabelaItens.bootstrapTable('getData')) {
            itens.push({
                numeroItem: item.numeroItem,
                categoriaItemPca: item.categoriaItemPca,
                descricao: item.descricao,
                unidadeFornecimento: item.unidadeFornecimento,
                quantidade: item.quantidade.replaceAll('.', '').replaceAll(',', '.'),
                valorUnitario: item.valorUnitario.replaceAll('.', '').replaceAll(',', '.'),
                valorTotal: item.valorTotal.replaceAll('.', '').replaceAll(',', '.'),
                valorOrcamentoExercicio: item.valorOrcamentoExercicio.replaceAll('.', '').replaceAll(',', '.'),
                unidadeRequisitante: item.unidadeRequisitante,
                dataDesejada: item.dataDesejada,
                catalogo: item.catalogo,
                classificacaoCatalogo: item.classificacaoCatalogo,
                categoriaItem: item.categoriaItem,
                codigoItem: item.codigoItem,
                classificacaoSuperiorCodigo: item.classificacaoSuperiorCodigo,
                classificacaoSuperiorNome: item.classificacaoSuperiorNome,
            })
        }
        itens = JSON.stringify(itens);
        const formData = new FormData();
        formData.append('codigoUnidade', codigoUnidade.value);
        formData.append('anoPca', anoPCA.value);
        formData.append('itensPlano', itens);
        formData.append('documento', cnpj.value);
        PHPSession.appendFormData(formData);
        HttpClient.post(routes.incluirPCA, {body: formData}).then(response => {
            if (response.error) {
                alert(response.message);
                return;
            }
            let msg = "Plano de Contratação publicado com sucesso.";
            let link = `<a target="_blank" href="${response.data.link}">Clique aqui para acessar.</a>`;
            alert(`${msg}\n${link}`);
            tabelaItens.bootstrapTable('removeAll');
            anoPCA.value = '';
            unidadeEcidade.value = 'sim'
            unidadeEcidade.dispatchEvent(new Event('change'));
            unidadeEcidadeRequisitante.value = 'sim';
            unidadeEcidadeRequisitante.dispatchEvent(new Event('change'));
            limparCampos();
            item = 0;
            codigoDepartamento.value = departamentoSessao.value;
            codigoDepartamento.dispatchEvent(new Event('change'));
            if (codigoUnidade.options.length > 1) {
                codigoUnidade.value = '0';
            }
        });
    });

    new DBLookUp(ancoraUnidadeRequisitante, codigoDepartamento, descricaoDepartamento, {
        'arquivo': 'func_db_depart.php',
        'label': 'Pesquisa',
        'objetoLookUp': 'db_iframe_db_depart',
    });

    new DBLookUp(ancoraMaterial, codigoMaterial, descricaoMaterial, {
        'arquivo': 'func_pcmatersolicita.php',
        'label': 'Pesquisa de contratos',
        'objetoLookUp': 'db_iframe_pcmater',
        'aCamposAdicionais': ['pc03_codgrupo', 'pc03_descrgrupo', 'pc01_servico'],
        'fCallBack': (a, b, grupoCodigo, grupoDescricao, servico) => {
            codigoGrupo.value = grupoCodigo;
            descricaoGrupo.value = grupoDescricao;
            classificacaoCatalogo.value = servico === 't' ? '2' : '1';
        }
    });

    jQuery(async () => {
        let apiUrl;
        await PHPSession.loadData().then(() => {
            apiUrl = PHPSession.requestApi;
        });

        routes.buscarEntidade = `${apiUrl}/patrimonial/pncp/unidades/buscarEntidade/`;
        routes.buscarUnidadesAtivas = `${apiUrl}/patrimonial/pncp/unidades/buscarUnidadesAtivas/`;
        routes.verificaIntegracao = `${apiUrl}/patrimonial/pncp/integracao/verificaIntegracao/`;
        routes.buscarUnidades = `${apiUrl}/patrimonial/pncp/planoContratacoes/buscar-unidades/`;
        routes.incluirPCA = `${apiUrl}/patrimonial/pncp/planoContratacoes/incluir/`;
        codigoDepartamento.dispatchEvent(new Event('change'));
        verificaIntegracaoAtiva();

        window.operateEvents = {
            'click .excluir': (e, d, row) => {
                tabelaItens.bootstrapTable('remove', {
                    field: 'numeroItem',
                    values: [row.numeroItem]
                });
            }
        }
        const colunasItens = [
            {
                field: 'numeroItem',
                title: 'Número do Item',
                halign: 'center',
                align: 'center',
                sortable: true
            },
            {
                field: 'categoriaItemDescricao',
                title: 'Categoria Item',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'classificacaoCatalogo',
                title: 'Material/Serviço',
                halign: 'center',
                align: 'center',
                formatter: (valor, data, index) => {
                    let classificacao = 'Serviço'
                    if (valor === '1') {
                        classificacao = 'Material'
                    }
                    return classificacao;
                }
            },
            {
                field: '',
                title: 'Catálogo',
                halign: 'center',
                align: 'center',
                formatter: () => {
                    return 'Outros';
                }
            },
            {
                field: 'classificacaoSuperiorNome',
                title: 'Grupo Material/Serviço',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'unidadeFornecimento',
                title: 'Unidade Fornecimento',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'quantidade',
                title: 'Quantidade',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'valorUnitario',
                title: 'Valor Unitário',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'valorTotal',
                title: 'Valor Total',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'valorOrcamentoExercicio',
                title: 'Valor Orçamento',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'unidadeRequisitante',
                title: 'Unidade Requisitante',
                halign: 'center',
                align: 'center',
            },
            {
                field: 'acao',
                title: 'Ações',
                halign: 'center',
                align: 'center',
                formatter: (valor, data, index) => {
                    return [
                        '<a class="excluir" href="javascript:void(0)" title="Excluir">',
                        '  <i class="fas fa-trash-alt"></i>',
                        '</a>'
                    ].join('');
                },
                events: window.operateEvents
            }
        ];

        tabelaItens.bootstrapTable({
            locale: 'pt-BR',
            height: 250,
            uniqueId: "numeroItem",
            class: "table table-sm",
            columns: colunasItens,
            showButtonText: true,
            useRowAttrFunc: true,
            reorderableRows: true,
        });
    });

    function buscarEntidade() {
        const formData = new FormData();
        formData.append('documento', cnpj.value);
        PHPSession.appendFormData(formData);
        HttpClient.post(routes.buscarEntidade, {body: formData}).then(response => {
            if (response.error) {
                alert(response.message);
                return;
            }
            orgao.value = response.data.razaoSocial;
        });
    }

    function buscarUnidades() {
        const formData = new FormData();
        formData.append('documento', cnpj.value);
        PHPSession.appendFormData(formData);
        HttpClient.post(routes.buscarUnidadesAtivas, {body: formData}).then(response => {
            if (response.error) {
                alert(response.message)
            }
            unidades = response.data.length;
            if (response.data.length === 0) {
                alert('Não foi possível identificar nenhuma unidade compradora,' +
                    ' por favor, verifique o cadastro de unidade.');
            } else if (response.data.length === 1) {
                deleteChild(codigoUnidade);
                codigoUnidade.disabled = true;
                let unidade = document.createElement('option');
                unidade.value = response.data[0].pn02_unidade;
                let unidadeNome = document.createTextNode(response.data[0].pn02_nome);
                unidade.appendChild(unidadeNome);
                codigoUnidade.appendChild(unidade);
            } else {
                mostrarUnidadesAtivas(response.data);
            }
        })
    }
    function buscaUnidadeFornecimento() {
        unidadeFornecimento.hidden = false;
        unidadeFornecimentoTexto.hidden = true;
        HttpClient.post(routes.buscarUnidades).then(response => {
            if (response.error) {
                alert(response.message)
                return;
            }
            deleteChild(unidadeFornecimento);
            mostrarUnidades(response.data);
        });
    }

    function mostrarUnidadesAtivas(unidadeCompradora) {
        unidadeCompradora.forEach((unidadeCompradora) => {
            let unidade = document.createElement('option');
            unidade.value = unidadeCompradora.pn02_unidade;
            let unidadeNome = document.createTextNode(unidadeCompradora.pn02_nome);
            unidade.appendChild(unidadeNome);
            codigoUnidade.appendChild(unidade);
        });
    }

    function mostrarUnidades(unidades) {
        unidades.forEach((unidades) => {
            let unidade = document.createElement('option');
            unidade.value = unidades.m61_codmatunid;
            let unidadeDescricao = document.createTextNode(unidades.m61_descr);
            unidade.appendChild(unidadeDescricao);
            unidadeFornecimento.appendChild(unidade);
        });
    }

    function limparCampos() {
        unidadeRequisitanteTexto.value = '';
        catalogoMaterialServico.value = '1';
        descricaoMaterial.value = '';
        codigoMaterial.value = '';
        unidadeFornecimentoTexto.value = '';
        quantidade.value = '';
        valorUnitario.value = '';
        valorTotal.value = '';
        valorOrcamento.value = '';
        dataDesejada.value = '';
        categoriaItem.value = '0';
    }

    function verificaIntegracaoAtiva() {
        catalogoMaterialServico.value = '1';
        catalogoMaterialServico.disabled = true;
        const formData = new FormData();
        formData.append('documento', cnpj.value);
        PHPSession.appendFormData(formData);
        HttpClient.post(routes.verificaIntegracao, {body: formData}).then(response => {
            if (response.error) {
                alert(response.message);
                return;
            }
            if (response.data === null) {
                alert("Por favor, verifique a configuração de integração com o PNCP!");
                btnSalvar.disabled = true;
                btnAdicionar.disabled = true;
                return;
            }
            buscarEntidade();
            buscarUnidades();
            buscaUnidadeFornecimento();
        });
    }

    function deleteChild(campo) {
        var child = campo.lastElementChild;
        while (child) {
            campo.removeChild(child);
            child = campo.lastElementChild;
        }
    }

    function validarCampos() {
        if (codigoUnidade.value === '0') {
            alert('Selecione uma Unidade Compradora');
            return false;
        }
        if (anoPCA.value === '') {
            alert('Preencha o Ano do Plano de Contratação.');
            return false;
        }
        if (unidadeEcidadeRequisitante.value === 'nao' && unidadeRequisitanteTexto.value === '' ) {
            alert('Preencha a Unidade Requisitante.');
            return false;
        }
        if (unidadeEcidadeRequisitante.value === 'sim' && codigoDepartamento.value === '') {
            alert('Preencha a Unidade Requisitante.');
            return false;
        }
        if (codigoMaterial.value === '') {
            alert('Preencha o Código do Material/Serviço.');
            return false;
        }
        if (categoriaItem.value === '0') {
            alert('Preencha a categoria do item do PCA.');
            return false;
        }
        if (unidadeEcidade.value === 'nao' && unidadeFornecimentoTexto.value === '') {
            alert('Preencha a Unidade de Fornecimento.');
            return false;
        }
        if (quantidade.value === '') {
            alert('Preencha a quantidade.');
            return false;
        }
        if (valorUnitario.value === '') {
            alert('Preencha o Valor Unitário.');
            return false;
        }
        if (valorTotal.value === '') {
            alert('Preencha o Valor Total.');
            return false;
        }
        if (valorOrcamento.value === '') {
            alert('Preencha o Valor do Orçamento.');
            return false;
        }
        if (dataDesejada.value === null) {
            alert('Preencha a Data Desejada.');
            return false;
        }
        if (dataDesejada.__toLocaleDateString().slice(-4) !== anoPCA.value) {
            alert('Data Desejada para o Item é diferente ao Ano do PCA.');
            return false;
        }
        return true;
    }

    function listenerValor(e) {
        const target = e.target;
        target.value = target.value.replace(/[^\d,]/g, '');
        let [inteiro, decimal] = target.value.split(',');

        if (inteiro.replaceAll('.', '').length > 17) {
            inteiro = inteiro.substring(0, inteiro.length - 1);
        }

        inteiro = inteiro.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        target.value = inteiro;

        if (decimal !== undefined) {
            decimal = decimal.length > 4 ? decimal.substring(0, 4) : decimal;
            target.value = `${inteiro},${decimal}`;
        }
    }

</script>
</body>
</html>
