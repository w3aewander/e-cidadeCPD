<script setup>
import {ref} from "vue";
import ModalLoading from "@modules/Components/ModalLoading.vue";
import {useToast} from "primevue/usetoast";
import Swal from "sweetalert2";

const emit = defineEmits(['fecharDialogsAposTransferencia', 'attProcessos']);
const processoDados = ref();
const showDialod = ref(false);
const textLoad = ref('');
const modalLoading = ref(false);
const toast = useToast();
const instituicaoSelecionada = ref();
const instituicoes = ref([]);

const departamentoSelecionado = ref();
const departamentos = ref([]);

const usuarioSelecionado = ref();
const usuarios = ref([]);

const closeDialog = (e) => {
    showDialod.value = false;
    usuarios.value = [];
    usuarioSelecionado.value = [];
    departamentos.value = [];
    departamentoSelecionado.value = [];
    instituicaoSelecionada.value = [];
    instituicoes.velue = [];
}
const openDialog = (e) => {
    showDialod.value = true;
    buscarInstituicoes();
}

const setProcesso = function (processo) {
    processoDados.value = processo;
}

const buscarInstituicoes = async () => {
    textLoad.value = "Buscando Instituições...";
    modalLoading.value = true;

    try {
        const resp = await window.axios.get('v4/api/patrimonial/protocolo/processo/instituicoes');
        instituicoes.value = resp.data.data;
        modalLoading.value = false;
    } catch (e) {
        console.log('erro' + e);
        modalLoading.value = false;
    }
}

const buscarUsuarios = async (codDepartamento) => {
    textLoad.value = "Buscando Usuários...";
    modalLoading.value = true;

    try {
        const resp = await window.axios.get(`v4/api/patrimonial/protocolo/processo/usuarios-departamento/${codDepartamento}`);
        usuarios.value = resp.data.data;
        modalLoading.value = false;
    } catch (e) {
        console.log('erro' + e);
        modalLoading.value = false;
    }
}

const onInstituicaoSelecionada = function () {
    departamentos.value = instituicaoSelecionada.value.departamentos.filter(departamento => {
        departamento.label = departamento.sequencial + ' - ' + departamento.descricao
        return departamento.sequencial !== '0';
    });
}

const onDepartamentoSelecionado = function () {
    buscarUsuarios(departamentoSelecionado.value.sequencial);
}


const codigosProcesso = (param) => {
    if (Array.isArray(param)) {
        var arrayCodigos = [];
        param.forEach(elemento => {
            arrayCodigos.push(elemento.codigoProcesso);
        });
        return arrayCodigos.join(',');
    } else {
        return param;
    }
}

const transferir = async () => {
    if (!departamentoSelecionado.value ) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Selecione um departamento.', life: 3000});
        return false;
    }

    textLoad.value = "Transferindo Processo...";
    modalLoading.value = true;


    const parametros = {};
    parametros.departamentoDestino = departamentoSelecionado.value.sequencial;
    parametros.codigoProcesso = processoDados.value.codigo;
    parametros.codigoTransferencia = processoDados.value.transferencia;

    if (processoDados.value.codigo) {
        parametros.codigoProcesso = processoDados.value.codigo;
    } else {
        parametros.codigoProcesso = processoDados.value;
    }

    if (usuarioSelecionado.value) {
        parametros.recebimentoDestino = usuarioSelecionado.value.id_usuario;
    }

    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/processo/transferir',
            parametros
        );

        if (resp.error) {
            modalLoading.value = false;
            closeDialog();
            toast.add({severity: 'warn', summary: 'Atenção', detail: 'Erro transferir despacho. Contate o suporte!', life: 3000});
            return false;
        }

        if (resp.data.data.erro) {
            modalLoading.value = false;
            closeDialog();
            toast.add({severity: 'warn', summary: 'Atenção', detail: resp.data.data.mensagem});
            return;
        }

        var processos = resp.data.data;

        if (processos[0].transferenciaUnica === true) {
            toast.add({severity: 'success', summary: 'Sucesso', detail: `Processo Transferido com Sucesso`, life: 3000});
            modalLoading.value = false;
            criarDocRebimento(processos.geraDocTransferencia, processos.codigoTransferencia);
            emit('fecharDialogsAposTransferencia');
            return ;
        }

        if (processos[0].transferidos.length > 0) {
            toast.add({severity: 'success', summary: 'Processos Transferidos', detail: `${processos[0].transferidos.length} processos transferidos!`});
            criarDocRebimento(processos.geraDocTransferencia, processos.codigoTransferencia);
            closeDialog();
            emit('attProcessos');
        }

        if (processos[0].erros.length > 0) {
            processos[0].erros.forEach((elemento) => {
                toast.add({severity: 'error', summary: 'Falha ao transferir', detail: `Processo ${elemento.processo.numero} ---> ${elemento.mensagem}`});
            })
            closeDialog();
        }

        modalLoading.value = false;
        emit('fecharDialogsAposTransferencia');
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

const criarDocRebimento = (geraDocTransferencia, codigoTransferencia) => {
    if (geraDocTransferencia) {
        var url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
        url = url.replace('//w', '/w');
        url = `${url}pro4_termorecebimento.php?codtran=${codigoTransferencia}`;
        window.open(url,'','location=0');
    }
}

defineExpose({
    setProcesso,
    closeDialog,
    openDialog
});
</script>

<template>
    <Dialog
        header="Transferência"
        :maximizable="true"
        :visible="showDialod"
        @update:visible="closeDialog"
        style="height: 300px; width: 550px;"
    >
        <ModalLoading
            :is-loading="modalLoading"
            :message="textLoad"
        />
        <div class="container" style="height: 100% !important;">
            <div class="flex flex-wrap">
                <div class="w-full md:w-1/3 mb-4 md:mb-0 flex items-center">
                    <label style="padding-right: 27px;" for="instituicao" class="mr-2">Instituição:</label>
                    <Dropdown id="instituicao" v-model="instituicaoSelecionada" :options="instituicoes" optionLabel="descricao" placeholder="Selecione uma instituição" class="w-full" @change="onInstituicaoSelecionada"/>
                </div>
                <div style="padding-top: 10px;" class="w-full md:w-1/3 mb-4 md:mb-0 flex items-center">
                    <label for="departamento" class="mr-2">Departamento:</label>
                    <Dropdown filter id="departamento" v-model="departamentoSelecionado" :options="departamentos" optionLabel="label" placeholder="Selecione um departamento" class="w-full" @change="onDepartamentoSelecionado"/>
                </div>
                <div style="padding-top: 10px;" class="w-full md:w-1/3 flex items-center">
                    <label style="padding-right: 8px;" for="recebimento" class="mr-2">Recebimento:</label>
                    <Dropdown filter id="recebimento" v-model="usuarioSelecionado" :options="usuarios" optionLabel="nome" placeholder="Selecione o usuário" class="w-full"/>
                </div>
            </div>
            <div class="container" style="text-align: center;margin-top: 10px;">
                <Button
                    @click="closeDialog"
                    label="Cancelar"
                    class="mr-4"
                    iconPos="right"
                >
                    <template #icon>
                        <i style="margin-right: 10px" class="pi pi-times"></i>
                    </template>
                </Button>
                <Button
                    @click="transferir"
                    label="Transferir"
                    iconPos="right"
                >
                    <template #icon>
                        <i style="margin-right: 10px" class="pi pi-arrow-right-arrow-left"></i>
                    </template>
                </Button>
            </div>
        </div>
    </Dialog>
</template>

<style scoped>

</style>
