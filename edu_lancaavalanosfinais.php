<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));

function buscaMinMax(){
  $escola = db_getsession("DB_coddepto");
  $sql = pg_query("SELECT ed37_i_codigo, ed37_c_tipo, ed37_i_menorvalor, ed37_i_maiorvalor FROM formaavaliacao WHERE ed37_i_escola = {$escola} AND ed37_c_tipo = 'NOTA' ORDER BY ed37_i_codigo");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}
$vn = buscaMinMax();
$nmin = $vn["ed37_i_menorvalor"];
$nmax = $vn["ed37_i_maiorvalor"];

$clrotulo = new rotulocampo;
$clrotulo->label("ed61_i_aluno");
$clrotulo->label("ed47_i_codigo");
$clrotulo->label("ed47_v_nome");
$db_opcao = 1;

$iEscola = db_getsession("DB_coddepto");
$user = db_getsession("DB_id_usuario");
?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?
    db_app::load("scripts.js,
                  prototype.js,
                  strings.js,
                  arrays.js,
                  windowAux.widget.js,
                  datagrid.widget.js,
                  dbmessageBoard.widget.js,
                  dbcomboBox.widget.js,
                  dbtextField.widget.js,
                  datagrid/plugins/DBOrderRows.plugin.js,
                  datagrid/plugins/DBHint.plugin.js,
                  AjaxRequest.js");

    db_app::load("estilos.css,
                  grid.style.css"
                );
    ?>
    <script language='JavaScript' type='text/javascript' src='scripts/widgets/DBToggleList.widget.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaCalendario.classe.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaTurma.classe.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaPeriodoAvaliacao.classe.js'></script>
    <script language='JavaScript' type='text/javascript' src='scripts/classes/educacao/escola/ListaDisciplinas.classe.js'></script>
  </head>
  <body style='margin-top: 25px' bgcolor="#cccccc">
  







    <div class="container">
        <form method="post" action="edu_lancaavalgradeanosfinais.php">
            <fieldset>
                <legend>Diário de Classe - Lançamento de Avaliações</legend>
                <table class="form-container">
                    <?php /* ?>
                    <tr>
                        <td class="field-size5">
                            <label>Seleção:</label>
                        </td>
                        <td>
                            <select id="tipoSelecao">
                                <option value="1" selected>Multiplas Disciplinas</option>
                                <option value="2" >Por Disciplina</option>
                            </select>
                        </td>
                    </tr>
                    <?php */ ?>
                    <tr>
                        <td class="field-size5">
                            <label>Selecione o Calendário:</label>
                        </td>
                        <td>
                            <select id="listaCalendarios" name="xcalendario" onchange="verifica()">
                                <option value="" selected>Selecione a Calendario</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field-size5">
                            <label>Selecione a Turma:</label>
                        </td>
                        <td>
                            <select id="listaTurmas" name="xturma" onchange="verifica()">
                                <option value="" selected>Selecione a Turma</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="listaEtapas">
                        <td class="field-size5">
                            <label>Etapas:</label>
                        </td>
                        <td class="field-size-max">
                            <select id="cboEtapas" name="xetapa" onchange="verifica()">
                                <option value="">Selecione a Etapa</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field-size5">
                            <label>Selecione o Período de Avaliação:</label>
                        </td>
                        <td id="listaPeriodos" class="field-size-max">
                        </td>
                    </tr>
                    

                    <tr id="linhaDisciplina" style="display: table-row;">
                        <td class="field-size5">
                            <label>Selecione a Disciplina:</label>
                        </td>
                        <td class="field-size-max">
                            <select id="unicaDisciplina" name="xdisciplina" onchange="verifica()">
                                <option value="">Selecione uma Disciplina</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="linhaRegente" style="display: table-row">
                        <td class="field-size5">
                            <label>Selecione o Regente:</label>
                        </td>
                        <td class="field-size-max">
                            <select id="regente" name="xregente" onchange="verifica()">
                                <option value="">Selecione o Regente</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <fieldset id="fieldsetDisciplinas" class='separator' style="display: none;">
                    <legend>Disciplinas</legend>
                    <div id='listaDisciplinas' style="padding-left: 19%;"></div>
                </fieldset>

                <table class="form-container" style="visibility: hidden;">
                    <tr>
                        <td colspan="2">
                            <fieldset class="separator">
                                <legend>Configuração do Relatório</legend>
                                <table>
                                    <tr>
                                        <td>
                                            <label>Selecione um modelo:</label>
                                        </td>
                                        <td class="field-size-max">
                                            <select id="listaModelos" onchange="liberaFiltrosPorModelo();">
                                                <option value="2">Modelo 1 - Uma disciplina por página (Área)</option>
                                                <option value="4" disabled="disabled">Modelo 2 - Todas disciplinas em uma
                                                    página (Currículo)
                                                </option>
                                                <option value="3">Modelo 3 - Duas páginas por disciplina (Página 1 -
                                                    Presenças / Página 2 - Avaliações)
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    
                    
                    
                    
                    
                </table>
            </fieldset>
            <input type="hidden" value=<?php echo $iEscola ?> id="iEscola">
            <input type="submit" value="Lançar" disabled id="btnlancamento">
            <?php if(db_getsession("DB_id_usuario") == 1) : ?>
                <input type="submit" value="(Desenvolvimento)Lançar">
            <?php endif; ?>
            <!--<input type="button" id="imprimir" name="imprimir" value="Imprimir" onclick="validaDados();">-->
        </form>
    </div>
    <!--<script rel="script" type="text/javascript" src="scripts/session.js"></script>-->


<script>
    const user = '<?php echo $user; ?>';
    const tipoSelecao = 2;//$('tipoSelecao');
    const MENSAGENS_DIARIO_CLASSE_NOVO = 'educacao.escola.edu2_diarioclassenovo001.';

    var aColunas = document.getElementsByName('colunas');
    var lTemDisciplinaGlobal = false;
    var iEscola = $F("iEscola");
    var oPeriodo = new DBViewFormularioEducacao.ListaPeriodoAvaliacao();
    oPeriodo.somentePeriodoCalculaCargaHoraria(true);
    var oDisciplina = new DBViewFormularioEducacao.ListaDisciplinas();
    var sRpc = "edu4_turmas.RPC.php";

    // oTurma.show($('listaTurmas'));
    oPeriodo.show($('listaPeriodos'));
    oDisciplina.show($('listaDisciplinas'));

    for (var oElemento in aColunas) {

        aColunas[oElemento].checked = false;
        aColunas[oElemento].disabled = true;

        if (
            aColunas[oElemento].id == "exibirAvaliacoes" ||
            aColunas[oElemento].id == "exibirTotalFaltas" ||
            aColunas[oElemento].id == "exibirDataPeriodo" ||
            aColunas[oElemento].id == "exibirAlunos" ||
            aColunas[oElemento].id == "exibirAulasDadas"
        ) {
            aColunas[oElemento].checked = true;
            aColunas[oElemento].disabled = false;
        }
        if (aColunas[oElemento].id == "pautaUnica") {
            aColunas[oElemento].disabled = false;
        }
    }

    

    
    


    function functionChangeTurma() {

        var oTurmaSelecionada = document.querySelector("#listaTurmas").value

        $('cboEtapas').options.length = 0;
        $('cboEtapas').add(new Option('Selecione uma Etapa', ''));

        js_divCarregando('Buscando etapas, aguarde...', 'msgBox');
        var oAjaxRequest = new AjaxRequest(
            'edu_educacaobase.RPC.php', {
                'exec': 'pesquisaEtapa',
                'turma': oTurmaSelecionada,
            },
            (response) => {
                const etapas = [];
                response.dados.map((etapa) => {
                    $('cboEtapas').add(new Option(etapa.ed11_c_descr.urlDecode(), etapa.ed11_i_codigo));
                    etapas.push(etapa.ed11_i_codigo);
                });

                if (response.dados.length === 1) {
                    $('cboEtapas').value = response.dados[0].ed11_i_codigo;
                    $('cboEtapas').dispatchEvent(new Event('change'));
                } else {
                    $('cboEtapas').add(new Option("TODAS ETAPAS", etapas.join(',')));
                }
            }
        );
        js_removeObj('msgBox')
        oAjaxRequest.execute();
    }

    $('cboEtapas').addEventListener('change', (event) => {

        if ($F('cboEtapas') === "") {
            return;
        }
        const etapaSelecionada = $F('cboEtapas').split(',')[0];

        validaTurma(document.querySelector('#listaTurmas').value, etapaSelecionada);
        //oPeriodo.getPeriodos(document.querySelector('#listaTurmas').value, etapaSelecionada, 2); //1 para tudo
        oPeriodo.getPeriodos(document.querySelector('#listaTurmas').value, etapaSelecionada, 99); //Apenas bimestres e recuperação
    });

    $('listaPeriodos').addEventListener('change', (event) => {
        oPeriodoSelecionado = oPeriodo.getSelecionado().iCodigo;

        

        escreveDataDoBimestre(oPeriodoSelecionado, document.querySelector('#listaTurmas').value);
        verifica();
    })

    function escreveDataDoBimestre(icodigo, iturma) {
        var oParametro = new Object();
        oParametro.exec = 'getPeriodoCalendario';
        oParametro.iCodigo = icodigo;
        oParametro.iTurma = iturma;

        var oDadosRequisicao = new Object();
        oDadosRequisicao.method = 'post';
        oDadosRequisicao.parameters = 'json=' + Object.toJSON(oParametro);
        oDadosRequisicao.onComplete = retornoDataDoBimestre;

        js_divCarregando(_M(MENSAGENS_DIARIO_CLASSE_NOVO + "validando_turma"), "msgBox");
        new Ajax.Request(sRpc, oDadosRequisicao);
    }

    var dataFim = "";
    var dataInicio = "";

    function retornoDataDoBimestre(oResponse) {
        js_removeObj("msgBox");
        var oRetorno = JSON.parse(oResponse.responseText);

        oRetorno.aPeriodos.each(function(oPeriodo) {

            document.getElementById("avisoTempoBimestre").innerHTML = "Para REIMPRESSÃO de Pauta, digite a data em que ocorreu a 1ª impressão. <br> Essa data de corte deverá estar compreendida entre <strong> " + oPeriodo.sDataInicio + "</strong> e <strong>" + oPeriodo.sDataFim + "</strong>";
            dataFim = oPeriodo.sDataFim;
            dataInicio = oPeriodo.sDataInicio;
        });
    }

    /**
     * Buscas as seguintes informações da turma:
     *   -> lTipoEja             - Verifica se a turma é do tipo EJA
     *   -> lTemDisciplinaGlobal - Verifica se o controle de frequência da turma é individual ou globalizada
     * @param  {integer} iTurma
     * @param  {integer} iEtapa
     */
    function validaTurma(iTurma, iEtapa) {

        var oParametro = new Object();
        oParametro.exec = 'getInformacoesTurma';
        oParametro.iTurma = iTurma;
        oParametro.iEtapa = iEtapa;

        var oDadosRequisicao = new Object();
        oDadosRequisicao.method = 'post';
        oDadosRequisicao.parameters = 'json=' + Object.toJSON(oParametro);
        oDadosRequisicao.onComplete = retornoValidaTurma;

        js_divCarregando(_M(MENSAGENS_DIARIO_CLASSE_NOVO + "validando_turma"), "msgBox");
        new Ajax.Request(sRpc, oDadosRequisicao);
    }

    /**
     * Verifica se o tipo da turma é igual a EJA e libera modelo de relatório  "Turma EJA"
     * Verifica se a frequência da turma é individual ou globalizada e busca as disciplinas
     * @param  {Object} oResponse
     */
    function retornoValidaTurma(oResponse) {

        js_removeObj("msgBox");
        var oRetorno = JSON.parse(oResponse.responseText);

        $('listaModelos').options[1].disabled = true;
        $('listaModelos').options[1].selected = false;

        lTemDisciplinaGlobal = false;

        if (oRetorno.lFrequenciaGlobal) {

            lTemDisciplinaGlobal = true;
            $('listaModelos').options[1].disabled = false;
        }

        buscaDisciplina(oRetorno.iTurma, oRetorno.iEtapa);
    }

    /**
     * Busca as disciplinas da turma.
     * Foi alterado, para buscar todas disciplinas da turma, não importando mais se a disciplina não controla frequência
     * @param  {integer} iTurma
     * @param  {integer} iEtapa
     */
    function buscaDisciplina(iTurma, iEtapa) {

        oDisciplina.clear();
        oDisciplina.setSomenteDisciplinasGlobais(false);
        oDisciplina.setCallBackLoad(function() {
            populaComboUnicaDisciplina(oDisciplina.regencias);
        })
        oDisciplina.getDisciplinas(iTurma, iEtapa, false);

    }

    

    


    function formatarData(dataString) {
        var partesData = dataString.split("/");
        var dataFormatada = new Date(partesData[2], partesData[1] - 1, partesData[0]);
        return dataFormatada;
    }

    

    /**
     * Limpa os campos período, turma e disciplina
     */
    function limpaElementos() {
        oPeriodo.limpaElemento();
        oDisciplina.clear();
        document.querySelector('#listaTurmas').options.length = 0
        document.querySelector('#listaTurmas').add(new Option('Selecione uma Turma', ''));


        $('cboEtapas').options.length = 0;
        $('cboEtapas').add(new Option('Selecione uma Etapa', ''));
    }

    
    /*
    $('tipoSelecao').addEventListener('change', function(event) {
        tipoSelecaoDisciplina(event.target.value);
    });

    tipoSelecaoDisciplina = (tipo) => {
        if (tipo == 1) {
            $('linhaDisciplina').style.display = 'none';
            $('linhaRegente').style.display = 'none';
            $('fieldsetDisciplinas').style.display = 'block';
        }
        if (tipo == 2) {
            $('linhaDisciplina').style.display = 'table-row';
            $('linhaRegente').style.display = 'table-row';
            $('fieldsetDisciplinas').style.display = 'none';
        }
    };
    */

    populaComboUnicaDisciplina = regencias => {
        const regente = $('regente');
        regente.options.length = 0;
        regente.add(new Option('Selecione o Regente', ''));

        const unicaDisciplina = $('unicaDisciplina');
        unicaDisciplina.options.length = 0;
        unicaDisciplina.add(new Option('Selecione uma Disciplina', ''));

        regencias.each(function(regencia) {
            const option = new Option(regencia.sDisciplina.urlDecode(), regencia.iDisciplina);
            option.setAttribute('lTemGradeHorario', regencia.lTemGradeHorario);
            option.setAttribute('regencia', regencia.iRegencia);
            unicaDisciplina.add(option);
        });
    };

    $('unicaDisciplina').addEventListener('change', () => {
        const regente = $('regente');
        regente.options.length = 0;
        regente.add(new Option('Selecione o Regente', ''));

        if ($F('unicaDisciplina') != '') {
            buscarRegente();
            verifica();
        }
    });

    buscarRegente = () => {
        const parametros = {
            "exec": "buscarRegentePorRegencia",
            "regencia": $('unicaDisciplina').options[unicaDisciplina.selectedIndex].getAttribute('regencia')
        };

        const regente = $('regente');
        var oAjaxRequest = new AjaxRequest('edu4_turmas.RPC.php', parametros, function(retorno, erro) {
            if (erro) {
                alert(retorno.message);
                return;
            }

            retorno.regentes.forEach(function(professor) {
                regente.add(new Option(professor.nome.urlDecode(), professor.nome.urlDecode()));
            });

            if (retorno.regentes.length == 1) {
                regente.value = retorno.regentes[0].nome.urlDecode();
                verifica();
            }
        });

        oAjaxRequest.setMessage('Buscando regentes.');
        oAjaxRequest.execute();
        
    }

    window.addEventListener('load', async () => {
        const montaSelect = async (options, elemento) => {
            let first = elemento.options[0]
            elemento.options.length = 0
            elemento.add(first)

            options.forEach(linha => {
                if(linha.nome.includes("INFANTIL") || linha.nome.includes("Infantil") || linha.nome.includes("EJA") || (!linha.nome.includes("FINAIS") && !linha.nome.includes("EJA"))){

                }else{
                    let option = new Option(linha.nome, linha.codigo)
                    elemento.add(option)
                }
                //let option = new Option(linha.nome, linha.codigo)
                //elemento.add(option)
            })
        }

        const montaSelectTurmas = async (options, elemento) => {
            let first = elemento.options[0]
            elemento.options.length = 0
            elemento.add(first)

            options.forEach(linha => {                
                let option = new Option(linha.nome, linha.codigo)
                elemento.add(option)
            });
        }

        const escola = document.querySelector('#iEscola').value;
        const selectCalendario = document.querySelector('#listaCalendarios');
        const selectTurmas = document.querySelector('#listaTurmas');
        const routes = {
            calendarios: `v4/api/educacao/escola/${escola}/calendario?validaUsuario=${user}`,
            turmas: `v4/api/educacao/escola/turmas-por-calendario`
        }

        js_divCarregando('Buscando calendários, aguarde...', 'msgBox');

        const calendarios = (await Desktop.axios.get(routes.calendarios)).data.data;

        montaSelect(calendarios, selectCalendario);
        js_removeObj('msgBox');

        selectCalendario.addEventListener('change', async () => {
            const codCalendario = calendarios.find(calendario => selectCalendario.value == calendario.codigo);
            let turmas;

            if (codCalendario) {
                js_divCarregando('Aguarde, buscando turmas', 'msgBox');
                const codigoCalendario = codCalendario.codigo;
                const response = (await CurrentWindow.axios.get(`${routes.turmas}/${codigoCalendario}?validaUsuario=${user}`));
                turmas = response.data.data;
            }

            montaSelectTurmas(turmas, selectTurmas);
            js_removeObj('msgBox');
        });

        selectTurmas.addEventListener('change', () => {
            functionChangeTurma();
        })
    })

    document.getElementById("cboPeriodoAvaliacao").name = 'xperiodo';



function verifica(){    
    if(
        document.getElementById("listaCalendarios").value == "" ||
        document.getElementById("listaTurmas").value == "" ||
        document.getElementById("cboEtapas").value == "" ||
        document.getElementById("cboPeriodoAvaliacao").value == "" ||
        document.getElementById("unicaDisciplina").value == "" ||
        document.getElementById("regente").value == "" 
        ){
        //alert("Todos os campos precisam ser selecionados.");
        document.getElementById("btnlancamento").disabled = true;
    }else{
        document.getElementById("btnlancamento").disabled = false;
    }
}




</script>










  </body>
  
</html>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
