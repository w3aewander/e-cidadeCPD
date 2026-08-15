<script setup>
import Dialog from 'primevue/dialog';

const props = defineProps({
    dadosModal: {
        type: Object,
        default: () => ({
            titulo: 'Justificativa Rejeição',
            conteudo: ''
        })
    },
    modalShow: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <Dialog
        :visible="modalShow"
        :header="dadosModal.titulo"
        :style="{ width: '50rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        maximizable
        modal
    >
        <p style="margin-top: 30px;">
            <div>
                {{ dadosModal.conteudo }}
            </div>
        </p>
    </Dialog>
</template>
