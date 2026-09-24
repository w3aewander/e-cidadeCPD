<script setup>
import {onMounted, ref} from 'vue';
import {useToast} from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import ModalLoading from "@modules/Components/ModalLoading.vue";
import Textarea from 'primevue/textarea';
import ConfirmDialog from 'primevue/confirmdialog';

const confirm = useConfirm();
const templates = ref({
    data: []
});
const ecidadepath = window.ECIDADE_PATH;
const imgSrc = ecidadepath + 'imagens/educacao/secretaria/SemTemplatesCadastrados.png';
const registerMsg = ref(false);
const editMsg = ref(false);
const msgTemplate = ref('');
const liberaTemplate = ref (false);
const currentMessageId = ref(null);
const caractersDeEstilo = ref('');
const routes = {
    buscarTemplates: `v4/api/educacao/secretaria/cadastros/templates/index`,
    salvarTemplate: `v4/api/educacao/secretaria/cadastros/templates/store`,
    editarTemplate: `v4/api/educacao/secretaria/cadastros/templates/update`,
    excluirTemplate: `v4/api/educacao/secretaria/cadastros/templates/destroy`,
}
const loading = ref(false);
const mensagemModal = ref(null);
const mensagens = {
    salvar: 'Salvando template...',
    buscar: 'Buscando templates...',
    deletar: 'Excluindo template...'
}
const textareaRef = ref(null);
const toast = useToast();

const showConfirmDialog = (id) => {
    confirm.require({
        group: 'templating',
        header: 'Confirmação',
        message: 'Posso deletar o template?',
        icon: 'pi pi-exclamation-circle',
        acceptIcon: 'pi pi-check',
        rejectIcon: 'pi pi-times',
        rejectClass: 'p-button-outlined p-button-sm',
        acceptClass: 'p-button-sm',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Confirmar',
        accept: () => {
            deleteMessage(id);
        },
        reject: () => {
        }
    });
};

async function buscarTemplates() {
    try {
        mensagemModal.value = mensagens.buscar;
        loading.value = true;

        const response = await window.axios.get(routes.buscarTemplates);
        if (response.data.data.length > 0) {
            templates.value.data = response.data.data.map((template, index) => ({
                index: index + 1,
                id: template.ed203_template_id,
                body: template.ed203_mensagem,
                ativo: template.ed203_ativo
            }));
        } else {
            toast.add({
                severity: 'warn',
                summary: 'Atenção!',
                detail: 'Não há templates cadastrados no sistema!',
                life: 15000
            });
        }
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: `${e.response.data.message}`,
            life: 15000
        });
    } finally {
        loading.value = false;
    }
}

async function saveChanges() {
    if (!editMsg.value) {
        await saveNewMessage(msgTemplate.value, liberaTemplate.value)
            .then(() => {
                buscarTemplates();
            });
    } else {
        await updateMessage(currentMessageId.value, msgTemplate.value, liberaTemplate.value)
            .then(() => {
                buscarTemplates();
            });
    }

    resetForm();
}

async function saveNewMessage(message, liberaTemplate) {
    let parametro = {
        template: message,
        ativo: liberaTemplate
    }
    try {
        mensagemModal.value = mensagens.salvar;
        loading.value = true;

        await window.axios.post(routes.salvarTemplate, parametro);
        toast.add({
            severity: 'success',
            summary: 'Sucesso',
            detail: 'Template salvo com sucesso!',
            life: 15000
        });
        registerMsg.value = false;
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: `${e.response.data.message}`,
            life: 15000
        });
    } finally {
        loading.value = false;
    }
}

async function updateMessage(id, updatedMessage, liberaTemplate) {
    let parametro = {
        dados: {
            id: id,
            body: updatedMessage,
            ativo: liberaTemplate
        }
    }

    try {
        loading.value = true;
        mensagemModal.value = mensagens.salvar

        await window.axios.put(routes.editarTemplate, parametro);
        toast.add({
            severity: 'success',
            summary: 'Sucesso',
            detail: 'Template salvo com sucesso!',
            life: 15000
        });
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: `${e.response.data.message}`,
            life: 15000
        });
    } finally {
        loading.value = false;
    }
}

