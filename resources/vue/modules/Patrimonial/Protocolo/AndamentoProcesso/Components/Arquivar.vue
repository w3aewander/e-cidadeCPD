<script setup>

import ModalLoading from "@modules/Components/ModalLoading.vue";
import {ref} from "vue";
import {useToast} from "primevue/usetoast";
import {useConfirm} from "primevue/useconfirm";
import Swal from "sweetalert2";

const toast = useToast();
const props = defineProps(['processos']);
const emit = defineEmits(['closeDialogArquivar', 'attProcessos']);
const showDialod = ref(false);
const motivoArquivamento = ref('');
const textLoad = ref('');
const modalLoading = ref(false);
const confirm = useConfirm();

const confirmArquivar = () => {
    confirm.require({
        group: 'templating',
        message: `Deseja arquivar ${props.processos.length} processos?`,
        header: 'Atenção',
        icon: 'pi pi-exclamation-triangle',
        acceptIcon: 'pi pi-check',
        rejectIcon: 'pi pi-times',
        acceptClass: 'borda-arredondada',
        rejectClass: 'p-button-secondary p-button-outlined borda-arredondada',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Arquivar',
        accept: () => arquivar(),
        reject: () => {}
    });
};

const arquivar = async () => {
    if (motivoArquivamento.value === '') {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Preencha o campo motivo.', life: 3000});
        return false;
    }

    textLoad.value = "Arquivando Processo...";
    modalLoading.value = true;

    const parametros = {};
    parametros.codigo = props.processos;
    parametros.motivo = motivoArquivamento.value;

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/arquivar`,
            parametros
        );

        var processos = resp.data.data;
        if (processos.arquivados.length > 0) {
            toast.add({severity: 'success', summary: 'Processos Arquivados', detail: `${processos.arquivados.length} processos arquivados!`});
        }

        if (processos.erros.length > 0) {
            processos.erros.forEach((elemento) => {
                toast.add({severity: 'error', summary: 'Falha ao arquivar', detail: `Processo ${elemento.processo.numero} ---> ${elemento.mensagem}`});
            })
        }

        motivoArquivamento.value = '';
        modalLoading.value = false;
        emit('closeDialogArquivar');
        emit('attProcessos');
    } catch (e) {
        console.log('erro' + e);
        if (e.response.data.error) {
            await Swal.fire(
                'Atenção',
                e.response.data.message,
                "warning"
            );
        }
        modalLoading.value = false;
    }
}
</script>

<template>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />
    <div class="corpo">
        <div class="container" style="display: grid;">
            <label for="motivo">Motivo</label>
            <Textarea id="motivo" v-model="motivoArquivamento" variant="filled" rows="5" cols="30" style="width:100%"/>
        </div>
        <div class="container" style="text-align: center;">
            <Button
                @click="$emit('closeDialogArquivar')"
                label="Cancelar"
                class="mr-4"
                iconPos="right"
            >
                <template #icon>
                    <i style="margin-right: 10px" class="pi pi-times"></i>
                </template>
            </Button>
            <Button
                severity="warning"
                @click="confirmArquivar"
                label="Arquivar"
                iconPos="right"
            >
                <template #icon>
                    <i style="margin-right: 10px" class="pi pi-inbox"></i>
                </template>
            </Button>
        </div>
    </div>
</template>

<style scoped>

</style>
