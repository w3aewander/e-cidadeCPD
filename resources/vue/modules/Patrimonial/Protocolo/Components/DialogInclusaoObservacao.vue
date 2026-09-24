<script setup>
import { ref, watch } from "vue";
import Dialog from 'primevue/dialog';
import Textarea from "primevue/textarea";
import Button from "primevue/button";

const props = defineProps({
    modalShow: {
        type: Boolean,
        default: false
    },
    reenviarSolicitacao: Function,
    solicitacao: Object,
    observacao: String
});

const maxCaracteres = 500;
const localObservacao = ref(props.observacao);

const emit = defineEmits(['update:observacao', 'update:visible', 'clearSelectedSolicitacao'])

const enviar = () => {
    props.reenviarSolicitacao();
    closeModal();
}

const closeModal = () => {
    emit('update:visible', false);
    emit('clearSelectedSolicitacao');
    localObservacao.value = '';
}

watch(localObservacao, (newValue) => {
    emit('update:observacao', newValue);
});
</script>

<template>
    <Dialog
        header="Inclusão de Observação"
        :visible="modalShow"
        :style="{ width: '50rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        @update:visible="closeModal"
        modal
    >
        <div class="textarea-container">
            <label class="label">Deseja incluir uma observação?</label>

            <Textarea
                v-model="localObservacao"
                auto-resize
                :maxlength="maxCaracteres"
                class="textarea"
            />

            <small v-if="localObservacao.length !== 0" style="align-self: flex-end; margin: -18px 10px 5px 0;">
                {{ localObservacao.length }} / {{ maxCaracteres }}
            </small>
        </div>

        <div class="action-buttons-container">
            <Button label="Cancelar" severity="danger" @click="closeModal" rounded />
            <Button label="Enviar" @click="enviar" rounded />
        </div>
    </Dialog>
</template>

<style scoped>
.textarea-container {
    display: flex;
    flex-direction: column;
}

.label {
    margin-top: 20px;
    font-size: 1.2rem;
}

.textarea {
    width: 100%;
    margin-top: 10px;
    padding: 15px 15px 20px 15px;
    border-color: #979797;
    border-radius: 10px;
}

.observacoes-container span {
    color: #D13438;
}

.action-buttons-container {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}
</style>
