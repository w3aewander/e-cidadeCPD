<script setup>

import { ref } from 'vue';

import ChatBox from "../../Components/mensageria/ChatBox.vue";
import Mensagem from "../../Components/mensageria/Mensagem.vue";
import ModalLoading from "../../../../Components/ModalLoading.vue";
import DialogDespacho from "../../Components/mensageria/DialogDespacho.vue";

const props = defineProps(['processo', 'dialog']);
const emit = defineEmits(['getMensagens', 'onSubmit', 'criarDespacho']);
const mensagens = ref([]);
const ultimoVisualizar = ref([]);
const modalLoading = ref(false);
const textLoad = ref('');
const mensagemReferenciadaObjeto = ref([]);
const mensagemDespacho = ref([]);
const grupoMensagensPorData = ref([]);
const componentKey = ref(0);
const dialogShow = ref(false);
const avisoDialog = ref('');
const visualizarBtn = ref(true);

const getMensagens = async () => {
    textLoad.value = "Carregando mensagens...";
    modalLoading.value = true;
    const parametros = {};
    parametros.processo = props.processo;

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/getMensagem`,
            parametros
        );

        ultimoVisualizar.value = resp.data.data.ultima_visualizacao;
        grupoMensagensPorData.value = mensagensPorData(resp.data.data.mensagens);
        modalLoading.value = false;

        setTimeout(scrollMensagens, 100);
    } catch (e) {
        mensagens.value = [];
        modalLoading.value = false;
    }
}

async function onSubmit(text, fileList) {
    textLoad.value = "Enviando mensagem...";
    modalLoading.value = true;

    const parametros = {};
    parametros.processo = props.processo;
    parametros.mensagem = text;
    parametros.arquivos = JSON.stringify(fileList);
    parametros.acao = 'interna';

    if(Object.keys(mensagemReferenciadaObjeto).length){
        parametros.mensagemReferenciada = mensagemReferenciadaObjeto.value.p123_id;
    }

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/sendMensagem`,
            parametros
        );

        var data = resp.data.data;

        if (data.hasOwnProperty('alerta') && data.alerta === true){
            alert(data.mensagem);
        }

        mensagemReferenciadaObjeto.value = [];
    } catch (e) {
        mensagemReferenciadaObjeto.value = [];
        mensagens.value = [];
        modalLoading.value = false;
    }
    getMensagens();
}

async function criarDespacho(checkboxValue) {
    dialogShow.value = false;
    if (Object.keys(mensagemDespacho).length) {
        textLoad.value = "Criando despacho...";
        modalLoading.value = true;
        const parametros = {};
        parametros.mensagem = JSON.stringify(mensagemDespacho.value);
        parametros.publico = checkboxValue;
        try {
            const resp = await window.axios.post(
                'v4/api/patrimonial/protocolo/processo/criarDespacho',
                parametros
            );

            if (!resp.data.erro) {
                textLoad.value = "Vinculando despacho a mensagem...";
                vinculaDespachoMensagem(resp.data.data.codigoDespacho, mensagemDespacho.value.p123_id);
            }
        } catch (e) {
            modalLoading.value = false;
        }
    }
}

async function vinculaDespachoMensagem(codigoDespacho, mensagemDespacho) {
    const parametros = {};
    parametros.idMensagem = mensagemDespacho;
    parametros.codigoDespacho = codigoDespacho;
    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/processo/vincularDespachoMensagem',
            parametros
        );
        modalLoading.value = false;
        if (!resp.data.erro) {
            await getMensagens();
            avisoDialog.value = 'Despacho criado e vinculado com sucesso!';
            visualizarBtn.value = false;
            dialogShow.value = true;
        } else {
            avisoDialog.value = 'Erro no processo de criação de despacho!';
            visualizarBtn.value = false;
            dialogShow.value = true;
        }
    } catch (e) {
        modalLoading.value = false;
    }
}

function dialogDespacho(mensagem) {
    dialogShow.value = true;
    avisoDialog.value = 'Deseja criar um despacho baseado na mensagem?';
    mensagemDespacho.value = mensagem;
}

function fecharDialog() {
    dialogShow.value = false;
    visualizarBtn.value = true;
}

function scrollMensagens() {
    const minhaDiv = document.querySelector('#mensagens');
    minhaDiv.scrollTop = minhaDiv.scrollHeight;
}

function scrollToMensagem(posicaoMensagem, idMensagem) {
    const minhaDiv = document.querySelector('#mensagens');
    const alturaJanela = window.innerHeight;
    const metadeAlturaJanela = alturaJanela / 2;
    const posicaoCentralizada = posicaoMensagem - metadeAlturaJanela;

    minhaDiv.scrollTop = posicaoCentralizada;
    destacaMensagem(idMensagem);
}

function destacaMensagem(id) {
    var idMensagem = `M${id}`;
    var div = document.getElementById(idMensagem);

    function piscar() {
        div.classList.toggle("destaque");
    }

    var intervalo = setInterval(piscar, 200);

    setTimeout(function() {
        clearInterval(intervalo);
        div.classList.remove("destaque");
    }, 2000);
}

function mensagemReferenciada(mensagem) {
    mensagemReferenciadaObjeto.value = mensagem;
}