async function deleteMessage(id) {
    try {
        loading.value = true;
        mensagemModal.value = mensagens.deletar

        await window.axios.delete(`${routes.excluirTemplate}/${id}`);

        templates.value.data = templates.value.data.filter(template => template.id !== id);
        toast.add({
            severity: 'success',
            summary: 'Sucesso',
            detail: 'Template excluído com sucesso!',
            life: 15000
        });

        // Verificar se não há mais templates
        if (templates.value.data.length === 0) {
            toast.add({
                severity: 'warn',
                summary: 'Atenção!',
                detail: 'Não há templates cadastrados no sistema!',
                life: 15000
            });
        }
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: `${e.response.data.message}`,
            life: 15000
        });
    } finally {
        loading.value = false;
    }
}

function editMessage(message) {
    editMsg.value = true;
    msgTemplate.value = message.body;
    currentMessageId.value = message.id;
    liberaTemplate.value = message.ativo
}

function createMessage() {
    registerMsg.value = true;
    editMsg.value = false;
    msgTemplate.value = '';
    liberaTemplate.value = false;
}
function resetForm() {
    registerMsg.value = false;
    editMsg.value = false;
    msgTemplate.value = '';
    currentMessageId.value = null;
    liberaTemplate.value = false;
}

function selectedText() {
    const textArea = textareaRef.value;
    const inicio = textArea.selectionStart;
    const fim = textArea.selectionEnd;

    if (inicio !== fim) {
        return textArea.value.substring(inicio, fim);
    } else {
        return '';
    }
}

function insertStyle(estilo) {
    const textArea = document.querySelector('Textarea');
    const inicio = textArea.selectionStart;
    const fim = textArea.selectionEnd;

    switch (estilo) {
        case 'bold':
            caractersDeEstilo.value = '*';
            break;
        case 'italic':
            caractersDeEstilo.value = '_';
            break;
        case 'strike':
            caractersDeEstilo.value = '~';
            break;
        case 'variavel':
            caractersDeEstilo.value = '[####]';
            break;
    }

    if (estilo !== 'variavel') {
        if (inicio !== fim) {
            const textoSelecionado = msgTemplate.value.substring(inicio, fim);
            const textoComEstilo = caractersDeEstilo.value + textoSelecionado + caractersDeEstilo.value;
            msgTemplate.value =
                msgTemplate.value.slice(0, inicio) + textoComEstilo + msgTemplate.value.slice(fim);
            textArea.setSelectionRange(inicio, inicio + textoComEstilo.length);
        } else {
            const textoComEstilo = caractersDeEstilo.value + caractersDeEstilo.value;
            msgTemplate.value =
                msgTemplate.value.slice(0, fim) + textoComEstilo + msgTemplate.value.slice(fim);
        }
    } else {
        const textoComEstilo = caractersDeEstilo.value;
        msgTemplate.value =
            msgTemplate.value.slice(0, fim) + textoComEstilo + msgTemplate.value.slice(fim);
    }

    textArea.focus();
}

onMounted(() => {
   buscarTemplates();
});

</script>

