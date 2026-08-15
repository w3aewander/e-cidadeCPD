<script setup>
import { toRefs, ref, watch, computed } from 'vue';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';

const props = defineProps({
    documento: Object,
    documentoComSolicitacoesVisiveis: Object,
    documentSelected: Array,
    solicitacoesSelected: Array,
    solicitacoesVisiveisESelecionaveis: Array,
    allSolicitacoesSelected: Boolean,
    codigoProcesso: String,
    codigoDespacho: String,
    loading: Boolean
});

const emit = defineEmits([
    'openAssinantesDocumento',
    'cancelarSolicitacoes',
    'toggleSelectAllSolicitacoes',
    'openDialogInfoAssinante',
    'update:documentSelected',
    'update:solicitacoesSelected',
    'update:loading'
]);

const ECIDADE_REQUEST_PATH = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;

const { documento } = toRefs(props);

const localDocumentSelected = ref([...props.documentSelected]);
const localSolicitacoesSelected = ref([...props.solicitacoesSelected]);
const localLoading = ref(props.loading);

const filteredSolicitacoes = computed(() => {
    const solicitacoesMap = new Map();

    documento.value.solicitacao_assinatura.forEach((solicitacao) => {
        const nome = solicitacao.cgm_assinante.z01_nome;

        if (
            !solicitacoesMap.has(nome) ||
            new Date(solicitacao.created_at) > new Date(solicitacoesMap.get(nome).created_at)
        ) {
            solicitacoesMap.set(nome, solicitacao);
        }
    });

    return Array.from(solicitacoesMap.values());
});

const cancelarSolicitacoes = () => emit('cancelarSolicitacoes');

const openDialogInfoAssinante = (solicitacao) => emit('openDialogInfoAssinante', solicitacao);

watch(localDocumentSelected, (newDocumentSelected) => {
    if (JSON.stringify(newDocumentSelected) !== JSON.stringify(props.documentSelected)) {
        emit('update:documentSelected', newDocumentSelected);
    }
});

watch(() => props.documentSelected, (newDocumentSelected) => {
    if (JSON.stringify(newDocumentSelected) !== JSON.stringify(localDocumentSelected.value)) {
        localDocumentSelected.value = [...newDocumentSelected];
    }
}, {immediate: true});

watch(localSolicitacoesSelected, (newSolicitacoesSelected) => {
    emit('update:solicitacoesSelected', newSolicitacoesSelected);
});

watch(localLoading, (newLoadingValue) => {
    emit('update:loading', newLoadingValue);
})

const toggleSelectAllSolicitacoes = () => {
    if (props.allSolicitacoesSelected) {
        localSolicitacoesSelected.value = [];
    } else {
        localSolicitacoesSelected.value = [...props.solicitacoesVisiveisESelecionaveis];
    }
}

