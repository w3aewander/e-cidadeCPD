<script setup>
import {ref, watch} from "vue";
import {useToast} from "primevue/usetoast";
import ModalLoading from "@modules/Components/ModalLoading.vue";

const toast = useToast();
const showDialod = ref(false);
const processoDados = ref(null);
const modalLoading = ref(false);
const sucessoEnvio = ref(false);
const enviarRespostaRede = ref(false);
const textLoad = ref('');
const textHeader = ref('');
const inscricoesCgm = ref([]);
const eventos = ref([]);
const checkboxInscricoes = ref([]);
const checkboxDeferido = ref([]);
const checkboxIndeferido = ref([]);

const getInscricoes = async () => {
    textLoad.value = "Carregando Inscrições...";
    modalLoading.value = true;


    try {
        const resp = await window.axios.get(
            `v4/api/tributario/issqn/redesim/inscricoes-cgm/?cgm=${processoDados.value.numcgm}`
        );

        if (resp.data.error) {
            modalLoading.value = false;
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao buscar inscrições.', life: 3000});
            return;
        }

        inscricoesCgm.value = resp.data.data;
        modalLoading.value = false;
    } catch (e) {
        console.log('erro' + e);
        modalLoading.value = false;
    }
}
const getEventos = async () => {
    textLoad.value = "Carregando Eventos...";
    modalLoading.value = true;

    try {
        const resp = await window.axios.get(
            `v4/api/tributario/issqn/redesim/eventos/?processo=${processoDados.value.codigo}`
        );

        if (resp.data.error) {
            modalLoading.value = false;
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao buscar eventos.', life: 3000});
            return;
        }

        eventos.value = resp.data.data;
        modalLoading.value = false;
    } catch (e) {
        console.log('erro' + e);
        modalLoading.value = false;
    }
}
const attInscricao = async () => {
    const parametros = {};
    parametros.inscr = getSelectCheckboxes().join(",");
    parametros.processo = processoDados.value.codigo;

    if (parametros.inscr.length === 0) {
        toast.add({severity: 'warn', summary: 'Aviso', detail: 'Selecione uma inscrição.', life: 3000});
        return;
    }

    textLoad.value = "Atualizando Inscrição...";
    modalLoading.value = true;

    try {
        const response = await window.axios.post(
            `v4/api/tributario/issqn/redesim/processar-evento-alteracao-inscricao`,
            parametros
        );
        sucessoEnvio.value = true;
        modalLoading.value = false;
        toast.add({severity: 'success', summary: 'Sucesso', detail: response.data.message});
    } catch (e) {
        modalLoading.value = false;
        console.log('Erro:', e);
        if (e.response) {
            console.error('Erro no servidor:', e.response.data.message);
            toast.add({severity: 'error', summary: 'Erro', detail: e.response.data.message, life: 3000});
        } else if (e.request) {
            console.error('Nenhuma resposta recebida:', e.request);
            toast.add({severity: 'error', summary: 'Erro', detail: 'Nenhuma resposta do servidor.', life: 3000});
        } else {
            console.error('Erro ao configurar a requisição:', e.message);
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao configurar a requisição: ' + e.message, life: 3000});
        }
    }
}
const inclusaoInscricao = async () => {
    const parametros = {};
    parametros.cgm = processoDados.value.numcgm;
    parametros.processo = processoDados.value.codigo;

    textLoad.value = "Incluindo Inscrição...";
    modalLoading.value = true;

    try {
        const response = await window.axios.post(
            `v4/api/tributario/issqn/redesim/processar-evento-inclusao-inscricao`,
            parametros
        );

        sucessoEnvio.value = true;
        modalLoading.value = false;
        toast.add({severity: 'success', summary: 'Sucesso', detail: response.data.message});
    } catch (e) {
        modalLoading.value = false;
        sucessoEnvio.value = false;
        console.log('Erro:', e);
        if (e.response) {
            console.error('Erro no servidor:', e.response.data.message);
            toast.add({severity: 'error', summary: 'Erro', detail: e.response.data.message});
        } else if (e.request) {
            console.error('Nenhuma resposta recebida:', e.request);
            toast.add({severity: 'error', summary: 'Erro', detail: 'Nenhuma resposta do servidor.'});
        } else {
            console.error('Erro ao configurar a requisição:', e.message);
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao configurar a requisição: ' + e.message});
        }
    }
}
const enviaReposta = async () => {
    if (!checkboxInscricoes.value.length > 0) {
        toast.add({severity: 'warn', summary: 'Aviso', detail: 'Selecionar ao menos uma inscrição.', life: 3000});
        return;
    }

    if (!checkboxInscricoes.value.length > 0) {
        toast.add({severity: 'warn', summary: 'Aviso', detail: 'Selecionar ao menos uma inscrição.', life: 3000});
        return;
    }

    const parametros = {};
    parametros.municipalRegistrationId = checkboxInscricoes.value[0].id;
    parametros.processId = processoDados.value.codigo;

    if (checkboxDeferido.value.length > 0 && checkboxIndeferido.value.length <= 0) {
        parametros.isDeferred = true;
    }

    if (checkboxIndeferido.value.length > 0 && checkboxDeferido.value.length <= 0) {
        parametros.isDeferred = false;
    }

    if (parametros.isDeferred === undefined) {
        toast.add({severity: 'warn', summary: 'Aviso', detail: 'Selecione Deferido ou Indefirido.', life: 3000});
        return;
    }

    if (!confirm("Ao confirmar, a resposta será enviada para a REDESIM. Deseja continuar?")) {
        return;
    }

    textLoad.value = parametros.isDeferred ? "Deferindo..." : "Indeferindo";
    modalLoading.value = true;

    try {
        const response = await window.axios.post(
            `v4/api/tributario/issqn/redesim/enviar-resposta`,
            parametros
        );

        sucessoEnvio.value = true;
        modalLoading.value = false;
        toast.add({severity: 'success', summary: 'Sucesso', detail: response.data.message});
    } catch (e) {
        modalLoading.value = false;
        sucessoEnvio.value = false;
        console.log('Erro:', e);
        if (e.response) {
            console.error('Erro no servidor:', e.response.data.message);
            toast.add({severity: 'error', summary: 'Erro', detail: e.response.data.message});
        } else if (e.request) {
            console.error('Nenhuma resposta recebida:', e.request);
            toast.add({severity: 'error', summary: 'Erro', detail: 'Nenhuma resposta do servidor.'});
        } else {
            console.error('Erro ao configurar a requisição:', e.message);
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao configurar a requisição: ' + e.message});
        }
    }
}
const openDialog = async (enviarRespostaRedeSim) => {
    showDialod.value = true;
    if (enviarRespostaRedeSim) {
        enviarRespostaRede.value = enviarRespostaRedeSim;
        textHeader.value = 'Enviar Resposta REDESIM';
        await getInscricoes();
    } else {
        textHeader.value = processoDados.value ? processoDados.value.descricao : ' ';
        if (!processoDados.value.isProcessoRedesimInclusaoInscricao) {
            await getInscricoes();
            await getEventos();
        }
    }
}
const closeDialog = (e) => {
    checkboxIndeferido.value = [];
    checkboxDeferido.value = [];
    enviarRespostaRede.value = false;
    sucessoEnvio.value = false;
    inscricoesCgm.value = [];
    eventos.value  = [];
    checkboxInscricoes.value  = [];
    showDialod.value = false;
}