<template>
    <div class="background-white messages-wrapper">
        <div class="list-mensagens-container">
            <h1 class="list-mensagens-title">Templates Cadastrados</h1>
            <div class="data-view" v-if="templates.data.length > 0">
                <DataView :value="templates.data" paginator :rows="6">
                    <template #list="slotProps">
                        <div class="mensagem">
                            <div class="conteudo-mensagem">
                                    <div>
                                        <Tag rounded :icon="slotProps.data.ativo ? 'pi pi-check' : 'pi pi-times'" :severity="slotProps.data.ativo ? 'success' : 'warning'">{{ slotProps.data.ativo ? 'Ativo' : 'Inativo' }}</Tag>
                                    </div>
                                    <div class="text-lg font-medium text-900 mt-2 message-content">
                                        {{ slotProps.data.body.length > 50 ? slotProps.data.body.slice(0,45) + ' (...)' :  slotProps.data.body }}
                                    </div>
                                </div>
                                <div class="botoes-mensagem">
                                    <Button icon="pi pi-pencil" aria-label="Editar" rounded outlined @click="editMessage(slotProps.data)"></Button>
                                    <Button icon="pi pi-trash" aria-label="Excluir" severity="danger" rounded @click="showConfirmDialog(slotProps.data.id)"></Button>
                                </div>
                            </div>
                        </template>
                    </DataView>
                </div>
                <ConfirmDialog group="templating">
                    <template #message="slotProps">
                        <div class="flex flex-column align-items-center w-full gap-3 border-bottom-1 surface-border">
                            <i :class="slotProps.message.icon" class="text-6xl text-primary-500"></i>
                            <p>{{ slotProps.message.message }}</p>
                        </div>
                    </template>
                </ConfirmDialog>
                <div  class="sem-templates" v-if="templates.data.length <= 0">
                    <Image
                        :src="imgSrc"
                        alt="Imagem de uma carta aberta com a mensagem de que não há templates cadastrados."
                        width="480">
                    </Image>
                    <Button
                        label="Cadastrar Template"
                        @click="createMessage"
                        rounded
                        raised
                        style="width: 15rem;
                        background-color: #ffffff;
                        color: #0b4d85;
                        font-size: 1.1rem;
                        padding: 1rem;"
                    >
                    </Button>
                </div>
            </div>
            <div class="container-view-mensagem">
                <div class="content-sem-templates" v-if="templates.data.length <= 0 && !registerMsg">
                    <Button
                        label="Cadastrar Template"
                        @click="createMessage"
                        rounded
                        raised
                        style="width: 15rem;
                        background-color: #ffffff;
                        color: #0b4d85;
                        font-size: 1.1rem;
                        padding: 1rem;"
                    >
                    </Button>
                </div>
                <div v-else-if="registerMsg || editMsg" class="text-area-msg">
                    <div class="editor-container">
                        <div class="buttons-container">
                            <div class="editor-buttons-container">
                                <div class="buttons-editor-wrapper">
                                    <Button text @click="insertStyle('bold')">
                                        <svg fill="#000000" width="24px" height="24px" viewBox="0 0 24 24" id="bold-square" data-name="Flat Color"
                                             xmlns="http://www.w3.org/2000/svg" className="icon flat-color">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"/>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
                                            <g id="SVGRepo_iconCarrier">
                                                <rect id="primary" x="2" y="2" width="20" height="20" rx="2" style="fill: #0b4d85;"/>
                                                <path id="secondary"
                                                      d="M15.34,11.53a3.47,3.47,0,0,0,.66-2A3.5,3.5,0,0,0,12.5,6H8A1,1,0,0,0,8,8v8a1,1,0,0,0,0,2h5.5a3.5,3.5,0,0,0,1.84-6.47ZM12.5,8a1.5,1.5,0,0,1,0,3H10V8Zm1,8H10V13h3.5a1.5,1.5,0,0,1,0,3Z"
                                                      style="fill: #ffffff;"/>
                                            </g>
                                        </svg>
                                    </Button>
                                    <Button text @click="insertStyle('italic')">
                                        <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"/>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
                                            <g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M3.46447 3.46447C2 4.92893 2 7.28595 2 12C2 16.714 2 19.0711 3.46447 20.5355C4.92893 22 7.28595 22 12 22C16.714 22 19.0711 22 20.5355 20.5355C22 19.0711 22 16.714 22 12C22 7.28595 22 4.92893 20.5355 3.46447C19.0711 2 16.714 2 12 2C7.28595 2 4.92893 2 3.46447 3.46447ZM10.6667 6.25H13.3162C13.3273 6.24975 13.3384 6.24975 13.3495 6.25H16C16.4142 6.25 16.75 6.58579 16.75 7C16.75 7.41421 16.4142 7.75 16 7.75H13.9095L11.6429 16.25H13.3333C13.7475 16.25 14.0833 16.5858 14.0833 17C14.0833 17.4142 13.7475 17.75 13.3333 17.75H10.6838C10.6727 17.7502 10.6616 17.7502 10.6505 17.75H8C7.58579 17.75 7.25 17.4142 7.25 17C7.25 16.5858 7.58579 16.25 8 16.25H10.0905L12.3571 7.75H10.6667C10.2525 7.75 9.91667 7.41421 9.91667 7C9.91667 6.58579 10.2525 6.25 10.6667 6.25Z" fill="#0b4d85"/> </g>
                                        </svg>
                                    </Button>
                                    <Button text @click="insertStyle('strike')">
                                        <svg fill="#000000" width="24px" height="24px" viewBox="0 0 24 24" id="strikethrough-square" data-name="Flat Color" xmlns="http://www.w3.org/2000/svg" class="icon flat-color">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"/>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>
                                            <g id="SVGRepo_iconCarrier">
                                                <rect id="primary" x="2" y="2" width="20" height="20" rx="2" style="fill: #0b4d85;"/>
                                                <path id="secondary" d="M17,11H13V8h2a1,1,0,0,0,2,0V7a1,1,0,0,0-1-1H8A1,1,0,0,0,7,7V8A1,1,0,0,0,9,8h2v3H7a1,1,0,0,0,0,2h4v3a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2V13h4a1,1,0,0,0,0-2Z" style="fill: #ffffff;"/>
                                            </g>
                                        </svg>
                                    </Button>
                                </div>
                                <Button label="Inserir variável" icon="pi pi-hashtag" @click="insertStyle('variavel')" raised style="background-color: #ffffff; color: #0b4d85"></Button>
                            </div>
                            <div class="libera-template">
                                <div class="flex align-items-center">
                                    <Checkbox id="check-libera" v-model="liberaTemplate" value="Libera Template" inputId="liberaTemplate" :binary="true" @change="console.log(liberaTemplate)" />
                                    <label for="liberaTemplate" class="ml-2"> Libera Template </label>
                                </div>
                            </div>
                        </div>
                    <Textarea id="text-area" v-model="msgTemplate" autoResize rows="10" cols="60" ref="textareaRef" @mouseup="selectedText"/>
                    </div>
                    <Button
                        label="Salvar"
                        icon="pi pi-save"
                        @click="saveChanges"
                        rounded
                        raised
                        :disabled="msgTemplate === ''"
                        style="width: 10rem;
                        margin-top: 1rem;
                        background-color: #0b4d85;
                        color: #ffffff;
                        font-size: 1.1rem;
                        padding: 0.7rem;"
                    >
                    </Button>
                </div>
                <div class="button-adicionar">
                    <Button icon="pi pi-plus"
                            rounded
                            raised
                            aria-label="Cadastrar Template"
                            @click="createMessage"
                    >
                    </Button>
                </div>
            </div>
        </div>
    <ModalLoading :is-loading="loading" :message="mensagemModal"/>
