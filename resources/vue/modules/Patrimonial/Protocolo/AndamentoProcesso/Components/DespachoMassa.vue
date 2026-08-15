<script setup>

import Trumbowyg from "vue-trumbowyg";
import {onMounted, ref} from "vue";
import {useConfirm} from "primevue/useconfirm";
import {useToast} from "primevue/usetoast";
import ModalLoading from "@modules/Components/ModalLoading.vue";
import Swal from "sweetalert2";

const props = defineProps(['processos']);
const emit = defineEmits(['buscarProcessos', 'fecharDialog']);
const opcoes = ref([
    { name: 'Sim', code: true },
    { name: 'Não', code: false }
]);
const despachoPublico = ref(opcoes.value[0]);
const despachoPublicoTexto = ref();
const editor = ref({
    config: {
        btns: [
            [
                'foreColor',
                'backColor',
                'historyUndo',
                'historyRedo',
            ],
            ['strong', 'em', 'del'],
            ['formatting'],
            ['superscript', 'subscript'],
            ['link'],
            ['image'], // Our fresh created dropdown
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
        ]
    },
});
const anexos = ref([]);
const confirm = useConfirm();
const toast = useToast();
const modalLoading = ref(false);
const textLoad = ref('');
const upload = ref(false);

const atualizarListaArquivos = ({files} = {files: []}) => {
    anexos.value = files;
}

onMounted(() => {
    console.log(props.processos);
});

const confirm3 = () => {
    confirm.require({
        group: 'templating',
        message: `Deseja despachar ${props.processos.length} processo?`,
        header: 'Atenção',
        icon: 'pi pi-exclamation-triangle',
        acceptIcon: 'pi pi-check',
        rejectIcon: 'pi pi-times',
        acceptClass: 'borda-arredondada',
        rejectClass: 'p-button-secondary p-button-outlined borda-arredondada',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Despachar',
        accept:despacharEmMassa,
        reject: () => {}
    });
};

const despacharEmMassa = async () => {
    if (despachoPublico.value === undefined) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Selecione se o daspacho é público ou não.', life: 3000});
        return false;
    }
    if (despachoPublicoTexto.value === undefined) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Preencha o campo Despacho.', life: 3000});
        return false;
    }

    if (props.processos.length < 0) {
        toast.add({severity: 'warn', summary: 'Atenção', detail: 'Selecione pelo menos um processo para despache'});
        return false;
    }

    textLoad.value = "Despachando processos...";
    modalLoading.value = true;

    const selectFiles = [];
    for (let anexo of anexos.value) {
        let fileBuffer = await anexo.arrayBuffer();
        let blob = await generateBlob(fileBuffer, anexo.type);
        let base64 = await covertBlobToBase64(blob);
        selectFiles.push({
            nome: anexo.name,
            type: anexo.type,
            conteudo: base64
        });
    }

    const parametros = {};
    parametros.processos = props.processos;
    parametros.despachoInterno = despachoPublicoTexto.value;
    parametros.despachoPublico = despachoPublico.value.code;
    parametros.origemMensagem = false;
    parametros.acao = 'despacharMassa';
    parametros.despachoAnexos = selectFiles;
    parametros.id_item_menu = 229288;

    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/processo/processar',
            parametros
        );

        var processos = resp.data.data;
        if (processos.despachados.length > 0) {
            toast.add({severity: 'success', summary: 'Processos Despachados', detail: `${processos.despachados.length} processos despachados!`});
        }

        if (processos.erros.length > 0) {
            processos.erros.forEach((elemento) => {
                toast.add({severity: 'error', summary: 'Falha ao despachar', detail: `Processo ${elemento.processo.numero} ---> ${elemento.mensagem}`});
            })
        }

        upload.value.clear();
        modalLoading.value = false;
        despachoPublicoTexto.value = "";
        despachoPublico.value = opcoes.value[0];
        emit('buscarProcessos');
        emit('fecharDialog');
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

const generateBlob = async (fileBuffer, type) => {
    return new Blob([fileBuffer], {type});
};

const covertBlobToBase64 = (blob) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onload = () => {
            resolve(reader.result);
        }
        reader.onerror = () => {
            reject()
        }
    });
}
</script>

<template>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />

    <div class="despacho-publico">
        <div style="display: flex; justify-content: center; align-items: center; gap: 5px;">
            <p>Despacho Público:</p>
            <Dropdown v-model="despachoPublico" :options="opcoes" optionLabel="name"/>
        </div>
        <Button @click="confirm3()" class="btn-acoes" label="Despachar" icon="pi pi-file-import" iconPos="right" />
    </div>

    <div>
        <trumbowyg
            v-model="despachoPublicoTexto"
            :config="editor.config"
            name="content">
            required
        </trumbowyg>

        <Fieldset
            legend="Anexar Documentos"
            style="margin-top:10px"
        >
            <FileUpload name="anexos[]"
                        ref="upload"
                        style="height:400px"
                        url=""
                        @upload="()=>{}"
                        @select="atualizarListaArquivos"
                        @remove="atualizarListaArquivos"
                        :multiple="true"
                        accept="application/pdf,image/png,image/jpeg,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        @clear="atualizarListaArquivos"
            >
                <template #header="{ chooseCallback, uploadCallback, clearCallback, files }">
                    <div class="flex flex-wrap justify-content-between align-items-center flex-1 gap-2">
                        <div class="flex gap-2">
                            <Button
                                @click="chooseCallback()"
                                icon="pi pi-file-pdf"
                                class="p-button-rounded"
                                label="Selecione"
                                ref="btnSelecionarArquivo"
                            />

                            <Button @click="clearCallback()"
                                    icon="pi pi-trash"
                                    class="p-button-rounded p-button-danger"
                                    label="Limpar"
                                    :disabled="!files || files.length === 0"
                                    ref="btnRemoverArquivo"
                            />
                        </div>
                    </div>
                </template>

                <template #empty>
                    <p>Arraste seus arquivos ou selecione</p>
                </template>
            </FileUpload>
        </Fieldset>
    </div>
</template>

<style scoped>
.btn-acoes {
    display: flex !important;
    border-radius: 2rem;
}

.despacho-publico {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    padding: 5px 10px;
}

.despacho-publico p {
    margin-right: 10px;
}
</style>
