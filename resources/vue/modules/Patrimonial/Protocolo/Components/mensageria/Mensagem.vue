<template>
    <div :id="'M'+mensagem.p123_id" :class="['message', { 'interna': mensagem.p123_interna }]" :title="'Última visualização: '+ultimoVisualizar.p125_nome">
        <h5 class="nome-remetente" title="Visualizar CGM" @click="$emit('consultaCgm', mensagem)">{{ mensagem.p123_nome }}</h5>
        {{ mensagem.p123_mensagem }}
        <div :class="['message-referenciada', { 'externa': respostaMensagemStyle }]" v-if="respostaMensagem" @click="$emit('scrollToMensagem', posicaoMensagem(mensagem.referencia.p123_id), mensagem.referencia.p123_id)">
            {{mensagem.referencia.p123_mensagem}}
        </div>
        <br>
        <br>
        <div class="icons">
            <i title="Responder mensagem" class="pi pi-arrow-left icon-arrow" style="font-size: 15px; position: absolute;" @click="$emit('mensagemReferenciada', mensagem);"></i>
            <i id="icon-file" title="Visualizar arquivos" :class="['pi pi-file icon-file', { 'externa': mensagem.p123_interna == false ? true : false }, {'com-despacho': !iconDespacho}]" @click="visualizarDocumentos" style="position: absolute; font-size: 15px; color: #777;" v-if="possuiDocumentos"></i>
            <i title="Criar despacho" class="pi pi-file-import dialog-despacho" @click="$emit('dialogDespacho', mensagem);" v-if="iconDespacho"></i>
            <p style="position: absolute; bottom: -6px; right: 7px; font-size: 15px; color: #777;">{{ formattedDate }}</p>
        </div>
    </div>
</template>

<script setup>
import {onUpdated, onMounted, ref} from 'vue';

const props = defineProps(['mensagem', 'ultimoVisualizar']);
const emit = defineEmits(['getMensagens', 'onSubmit', 'criarDespacho', 'mensagemReferenciada', 'scrollToMensagem', 'dialogDespacho', 'consultaCgm']);
const formattedDate = ref('');
const possuiDocumentos = ref(false);
const respostaMensagem = ref(false);
const respostaMensagemStyle = ref(false);
const iconDespacho = ref(true);
const idStorage = ref('');

onMounted(() => {
    const rawDate = new Date(props.mensagem.p123_data_criacao);
    const hours = String(rawDate.getHours()).padStart(2, '0');
    const minutes = String(rawDate.getMinutes()).padStart(2, '0');
    formattedDate.value = `${hours}:${minutes}`;
    verificaDocumentos();
    verificaResposta();
    respondeMensagemEstilo();
    verificaDespacho();
});

onUpdated(() => {
    verificaDespacho();
});

function verificaDespacho() {
    iconDespacho.value = props.mensagem.despacho == null;
}

function respondeMensagemEstilo() {
    if (props.mensagem.referencia != null) {
        if(props.mensagem.referencia.p123_interna === true && props.mensagem.p123_interna === true){
            respostaMensagemStyle.value = false;
        }

        if(props.mensagem.referencia.p123_interna === false && props.mensagem.p123_interna === true){
            respostaMensagemStyle.value = true;
        }
    } else{
        respostaMensagemStyle.value = true;
    }
}

function verificaResposta() {
    if(props.mensagem.p123_resposta_mensagem != null){
        respostaMensagem.value = true;
        return true;
    }else{
        respostaMensagem.value = false;
        return false;
    }
}

function visualizarDocumentos() {
    const url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_visualizador_imagens',
        `${url}db_visualizador_documentos.php?ids=${idStorage.value}`,
        'Visualizador de documentos',
        true
    );
}

function verificaDocumentos() {
    if(props.mensagem.documentos.length > 0){
        possuiDocumentos.value = true;
        setIds();
    }else{
        possuiDocumentos.value = false;
    }
}

function setIds() {
    const codigoStorageArray = [];
    for (const documento of props.mensagem.documentos) {
        codigoStorageArray.push(documento.p124_codigo_storage);
    }
    idStorage.value = codigoStorageArray.join(',');
}

function posicaoMensagem(id) {
    var idMensagem = `M${id}`;
    var minhaDiv = document.getElementById(idMensagem);
    return minhaDiv.offsetTop;
}

</script>

<style scoped>

.message {
    margin-top: 1rem;
    background: #a3bbff;
    border-radius: 10px;
    padding: 1rem;
    min-width: 160px;
    width: inherit;
    position: relative;
}

.message.right {
    margin-right: 10%;
    margin-left: 40%;
}

.message.left {
    margin-right: 40%;
    margin-left: 10%;
}

.message-referenciada.externa {
    background: #a3bbff;
    border-radius: 10px;
    padding: 1rem;
    width: 100%;
    position: relative;
}

.message-referenciada {
    margin-top:10px;
    background: #3a7a3d;
    border-radius: 10px;
    padding: 1rem;
    width: 100%;
    position: relative;
}

.message-referenciada:hover {
    cursor: pointer;
}

.message.interna {
    width: inherit;
    background: #a5e1a7;
}

h5 {
    margin: 0 0 .5rem 0;
}

.pi-file {
    margin-right: 5px;
    font-size: 15px;
    font-weight: 900;
    color: rgb(119, 119, 119);
}

.icon-file.com-despacho {
    position: absolute;
    bottom: 10px;
    right: 50px !important;
    font-weight: 900;
}

.icon-file {
    position: absolute;
    bottom: 10px;
    right: 75px;
}

.icon-file.externa {
    position: absolute;
    bottom: 10px;
    right: 75px;
    font-weight: 900;
}

.icon-file:hover {
    cursor: pointer;
}

.icon-arrow {
    position: absolute;
    bottom: 10px;
}

.icon-arrow:hover {
    cursor: pointer;
}

.dialog-despacho {
    font-weight: 900;
    position: absolute;
    bottom: 10px;
    right: 55px;
    font-size: 15px;
    color: #777;
}

.dialog-despacho:hover {
    cursor:pointer;
}

.nome-remetente:hover {
  cursor:pointer;
}
</style>
