<script setup>
import { ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Dialog from 'primevue/dialog';
import Textarea from "primevue/textarea";
import Button from "primevue/button";

const props = defineProps({
    modalShow: {
        type: Boolean,
        default: false
    },
    rejeitarAssinaturas: Function,
    justificativa: String
});

const maxCaracteres = 1000;
const localJustificativa = ref(props.justificativa);

const toast = useToast();

const emit = defineEmits(['update:justificativa', 'update:visible'])

const rejeitar = () => {
    if (localJustificativa.value.trim() === '') {
        return toast.add({
            severity: 'error',
            summary: 'Atenção',
            detail: 'É necessário enviar uma justificativa!',
            life: 3000
        });
    }

    props.rejeitarAssinaturas();
    closeModal();
}

const closeModal = () => {
    emit('update:visible', false);
    localJustificativa.value = '';
}

watch(localJustificativa, (newValue) => {
    emit('update:justificativa', newValue);
});
</script>

<template>
    <Dialog
        header="Justificativa"
        :visible="modalShow"
        :style="{ width: '50rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        @update:visible="closeModal"
        modal
    >
        <div class="textarea-container">
            <label class="label">Envie uma justificativa para a rejeição da assinatura:</label>

            <Textarea
                v-model="localJustificativa"
                auto-resize
                :maxlength="maxCaracteres"
                class="textarea"
            />

            <small v-if="localJustificativa.length !== 0" style="align-self: flex-end; margin: -18px 10px 5px 0;">
                {{ localJustificativa.length }} / {{ maxCaracteres }}
            </small>
        </div>

        <span style="color: #D13438">* A justificativa é obrigatória e não pode ser alterada posteriormente</span>

        <div class="action-buttons-container">
            <Button label="Cancelar" severity="danger" @click="closeModal" rounded />
            <Button label="Enviar" @click="rejeitar" rounded />
        </div>
    </Dialog>
</template>

<style scoped>
.textarea-container {
    margin-bottom: 5px;
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

.action-buttons-container {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}
</style>
