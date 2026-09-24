<template>
    <form class='chat-box' @submit.prevent>
        <textarea
            v-model='text'
            placeholder='Write a message'
            maxlength='200'
        ></textarea>
        <i title="Recarregar mensagens" class="pi pi-refresh refresh" @click.prevent="$emit('getMensagens')"></i>
        <i title="Anexar arquivos" class="pi pi-paperclip cliper" @click="fileInputClick"></i>
        <input name="arquivo" id="arquivo" type="file" style="display:none" @change="handleFileSelect" multiple>
        <button title="Enviar mensagem" @click.prevent="$emit('onSubmit', text, fileList); text = ''; files = []; fileList = [];" :disabled='text === ""' type="submit"><i class="pi pi-send send"></i></button>
    </form>
    <div id="arquivos-selecionados" class="arquivos-selecionados" v-if="fileList.length > 0">
        <div class="grid" style="margin: 6px; display:block;">
            <div v-for="(file, index) in fileList" class="arquivo-item">
                {{ file.descricao }}
                <a @click="removeFile(index)">X</a>
            </div>
        </div>
    </div>
</template>

<script setup>

import {onMounted, ref} from 'vue';

const text = ref('');
const files = ref([]);
const FILE_RPC = 'pro4_andamento_processo.RPC.php';
var fileList = ref([]);
const emit = defineEmits(['getMensagens', 'onSubmit']);

onMounted(() => {});

function removeFile(indexFile) {
    fileList.value = fileList.value.filter(function (file, index) {
        return index != indexFile;
    });
}

const fileInputClick = () => {
    document.querySelector('input[id="arquivo"]').click();
}

const handleFileSelect = async (event) => {
    const selectedFiles = event.target.files;

    let i = 0;
    var data = new FormData();

    for (i; i < selectedFiles.length; i++) {
        data.append('anexos[]', selectedFiles[i]);
    }

    data.append('acao', 'prepararDocumentos');
    data.append('getExtArq', 0);

    const isV3 = typeof CurrentWindow !== 'undefined' && typeof CurrentWindow.resolveUrl === 'function';
    var url = isV3 ? CurrentWindow.resolveUrl(FILE_RPC) : FILE_RPC;

    window.axios.post(url, data).then(function (response) {
        if (response.data.erro) {
            alert(response.data.mensagem);
            return;
        }
        response.data.documentos.forEach(function (documento) {
            fileList.value.push(Object.assign({}, documento));
        });
    }).catch(function (error) {
        console.error('Erro ao fazer a requisição:', error);
    });


    event.target.value = '';
}

</script>

<style scoped>
.chat-box {
    width: 100%;
    display: flex;
}

.arquivo-item{
    display: inline-block;
    background: #fff;
    padding: 5px;
    border-radius: 5px;
    border: solid #0a0a0a 1px;
    margin-right: 7px;
    white-space: nowrap;
}

.arquivo-item:hover {
    cursor: pointer;
}

.arquivos-selecionados{
    width: 100%;
    margin-right: auto;
    border-radius: 8px;
    overflow-x: auto;
    white-space: nowrap;
    height: 140px;
    display: flex;
}

textarea {
    width: min(100%, 20rem);
    flex-grow: 1;
    resize:none;
    border-radius: 8px;
}

button:disabled {
    opacity: 0.5;
}

.cliper {
    padding: 1rem;
    font-size: 1.5rem;
    background-color: darkgrey;
}

.cliper:hover {
    cursor: pointer;
    background-color: #737272;
}

.refresh {
    padding: 1rem;
    font-size: 1.5rem;
    background: #a5e1a7;
}

.refresh:hover {
    cursor: pointer;
    background: #539356;
}

.send {
    font-size: 1.5rem;
}
</style>
