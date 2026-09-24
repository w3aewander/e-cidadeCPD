<script setup>
import { ref, watch } from "vue";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";

const props = defineProps({
    assinarEcidade: Function,
    assinarA3: Function,
    assinadorAtivo: Boolean,
    exibirProgresso: Boolean,
    certificates: Array,
    certificadoSelecionado: {
        type: [String, null],
        default: null
    },
    selectedDocumentos: Array,
    modalInclusaoJustificativa: Boolean,
});

const emit = defineEmits(['update:certificadoSelecionado', 'update:modalInclusaoJustificativa']);

const localCertificadoSelecionado = ref(props.certificadoSelecionado);
const localModalInclusaoJustificativa = ref(props.modalInclusaoJustificativa);

watch(localCertificadoSelecionado, (newValue) => {
    emit('update:certificadoSelecionado', newValue);
});

watch(localModalInclusaoJustificativa, (newValue) => {
    emit('update:modalInclusaoJustificativa', newValue);
});

watch(() => props.modalInclusaoJustificativa, (newValue) => {
    localModalInclusaoJustificativa.value = newValue;
});

const showModalInclusaoJustificativa = () => {
    if (props.selectedDocumentos.length === 0) {
        return alert("Selecione ao menos uma solicitação para rejeitar!");
    }

    localModalInclusaoJustificativa.value = true;
}
</script>

<template>
    <section class="section-actions">
        <div style="display: flex;justify-content: center;align-items: center; width: 100%; margin-bottom: 10px">
            <span style="display: flex; align-items: center; font-size: 1.2rem;">
                <b v-if="!assinadorAtivo">Assinador A3 desconectado</b>
                <b v-if="assinadorAtivo">Assinador A3 conectado</b>
                <i class="pi pi-power-off"
                 :style="{'font-size': '15px', 'color':assinadorAtivo ? 'green' : 'red', 'margin-left': '5px'}"
                ></i>
            </span>
        </div>

        <div class="action-buttons-container">
            <Button
                label="Assinar com E-cidade"
                icon="pi pi-check-circle"
                :disabled="exibirProgresso"
                rounded
                @click="assinarEcidade()"
            />

            <Button
                label="Assinar com A3"
                icon="pi pi-check-circle"
                :disabled="exibirProgresso || !assinadorAtivo"
                rounded
                @click="assinarA3()"
            />

            <Button
                label="Rejeitar"
                icon="pi pi-times-circle"
                severity="danger"
                rounded
                @click="showModalInclusaoJustificativa()"
            />
        </div>

        <div style="display: flex; justify-content: center; align-items: center; width: 100%; margin-top: 10px">
            <Dropdown
                :options="certificates"
                v-model="localCertificadoSelecionado"
                placeholder="Selecione um certificado"
                style="width: 250px"
                v-if="assinadorAtivo"
            />
        </div>
    </section>
</template>

<style scoped>
.section-actions {
    width: 100%;
    margin: 20px 0;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

.action-buttons-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}
</style>