const setProcesso = function (processo) {
    processoDados.value = processo;
}

const getSelectCheckboxes = () => {
    const selecteds = [];

    for (let input of checkboxInscricoes.value) {
        selecteds.push(input.id)
    }

    return selecteds;
}

const confirmAcao = () => {
    if (processoDados.value.isProcessoRedesimInclusaoInscricao) {
        if (confirm("Ao confirmar, será gerada uma nova inscrição. Deseja continuar?")) {
            inclusaoInscricao();
        }
    } else {
        if (confirm("Ao confirmar, a inscrição selecionada será atualizada. Deseja continuar?")) {
            attInscricao();
        }
    }
};

const verificaStatus = (checkbox) => {
    if ((checkbox[0] === 'Deferido') && checkboxIndeferido.value.length > 0) {
        checkboxIndeferido.value = [];
    }
    if ((checkbox[0] === 'Indeferido') && checkboxDeferido.value.length > 0) {
        checkboxDeferido.value = [];
    }
}

const checkInscricao = (inscricao) => {
    checkboxInscricoes.value = [];
    checkboxInscricoes.value.push(inscricao);
}

defineExpose({
    setProcesso,
    closeDialog,
    openDialog
});
</script>

<template>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />
    <Dialog
        :maximizable="true"
        :visible="showDialod"
        @update:visible="closeDialog"
        :header="textHeader"
        style="width: 777px;"
    >
        <Fieldset
            legend="Eventos"
            style="margin-top: 5px"
            v-if="(!processoDados.isProcessoRedesimInclusaoInscricao) && eventos.length > 0"
        >
            <div v-for="evento in eventos" :key="evento.id" class="eventos-list">
                <Tag severity="success" :value="evento.external_id" class="tag-item" style="width: 90px !important;"></Tag>
                <Tag severity="success" :value="evento.description" class="tag-item" style="width: 480px !important; margin-left: 10px !important;"></Tag>
            </div>
        </Fieldset>
        <Divider v-if="enviarRespostaRede ? false : !processoDados.isProcessoRedesimInclusaoInscricao"/>
        <div style="margin-top: 10px" v-for="inscricao in inscricoesCgm" :key="inscricao.id">
            <Checkbox v-model="checkboxInscricoes" :value="inscricao" @change="checkInscricao(inscricao)"/>
            <label class="ml-2"> {{inscricao.id}} - {{inscricao.name}} </label>
        </div>
        <div v-if="enviarRespostaRede">
            <Divider/>
            <div>
                <Checkbox v-model="checkboxDeferido" value="Deferido" @change="verificaStatus(checkboxDeferido)"/>
                <label class="ml-2"> Deferido </label>
            </div>
            <div style="margin-top: 10px;">
                <Checkbox v-model="checkboxIndeferido" value="Indeferido" @change="verificaStatus(checkboxIndeferido)"/>
                <label class="ml-2"> Indeferido </label>
            </div>
        </div>
        <div class="botoes-acoes">
            <Button label="Fechar" @click="closeDialog"/>
            <Button v-if="!sucessoEnvio && !enviarRespostaRede" @click="confirmAcao" style="margin-left: 20px;" :label="processoDados.isProcessoRedesimInclusaoInscricao ? 'Incluir inscrição' : 'Atualizar Inscrições'"/>
            <Button v-if="!sucessoEnvio && enviarRespostaRede" @click="enviaReposta" style="margin-left: 20px;" label="Enviar Resposta"/>
        </div>
    </Dialog>
</template>

<style scoped>
.botoes-acoes{
    margin-top: 20px;
    text-align:center;
}
.eventos-list {
    display: flex;
    flex-wrap: wrap;
}

.tag-item {
    text-align: center;
    background-color: #4a789c;
}
</style>