const visualizarDocumento = async (despacho) => {
    localLoading.value = true;

    const parametros = {};
    parametros.codigoProcesso = props.codigoProcesso;
    parametros.procandamint = props.codigoDespacho;

    try {
        await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/processodocumento/documentosPorProcAndamInt`,
            parametros
        ).then(response => {
            if (response.data.error == true) {
                localLoading.value = false;
                alert(response.data.message);
                return;
            }

            var codigosEStorage = [];
            var index = 0;

            const documento = response.data.data.filter(
                (doc) => doc.sequencial === props.documento.p01_sequencial
            );

            codigosEStorage.push(documento[0].id_estorage);

            if (codigosEStorage.length == 0) {
                localLoading.value = false;
                return alert("Não foi possível localizar esse documento.");
            }

            localLoading.value = false;

            if (false) {
                window.open(`db_visualizador_documentos.php?ids=${codigosEStorage}&viewIndex=${index}`);
            } else {
                js_OpenJanelaIframe(
                    'CurrentWindow.corpo',
                    'db_visualizador_imagens',
                    `${ECIDADE_REQUEST_PATH}db_visualizador_documentos.php?ids=${codigosEStorage}&viewIndex=${index}`,
                    'Visualizar Documento',
                    true
                );
            }
        });
    } catch (e) {
        localLoading.value = false;
        alert('Erro ao carregar documento.');
    }
}

const openAssinantesDocumento = (documento) => {
    emit('openAssinantesDocumento', documento);
    localSolicitacoesSelected.value = [];
}

const handleInfoSolicitacoesHeader = (documento) => {
    if (filteredSolicitacoes.value.length !== 0) {
        const solicitacaoSingularOuPlural =
            filteredSolicitacoes.value.length === 1 ?
            ' Solicitação / ' :
            ' Solicitações / '
        ;
        const assinaturaSingularOuPlural =
            qtdSolicitacoesAssinadas(documento) === 1 ?
            ' Assinatura' :
            ' Assinaturas'
        ;
        const infoSolicitacoes =
            filteredSolicitacoes.value.length
            + solicitacaoSingularOuPlural
            + qtdSolicitacoesAssinadas(documento)
            + assinaturaSingularOuPlural
        ;

        return infoSolicitacoes;
    }

    return "Sem solicitações";
}

const qtdSolicitacoesAssinadas = (documento) => {
    const solicitacoesAssinadas = documento.solicitacao_assinatura.filter(
        (solicitacao) => solicitacao.data_assinatura
    );

    return solicitacoesAssinadas.length;
}

const todasSolicitacoesAssinadas = (documento) => {
    const solicitacoesAssinadas = qtdSolicitacoesAssinadas(documento);

    const todasSolicitacoesAssinadas = solicitacoesAssinadas === documento.solicitacao_assinatura.length;

    return todasSolicitacoesAssinadas;
}

const documentoEstaSelecionado = (documento) => {
    return props.documentSelected.includes(documento)
}

const solicitacaoEstaSelecionada = (solicitacao) => {
    return props.solicitacoesSelected.includes(solicitacao)
}
</script>

<template>
    <div
        class="card-documentos"
        :class="{'active-card': documentoEstaSelecionado(documento)}"
    >
        <div
            :class="[
                'documento-header',
                documentoComSolicitacoesVisiveis === documento ? 'card-aberto' : null
            ]"
            style="display: flex; justify-content: space-between;"
            @click="openAssinantesDocumento(documento)"
        >
            <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                <Checkbox
                    v-model="localDocumentSelected"
                    :value="documento"
                    @click.stop
                />

                <h2>{{ documento.p01_descricao }}</h2>

                <i
                    class="pi pi-file-pdf pdf-icon"
                    @click.stop="visualizarDocumento()"
                ></i>
            </div>

            <div class="qtd-assinaturas-e-seta">
                <span>{{ handleInfoSolicitacoesHeader(documento) }}</span>

                <i
                    v-if="documento.solicitacao_assinatura.length !== 0"
                    :class="[
                        'pi', 'pi-circle-fill',
                        'ponto-assinado-maior',
                        todasSolicitacoesAssinadas(documento) ? 'ponto-verde' : 'ponto-vermelho'
                    ]"
                ></i>

                <i
                    :class="{
                        'pi pi-angle-up card-arrow': documentoComSolicitacoesVisiveis !== documento,
                        'pi pi-angle-down card-arrow': documentoComSolicitacoesVisiveis === documento
                    }"
                ></i>
            </div>
        </div>

        <div
            v-if="documentoComSolicitacoesVisiveis === documento"
            class="assinantes-documento"
        >
            <div
                v-if="documento.solicitacao_assinatura.length !== 0"
                style="width: 100%; display: flex; justify-content: space-between; align-items: center;"
            >
                <h2 >Solicitações</h2>

                <div class="solicitacoes-action-buttons">
                    <Button
                        v-if="solicitacoesSelected.length !== 0"
                        class="action-button botao-menor"
                        label="Cancelar Solicitações"
                        @click.stop="cancelarSolicitacoes"
                    ></Button>

                    <Button
                        v-if="solicitacoesVisiveisESelecionaveis.length !== 0"
                        class="action-button botao-menor"
                        :label="allSolicitacoesSelected ? 'Desmarcar Todas' : 'Selecionar Todas'"
                        @click.stop="toggleSelectAllSolicitacoes"
                    ></Button>
                </div>
            </div>

            <div v-if="documento.solicitacao_assinatura.length > 0" class="cards-solicitacao-container">
                <div
                    v-for="solicitacao of filteredSolicitacoes"
                    :class="[
                        'card-solicitacao',
                        solicitacaoEstaSelecionada(solicitacao) ? 'solicitacao-selecionada' : null
                    ]"
                    @click.stop="openDialogInfoAssinante({ solicitacao, documento })"
                >
                    <Checkbox
                        v-if="!solicitacao.data_assinatura && !solicitacao.data_rejeicao"
                        v-model="localSolicitacoesSelected"
                        class="checkbox-menor"
                        :key="solicitacao.id"
                        :value="solicitacao"
                        @click.stop
                    />

                    <i
                        :class="[
                            'pi',
                            'pi-circle-fill',
                            'ponto-assinado',
                            solicitacao.data_assinatura ? 'ponto-verde'
                            : solicitacao.data_rejeicao ? 'ponto-vermelho' : 'ponto-laranja'
                        ]"
                    ></i>

                    <span>{{ solicitacao.cgm_assinante.z01_nome }}</span>
                    <b>CGM: </b> {{ solicitacao.cgm_assinante.z01_numcgm }}
                </div>
            </div>

            <p v-else style="margin: 0; font-size: 0.9rem;">
                Não possui solicitações
            </p>

            <div v-if="documento.solicitacao_assinatura.length > 0" class="legendas">
                <div class="legenda-assinatura">
                    <i class="pi pi-circle-fill ponto-assinado ponto-verde"></i>
                    <p>Assinado</p>
                </div>

                <div class="legenda-assinatura">
                    <i class="pi pi-circle-fill ponto-assinado ponto-laranja"></i>
                    <p>Aguardando assinatura</p>
                </div>

                <div class="legenda-assinatura">
                    <i class="pi pi-circle-fill ponto-assinado ponto-vermelho"></i>
                    <p>Rejeitado</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card-documentos {
    width: 100%;
    margin: 2px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    background: #ffffff;
    border-radius: 10px;
}

.active-card {
    background: #ccd8de;
}

.active-card .documento-header:hover {
    background-color: #bdd0da;
}

.documento-header {
    width: 100%;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 10px 10px 0 0;
    cursor: pointer;
}

.documento-header:hover {
    background-color: #efefef;
    border-radius: 10px;
}

.documento-header h2 {
    margin: 10px 0;
    color: var(--text-color);
    text-align: center;
}

.card-aberto {
    border-bottom: 2px solid #e9e9e9;
}

.card-aberto:hover {
    border-radius: 10px 10px 0 0;
}

.pdf-icon {
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.3rem;
    color: #5d5d5d;
    border-radius: 50%;
}

.pdf-icon:hover {
    background-color: #dedede;
}

.qtd-assinaturas-e-seta {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.qtd-assinaturas-e-seta span{
    font-size: 1.1rem;
    color: var(--text-color);
}

.ponto-assinado-maior {
    font-size: 0.7rem;
}

.ponto-assinado {
    font-size: 0.5rem;
}

.ponto-verde {
    color: #287628;
}

.ponto-laranja {
    color: #db4c13;
}

.ponto-vermelho {
    color: #9b1313;
}

.card-arrow {
    font-size: 1.7rem;
    color: var(--text-color);
}

.assinantes-documento {
    width: 100%;
    padding: 10px 15px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    background-color: #fff;
    border-radius: 0 0 10px 10px;
}

.assinantes-documento p {
    color: var(--text-color);
}

.assinantes-documento h2 {
    margin: 0;
    color: var(--text-color);
    text-align: center;
    font-size: 1rem;
}

.solicitacoes-action-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.action-button {
    border-radius: 2rem;
}

.cards-solicitacao-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 5px;
}

.card-solicitacao {
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    background: #fff;
    border: 1px solid #cacaca;
    border-radius: 1rem;
    position: relative;
    cursor: pointer;
}

.card-solicitacao:hover {
    background-color: #eeeeee;
}

.card-solicitacao b {
    color: var(--text-color);
}

.solicitacao-selecionada {
    background: #eaf1f5;
}

.solicitacao-selecionada:hover {
    background: #e0ebf2;
}

.legendas {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.legenda-assinatura {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.legenda-assinatura p {
    margin: 0;
}

:deep(.p-checkbox) {
    position: relative;
    display: flex;
    user-select: none;
    vertical-align: bottom;
    align-items: center;
}

:deep(.checkbox-menor .p-checkbox-box) {
    width: 17px;
    height: 17px;
    border-radius: 50%;
}

:deep(.checkbox-menor .p-checkbox-box .p-checkbox-icon.p-icon) {
    width: 8px !important;
    height: 8px !important;
}

:deep(.botao-menor) {
    padding: 0.3rem 1rem;
}
</style>