</template>

<style scoped>

.sem-templates {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4rem;
}

.mensagem {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.conteudo-mensagem {
    margin-top: 0.5rem;
    display: flex;
    flex-direction: column;
    min-height: 6rem;
    min-width: 32rem;
}

.botoes-mensagem {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
}

.container-view-mensagem {
    position: relative;
    box-shadow: 0 0 5px grey;
    border-radius: 1rem;
    width: 60vw;
    height: 90vh;
    background-color: #FFF;
}

.editor-container {
    width: auto;
}

.buttons-container {
    display: flex;
    flex-direction: row;
    justify-content: space-around;
    align-items: center;
}

.editor-buttons-container {
    display: flex;
    justify-content: space-between;
    align-items: normal;
}

.button-adicionar {
    position: absolute;
    right: 2rem;
    bottom: 2rem;
}

.content-sem-templates,
.text-area-msg {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 90vh;
}

#text-area {
    margin-top: 0.5rem;
}

.messages-wrapper {
    display: flex;
    flex-direction: row;
    gap: 2rem;
    justify-content: center;
    align-items: flex-start;
    padding: 2rem;
}

.background-white {
    width: 100vw;
    height: 100vh;
}

.list-mensagens-container {
    width: 43rem;
    padding: 1rem 3rem 2rem;
    box-shadow: 0 0 5px grey;
    border-radius: 1rem;
    height: 90vh;
    overflow-y: auto;
    background-color: #FFF;
}

.list-mensagens-title {
    font-size: 2rem;
    padding-bottom: 2rem;
    color: #022c62;
}

.libera-template label {
    font-size: 1rem;
    font-weight: bold;
    color: #023273;
}

#check-libera {
    box-shadow: 1px 1px 3px #023273;
}

.message-content {
    flex-grow: 1;
    display: flex;
    align-items: center;
    max-height: 60px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