function mensagensPorData(mensagens) {
    const grupo = {};

    mensagens.forEach((mensagem) => {
        const mensagemData = new Date(mensagem.p123_data_criacao);
        var dia;
        var mes = mensagemData.getMonth() + 1;

        if(mensagemData.getDate() < 10){
            dia = `0${mensagemData.getDate()}`
        }else{
            dia = mensagemData.getDate();
        }

        if(mes < 10){
            mes = `0${mes}`
        }

        const dataFormatada = `${dia}/${mes}/${mensagemData.getFullYear()}`;

        if (!grupo[dataFormatada]) {
            grupo[dataFormatada] = [];
        }

        grupo[dataFormatada].push(mensagem);
    });

    return Object.entries(grupo).map(([data, mensagens]) => ({
        data,
        mensagens,
    }));
}

async function consultaCgm (mensagem) {
  textLoad.value = "Consultando CGM...";
  modalLoading.value = true;
  const parametros = {}
  parametros.cpfcnpj = mensagem.p123_cpfcnpj;
  var data = null;

  try {
    const resp = await window.axios.post(
        'v4/api/patrimonial/protocolo/processo/getCgmByCpfCnpj',
          parametros
    );
    data = resp.data.data;
    modalLoading.value = false;
  } catch (e) {
    modalLoading.value = false;
  }

  if (data.erro) {
    alert(data.mensagem);
  } else {
      const url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
      var arquivo = 'prot3_consultacgmnovo002.php?numcgm=' + data.cgm;
      js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'consulta_cgm',
        `${url}${arquivo}`,
        'Visualizar CGM',
        true
      );
  }

}

getMensagens();
</script>

<template>
    <DialogDespacho
        :show="dialogShow"
        :aviso="avisoDialog"
        :visualizarBtn="visualizarBtn"
        @fecharDialog="fecharDialog"
        @criarDespacho="criarDespacho"
    />
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />
    <div :class="dialog ? 'dialog-mensagem' : 'app-chat'">
        <div class="sem-mensagens" v-if="grupoMensagensPorData.length <= 0">
            Histórico vazio!
        </div>
        <div ref='messages' class='messages' id="mensagens">
            <div v-for="grupo in grupoMensagensPorData" :key="grupo.data">
                <h2 style="text-align: center">{{ grupo.data }}</h2>
                <Mensagem
                    v-for='mensagem in grupo.mensagens'
                    :key='mensagem.id'
                    :class='["message", { right: mensagem.p123_interna, left: !mensagem.p123_interna }]'
                    :mensagem="mensagem"
                    :ultimoVisualizar="ultimoVisualizar"
                    @mensagemReferenciada="mensagemReferenciada"
                    @scrollToMensagem="scrollToMensagem"
                    @dialogDespacho="dialogDespacho"
                    @consultaCgm="consultaCgm"
                />
            </div>
        </div>
        <div v-if="Object.keys(mensagemReferenciadaObjeto).length" :class="['mensagem-referenciada', { 'externa': !mensagemReferenciadaObjeto.p123_interna }]">
            <div class="container" style="display: flex; padding:0px">
                <h5 style="margin: 6px">Respondendo mensagem</h5>
                <a  class="fechar-referencia" @click="mensagemReferenciadaObjeto = [] " style="margin-left: auto; padding-right: 7px">x</a>
            </div>
            <p style="margin: 6px">{{mensagemReferenciadaObjeto.p123_mensagem}}</p>
        </div>
        <ChatBox
            :key="componentKey"
            @onSubmit="onSubmit"
            @getMensagens="getMensagens"
        />
    </div>
</template>

<style scoped>
.dialog-mensagem {
    height: 90vh;
    width: 50%;
    display: flex;
    flex-direction: column;
    margin: auto;
    justify-content: center;
    align-items: center;
}
.destaque {
    transform: scale(1);
    animation: pulse 2s;
}

@keyframes pulse {
    0% {
        transform: scale(1); /* Escala inicial */
    }
    50% {
        transform: scale(1.08); /* Escala intermediária */
    }
    100% {
        transform: scale(1); /* Escala final, igual à inicial */
    }
}

.fechar-referencia:hover{
    cursor: pointer;
}

.mensagem-referenciada{
    position: relative;
    background: #a5e1a7;
    border-radius: 8px;
    margin-right: auto;
    width: 89%;
    height: fit-content;
}

.mensagem-referenciada.externa{
    background: #a3bbff;
    border-radius: 8px;
    margin-right: auto;
    width: 89%;
    height: fit-content;
}

.app-chat {
    height: 100vh;
    width: 50%;
    display: flex;
    flex-direction: column;
    margin: auto;
    justify-content: center;
    align-items: center
}

.messages {
    background-color: white;
    flex-grow: 1;
    overflow-y: scroll;
    padding: 1rem;
    width: 100%;
    min-height: 88%;
}

.sem-mensagens {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    font-size: 20px;
}

</style>

<style scoped>
* {
    box-sizing: border-box;
}

html {
    font-family: 'Georama', sans-serif;
}

body {
    margin: 0;
}

button {
    border: 0;
    background: #2a60ff;
    color: white;
    cursor: pointer;
    padding: 1rem;
}

input {
    border: 0;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.1);
}
</style>
