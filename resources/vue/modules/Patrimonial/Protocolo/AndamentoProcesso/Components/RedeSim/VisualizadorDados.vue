<script setup>
import {ref} from "vue";

const showDialod = ref(false);
const dados = ref([]);
const inclusaoInscricao = ref(false);

const openDialog = (jsonInfo, inclusao) => {
    dados.value = jsonInfo;
    inclusaoInscricao.value = inclusao;
    showDialod.value = true;
}
const closeDialog = (e) => {
    showDialod.value = false;
}

defineExpose({
    closeDialog,
    openDialog
});
</script>

<template>
    <Dialog
        :visible="showDialod"
        @update:visible="closeDialog"
        class="p-dialog-maximized"
        header="Dados Inclusão de Inscrição "
    >
        <div id="dados-header">
            <div class="campos" style="padding-top: 15px !important;">
                <div class="campo-grupo">
                    <b>CNPJ:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.cnpj}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>N° Órgão Registro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.numeroOrgaoRegistro}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Nome Empresarial:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.nomeEmpresarial}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Cod Tipo Órgão Registro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.codTipoOrgaoRegistro}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Identificador Matriz Filial:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.identificadorMatrizFilial}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Cod Natureza Jurídica:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.codNaturezaJuridica}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Data Abertura Estabelecimento:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.dataAberturaEstabelecimento}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Data Abertura Empresa:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.dataAberturaEmpresa}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Data Inicio Atividade:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.dataInicioAtividade}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.nuProcessoOrgaoRegistro">
                    <b>N° Processo Órgão Registro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.nuProcessoOrgaoRegistro}}
                    </label>
                </div>
            </div>
        </div>
        <Fieldset
            style="margin-top:10px"
            legend="Situação Cadastral RFB"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>Código:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.situacaoCadastralRFB.codigo}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Descrição:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.situacaoCadastralRFB.descricao}}
                    </label>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Situação Cadastral Órgão Registro"
            v-if="dados.dadosRedesim.situacaoCadastralOrgaoRegistro"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>Código:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.situacaoCadastralOrgaoRegistro.codigo}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Descrição:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.situacaoCadastralOrgaoRegistro.descricao}}
                    </label>
                </div>
            </div>
        </Fieldset>
        <div id="info-optante-simples">
            <div class="campos">
                <div class="campo-grupo">
                    <b>Opção Simples Nacional:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.opcaoSimplesNacional}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="inclusaoInscricao">
                    <b>Porte:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.porte}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="inclusaoInscricao">
                    <b>Opção Simei:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.opcaoSimei}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="inclusaoInscricao">
                    <b>Tipo Unidade:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.tipoUnidade}}
                    </label>
                </div>
            </div>
        </div>
        <Fieldset
            v-if="!inclusaoInscricao && (dados.dadosRedesim.periodosSimplesNacional.periodo)"
            style="margin-top:10px"
            legend="Periodo Simples Nacional"
        >
            <div id="periodo-simples-nacional" v-for="(periodo, index) in dados.dadosRedesim.periodosSimplesNacional.periodo">
                <div class="campos">
                    <div class="campo-grupo">
                        <Badge style="margin-top: 1px;" :value="index + 1"></Badge>
                    </div>
                    <div class="campo-grupo">
                        <b>Data Inclusão:</b>
                        <label style="margin-left: 10px">
                            {{periodo.dataInclusao}}
                        </label>
                    </div>
                    <div class="campo-grupo" v-if="periodo.dataExclusao">
                        <b>Data Exclusão:</b>
                        <label style="margin-left: 10px">
                            {{periodo.dataExclusao}}
                        </label>
                    </div>
                </div>
            </div>
        </Fieldset>
        <div id="info-optante-simples-att" v-if="!inclusaoInscricao">
            <div class="campos">
                <div class="campo-grupo">
                    <b>Porte:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.porte}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Opção Simei:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.opcaoSimei}}
                    </label>
                </div>
            </div>
        </div>
        <div id="periodos-mei">
            <Fieldset
                v-if="(!inclusaoInscricao) && dados.dadosRedesim.periodosMei"
                legend="Periodos MEI"
            >
                <div id="periodos-mei-data" v-for="(periodoMei, index) in dados.dadosRedesim.periodosMei.periodo">
                    <div class="campos">
                        <div class="campo-grupo">
                            <Badge style="margin-top: 1px;" :value="index + 1"></Badge>
                        </div>
                        <div class="campo-grupo">
                            <b>Data Inclusão:</b>
                            <label style="margin-left: 10px">
                                {{periodoMei.dataInclusao}}
                            </label>
                        </div>
                    </div>
                </div>
            </Fieldset>
        </div>
        <div id="tipo-unidade-att" v-if="!inclusaoInscricao">
            <div class="campos">
                <div class="campo-grupo">
                    <b>Tipo Unidade:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.tipoUnidade}}
                    </label>
                </div>
            </div>
        </div>
        <Fieldset
            style="margin-top:10px"
            legend="Formas Atuação"
        >
            <div id="forma-atuacao" v-for="(formaAtuacao, index) in dados.dadosRedesim.formasAtuacao.formaAtuacao">
                <div class="campos">
                    <div class="campo-grupo">
                        <Badge style="margin-top: 1px;" :value="index + 1"></Badge>
                    </div>
                    <div class="campo-grupo">
                        <b>Código:</b>
                        <label style="margin-left: 10px">
                            {{formaAtuacao.codigo}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Descrição:</b>
                        <label style="margin-left: 10px">
                            {{formaAtuacao.descricao}}
                        </label>
                    </div>
                </div>
            </div>
        </Fieldset>
        <div id="info-gerais">
            <div class="campos">
                <div class="campo-grupo">
                    <b>Capital Social:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.capitalSocial}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.capitalIntegralizado">
                    <b>Capital Integralizado:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.capitalIntegralizado}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.valorQuota">
                    <b>Valor Quota:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.valorQuota}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.possuiEstabelecimento">
                    <b>Possui Estabelecimento:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.possuiEstabelecimento}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.areaTotalEdificacao">
                    <b>Área Total Edificação:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.areaTotalEdificacao}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.areaTotalUtilizada">
                    <b>Área Total Utilizada:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.areaTotalUtilizada}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.ultimaViabilidadeVinculada">
                    <b>Última Viabilidade Vinculada:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.ultimaViabilidadeVinculada}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.ultimaViabilidadeAnaliseEndereco">
                    <b>Última Viabilidade Análise Endereço:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.ultimaViabilidadeAnaliseEndereco}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.dataUltimaAnaliseEndereco">
                    <b>Data Última Analise Endereço:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.dataUltimaAnaliseEndereco}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.ultimoColetorEstadualWebVinculado">
                    <b>Último Coletor Estadual Web Vinculado:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.ultimoColetorEstadualWebVinculado}}
                    </label>
                </div>
                <div class="campo-grupo" v-if="dados.dadosRedesim.inscricaoImobiliaria">
                    <b>Inscrição Imobiliaria:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.inscricaoImobiliaria}}
                    </label>
                </div>
            </div>
        </div>
        <Fieldset
            style="margin-top:10px"
            legend="Endereço"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>CEP:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.cep}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Logradouro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.logradouro}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Cod Tipo Logradouro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.codTipoLogradouro}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>N° Logradouro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.numLogradouro}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Bairro:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.bairro}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Cod Município:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.codMunicipio}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>UF:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.endereco.uf}}
                    </label>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Contato"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>DDD:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.contato.dddTelefone1}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Telefone:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.contato.telefone1}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Correio Eletrônico:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.contato.correioEletronico}}
                    </label>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Responsável Perante CNPJ"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>Nome Responsável:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.responsavelPeranteCnpj.nomeResponsavel}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>CPF Responsável:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.responsavelPeranteCnpj.cpfResponsavel}}
                    </label>
                </div>
                <div class="campo-grupo">
                    <b>Cod Qualific Responsável:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.responsavelPeranteCnpj.codQualificResponsavel}}
                    </label>
                </div>
            </div>
            <Fieldset
                style="margin-top:10px"
                legend="Endereço Responsável"
            >
                <div class="campos">
                    <div class="campo-grupo">
                        <b>CEP:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.cep}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Logradouro:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.logradouro}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Cod Tipo Logradouro:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.codTipoLogradouro}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>N° Logradouro:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.numLogradouro}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Bairro:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.bairro}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Cod Município:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.codMunicipio}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>UF:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.enderecoResponsavel.uf}}
                        </label>
                    </div>
                </div>
            </Fieldset>
            <Fieldset
                style="margin-top:10px"
                legend="Contato Responsável"
                v-if="dados.dadosRedesim.responsavelPeranteCnpj.contato"
            >
                <div class="campos">
                    <div class="campo-grupo">
                        <b>DDD:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.contato.dddTelefone1}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Telefone:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.contato.telefone1}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Correio Eletrônico:</b>
                        <label style="margin-left: 10px">
                            {{dados.dadosRedesim.responsavelPeranteCnpj.contato.correioEletronico}}
                        </label>
                    </div>
                </div>
            </Fieldset>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Atividades Econômica"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>CNAE Fiscal:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.atividadesEconomica.cnaeFiscal.codigo}}
                    </label>
                </div>
            </div>
            <Fieldset
                style="margin-top:10px"
                legend="CNAE Secundária"
            >
                <div v-if="dados.dadosRedesim.atividadesEconomica.cnaesSecundarias" class="cnae-secundarias" v-for="(secundaria, index) in dados.dadosRedesim.atividadesEconomica.cnaesSecundarias.cnaeSecundaria">
                    <div class="campos">
                        <div class="campo-grupo">
                            <Badge style="margin-top: 1px;" :value="index + 1"></Badge>
                        </div>
                        <div class="campo-grupo">
                            <b>Código:</b>
                            <label style="margin-left: 10px">
                                {{secundaria.codigo}}
                            </label>
                        </div>
                    </div>
                </div>
            </Fieldset>
            <div class="campos">
                <div class="campo-grupo">
                    <b>Objeto Social:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.atividadesEconomica.objetoSocial}}
                    </label>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Análise Endereço"
            v-if="dados.dadosRedesim.analiseEndereco"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>Complemento:</b>
                    <label style="margin-left: 10px">
                        {{dados.dadosRedesim.analiseEndereco.complemento}}
                    </label>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Horários Funcionamento"
            v-if="dados.dadosRedesim.horariosFuncionamento"
        >
            <div id="horarios-funcionamento" v-for="(horario, index) in dados.dadosRedesim.horariosFuncionamento.horarioFuncionamento">
                <div class="campos">
                    <div class="campo-grupo">
                        <Badge style="margin-top: 1px;" :value="index + 1"></Badge>
                    </div>
                    <div class="campo-grupo">
                        <b>Dia:</b>
                        <label style="margin-left: 10px">
                            {{horario.dia}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Hora Início:</b>
                        <label style="margin-left: 10px">
                            {{horario.horaInicio}}
                        </label>
                    </div>
                    <div class="campo-grupo">
                        <b>Hora Fim:</b>
                        <label style="margin-left: 10px">
                            {{horario.horaFim}}
                        </label>
                    </div>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            style="margin-top:10px"
            legend="Sócios"
        >
            <div id="socios" v-for="(socio, index) in dados.dadosRedesim.socios.socio">
                <Fieldset
                    style="margin-top:10px"
                    :legend="index + 1"
                >
                    <div class="campos">
                        <div class="campo-grupo">
                            <b>Identificador Tipo Sócio:</b>
                            <label style="margin-left: 10px">
                                {{socio.identificadorTipoSocio}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Cnpj/Cpf Sócio:</b>
                            <label style="margin-left: 10px">
                                {{socio.cnpjCpfSocio}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Capital Social Sócio:</b>
                            <label style="margin-left: 10px">
                                {{socio.capitalSocialSocio}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Cod Qualificação Sócio:</b>
                            <label style="margin-left: 10px">
                                {{socio.codQualificacaoSocio}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Nome:</b>
                            <label style="margin-left: 10px">
                                {{socio.nome}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Nacionalidade:</b>
                            <label style="margin-left: 10px">
                                {{socio.nacionalidade}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Estado Civil:</b>
                            <label style="margin-left: 10px">
                                {{socio.estadoCivil}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Sexo:</b>
                            <label style="margin-left: 10px">
                                {{socio.sexo}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Data Nascimento:</b>
                            <label style="margin-left: 10px">
                                {{socio.dataNascimento}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Identidade:</b>
                            <label style="margin-left: 10px">
                                {{socio.identidade}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Órgao Emissor:</b>
                            <label style="margin-left: 10px">
                                {{socio.orgaoEmissor}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>UF Órgao Emissor:</b>
                            <label style="margin-left: 10px">
                                {{socio.ufOrgaoEmissor}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Profissão:</b>
                            <label style="margin-left: 10px">
                                {{socio.profissao}}
                            </label>
                        </div>
                        <div class="campo-grupo">
                            <b>Data Inclusão:</b>
                            <label style="margin-left: 10px">
                                {{socio.dataInclusao}}
                            </label>
                        </div>
                    </div>
                    <Fieldset
                        style="margin-top:10px"
                        legend="Endereço Sócio"
                    >
                        <div class="campos">
                            <div class="campo-grupo">
                                <b>CEP:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.cep}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Logradouro:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.logradouro}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Cod Tipo Logradouro:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.codTipoLogradouro}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>N° Logradouro:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.numLogradouro}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Complemento:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.complemento}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Bairro:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.bairro}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Cod Municipio:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.codMunicipio}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>UF:</b>
                                <label style="margin-left: 10px">
                                    {{socio.enderecoSocio.uf}}
                                </label>
                            </div>
                        </div>
                    </Fieldset>
                    <Fieldset
                        style="margin-top:10px"
                        legend="Contato Sócio"
                        v-if="socio.contatoSocio"
                    >
                        <div class="campos">
                            <div class="campo-grupo">
                                <b>DDD:</b>
                                <label style="margin-left: 10px">
                                    {{socio.contatoSocio.dddTelefone1}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Telefone:</b>
                                <label style="margin-left: 10px">
                                    {{socio.contatoSocio.telefone1}}
                                </label>
                            </div>
                            <div class="campo-grupo">
                                <b>Correio Eletrônico:</b>
                                <label style="margin-left: 10px">
                                    {{socio.contatoSocio.correioEletronico}}
                                </label>
                            </div>
                        </div>
                    </Fieldset>
                </Fieldset>
            </div>
        </Fieldset>
    </Dialog>
</template>

<style scoped>
.campos {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin: 5px;
    padding: 2px;
}
.campo-grupo {
    display: flex;
    flex-direction: row;
    padding: 5px;
}
.input {
    border-radius: 10px;
    height: 20px;
}
</style>
