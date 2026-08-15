<script setup>
import {onMounted, ref} from "vue";

const showDialod = ref(false);
const processosRecebimento = ref([]);
const emit = defineEmits([
    'closeDialog',
]);

onMounted(() => {
})

const closeDialog = (e) => {
    showDialod.value = false;
}

const openDialog = (processos) => {
    showDialod.value = true;
    processosRecebimento.value = processos;
}

const emiteDocRebimento = (processo) => {
    var codigoTransferencia = processo.codigoTransferencia ?? processo.transferencia;
    var url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    url = url.replace('//w', '/w');
    url = `${url}pro4_termorecebimento.php?codtran=${codigoTransferencia}`;
    window.open(url,'','location=0');
}



const recebaProcesso = () => {
    closeDialog();
    emit('closeDialog', true, processosRecebimento.value);
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
        :modal="true"
        :closable="true"
    >
        <div class="corpo">
            <i class="pi pi-exclamation-triangle icon-warning"></i>
            <div style="text-align: center;">
                Atenção, foi identificado que, no(s) processo(s) selecionado(s) para recebimento, há outros processos ligados à mesma transferência.
                <br>
                Logo, ao recebê-lo(s), os outros processos também serão recebidos.
            </div>
            <div style="text-align: center;">Abaixo, a lista dos processos que possuem outros processos ligados à mesma transferência.</div>
            <div class="processos-list">
                <div class="processo" v-for="processo in processosRecebimento">
                    <div class="numero-processo">{{processo.numero ?? processo.processo}}</div>
                    <div class="visualizar-proc" @click="emiteDocRebimento(processo)">Ver Processos</div>
                </div>
            </div>
            <div class="btns-acoes">
                <Button
                    label="Receber"
                    style="height: 35px"
                    @click="recebaProcesso"
                />
                <Button
                    label="Cancelar"
                    style="height: 35px"
                    @click="closeDialog"
                />
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
    .corpo {
        width: 800px;
        height: 350px;
        padding: 20px;
        display:flex;
        flex-direction: column;
    }

    .processo {
        display:flex;
        flex-direction: row;
        gap:10px
    }

    .processos-list {
        display:flex;
        flex-direction: column;
        gap:10px;
        margin-top: 20px
    }

    .numero-processo {
        align-self: center;
    }

    .visualizar-proc:hover {
        color:blue;
        cursor:pointer;
    }

    .btns-acoes {
        display:flex;
        flex-direction: row;
        gap:10px;
        flex-grow: 1;
        align-items: flex-end;
        justify-content: center;
    }

    .icon-warning {
        font-size: 6rem;
        text-align: center;
        color: #ffcc00;
        margin-bottom: 10px;
    }
</style>
