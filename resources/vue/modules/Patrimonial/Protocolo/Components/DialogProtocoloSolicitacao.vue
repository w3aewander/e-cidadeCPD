<script setup>
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Timeline from 'primevue/timeline';
import DialogInclusaoObservacao from "@modules/Patrimonial/Protocolo/Components/DialogInclusaoObservacao.vue";

const props = defineProps({
    solicitacao: Object,
    cancelarSolicitacao: Function,
    reenviarSolicitacao: Function,
});

const emit = defineEmits(['update:observacao']);

const dialogVisible = ref(false);
const observacao = ref('');
const showDialog = ref(false);

const reenviar = () => {
    emit('update:observacao', observacao.value);

    props.reenviarSolicitacao({
        solicitacao: props.solicitacao.solicitacao,
        documento: props.solicitacao.solicitacao.documento
    });
}

const openDialog = () => {
    dialogVisible.value = true;
}

const closeDialog = () => {
    dialogVisible.value = false;
}

const cancelarSolicitacao = (solicitacao_id) => {
    props.cancelarSolicitacao(solicitacao_id);
    closeDialog();
}

const formatDate = (data) => {
    let date = new Date(data);

    return date.toLocaleString("pt-BR", {
        day: "2-digit",
        month: "2-digit",
        year: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
    }).replace(",", " ");
}

defineExpose({ closeDialog, openDialog });
</script>

<template>
    <DialogInclusaoObservacao
        v-model:visible="showDialog"
        :reenviarSolicitacao="reenviar"
        :observacao="observacao"
        :modalShow="showDialog"
        @update:observacao="(newValue) => observacao = newValue"
    />

    <Dialog
        header="Detalhes Solicitação"
        :modal="true"
        :closable="true"
        :visible="dialogVisible"
        :style="{ maxWidth: '60%' }"
        @update:visible="closeDialog"
    >
        <div class="infos-container">
            <div class="info-assinante-container">
                <span><b>Nome: </b>{{ solicitacao.solicitacao.cgm_assinante.z01_nome }}</span>
                <span><b>CGM: </b> {{ solicitacao.solicitacao.cgm_assinante.z01_numcgm }}</span>
                <span><b>CPF/CNPJ: </b> {{ solicitacao.solicitacao.cgm_assinante.z01_cgccpf }}</span>
                <span><b>Documento: </b> {{ solicitacao.solicitacao.documento.p01_descricao }}</span>
            </div>

            <span style="font-size: 1.2rem;">Histórico:</span>

            <Timeline :value="solicitacao.solicitacoesRelacionadas">
                <template #marker="slotProps">
                    <span :class="[
                        'icone-historico',
                        slotProps.item.data_assinatura ? 'fundo-verde' :
                        slotProps.item.data_rejeicao ? 'fundo-vermelho' : 'fundo-laranja'
                    ]">
                        <i
                            style="margin-top: 1px;"
                            :class="[
                                'pi',
                                slotProps.item.data_assinatura ? 'pi-check' :
                                slotProps.item.data_rejeicao ? 'pi-times' : 'pi-ellipsis-h'
                            ]"
                        ></i>
                    </span>
                </template>

                <template #content="slotProps">
                    <div class="info-solicitacao-container">
                        <span style="font-size: 1.3rem;">
                            {{ slotProps.item.data_assinatura ? 'Assinado'
                            : slotProps.item.data_rejeicao ? 'Rejeitado' : 'Solicitado' }}
                        </span>

                        <span v-if="slotProps.item.data_assinatura" class="data-historico">
                            {{ formatDate(slotProps.item.data_assinatura) }}
                        </span>

                        <span v-if="slotProps.item.data_rejeicao" class="data-historico">
                            {{ formatDate(slotProps.item.data_rejeicao) }}
                        </span>

                        <span v-if="slotProps.item.data_rejeicao">
                            <b>Justificativa: </b> {{ slotProps.item.justificativa_rejeicao }}
                        </span>

                        <span
                            v-if="!slotProps.item.data_assinatura && !slotProps.item.data_rejeicao"
                            class="data-historico"
                        >
                            {{ formatDate(slotProps.item.created_at) }}
                        </span>

                        <span v-else>
                            <b>Solicitado em: </b> {{ formatDate(slotProps.item.created_at) }}
                        </span>

                        <span v-if="slotProps.item.observacao">
                            <b>Observação: </b> {{ slotProps.item.observacao }}
                        </span>
                    </div>
                </template>
            </Timeline>
        </div>

        <div
            v-if="!solicitacao.solicitacao.data_assinatura && !solicitacao.solicitacao.data_rejeicao"
            class="btn-container"
        >
            <Button
                label="Cancelar Solicitação"
                rounded
                @click="cancelarSolicitacao(solicitacao.solicitacao.id)"
            ></Button>
        </div>

        <div v-if="solicitacao.solicitacao.data_rejeicao" class="btn-container">
            <Button
                label="Reenviar Solicitação"
                rounded
                @click="showDialog = true"
            ></Button>
        </div>
    </Dialog>
</template>

<style scoped>
.infos-container {
    padding-top: 15px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 15px;
}

.info-assinante-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 10px;
}

.info-assinante-container span {
    font-size: 1.3rem;
}

.icone-historico {
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: red;
    color: white;
}

.fundo-verde {
    background-color: #287628;
}

.fundo-vermelho {
    background-color: #9b1313;
}

.fundo-laranja {
    background-color: #db4c13;
}

.info-solicitacao-container {
    padding: 3px 0 25px 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 5px;
}

.data-historico {
    margin-bottom: 8px;
    color: #707070;
}

.info-solicitacao-container span {
    font-size: 1.2rem;
}

.p-dialog .p-dialog-header .p-dialog-header-icon:focus {
    outline: 0 none !important;
    outline-offset: 0 !important;
    box-shadow: none !important;
}

.p-link:focus {
    outline: 0 none !important;
    outline-offset: 0 !important;
    box-shadow: none !important;
}

.btn-container {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.2rem;
}

:deep(.p-timeline-event-opposite) {
    flex: none !important;
}

:deep(.p-timeline.p-timeline-vertical .p-timeline-event-opposite) {
    padding: 0 !important;
}

:deep(.p-dialog .p-dialog-content) {
    padding: 0 !important;
}
</style>
