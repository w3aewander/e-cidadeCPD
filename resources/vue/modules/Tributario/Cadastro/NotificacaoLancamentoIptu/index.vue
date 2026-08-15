<script setup>
import { ref } from 'vue';
import InputSearchMatricula from '../components/InputSearchMatricula.vue';
import DialogConsultaMatriculaImovel from '@modules/Tributario/Arrecadacao/components/DialogConsultaMatriculaImovel.vue';
import { formatDateToBrazilian } from '../../../../utils/Strings';

// data
const filters = ref({
    matricula: null,
    exercicio: new Date().getFullYear()
});
const matriculaLabel = ref('');
const notificacoes = ref([]);
const solicitanteVisible = ref(false);
const dadosVisible = ref(false);
const isFetching = ref(false);
const fetchingDoc = ref(false);
const currentNotificacao = ref();
const dadosDoc = ref(null);
const motivoInvalidacao = ref(false);
const pdfurl = ref(false);
const pdfviewer = ref(false);

// methods
const selectMatricula = (data) => {
    matriculaLabel.value = data.nome;
    filters.value.matricula = data.matricula;
}

const openDialogSolicitante = (notificacao) => {
    currentNotificacao.value = notificacao;
    solicitanteVisible.value = true;
}

const openDialogDados = (notificacao) => {
    currentNotificacao.value = notificacao;
    dadosDoc.value = JSON.parse(notificacao.dados);
    dadosVisible.value = true;
}

const getNotificacoes = async () => {
    const url = 'v4/api/tributario/cadastro/notificacao-lancamento-iptu/listar';
    isFetching.value = true;

    try {
        const req = await axios.get(url, {params: filters.value});
        const { data: resp } = req;
        notificacoes.value = resp.data;
    } catch (error) {
        if (error.response) {
            let response = error.response;
            if (response.status == 400) {
                alert(response.data.message);
                return;
            }
        }
        alert('Erro ao buscar notificações');
        console.error(error)
    } finally {
        isFetching.value = false;
    }
}

const getDocument = async (uuid) => {
    const url = 'v4/api/tributario/cadastro/notificacao-lancamento-iptu/download';
    const params = { uuid }

    try {
        fetchingDoc.value = uuid;
        const response = await CurrentWindow.axios.get(url, {params});
        const notificacao = response.data.data;
        pdfurl.value = notificacao.file;
        pdfviewer.value = true;
    } catch (error) {
        if (error.response) {
            let response = error.response;
            if (response.status == 400) {
                alert(response.data.message);
                return;
            }
        }
        alert('Erro ao buscar documento');
        console.error(error);
    } finally {
        fetchingDoc.value = false;
    }
}
</script>

<template>
    <Dialog v-model:visible="pdfviewer" modal>
        <iframe :src="pdfurl" frameborder="0" style="width: 80vw; height: 80vh;"></iframe>
    </Dialog>
    <Dialog v-model:visible="solicitanteVisible" modal header="Solicitante" position="top">
       <div class="p-4" style="width: 200px;">
            IP: <b>{{ currentNotificacao.ip }}</b>
            <Divider/>
            CPF/CNPJ: <b>{{ currentNotificacao.cgccpf }}</b>
            <Divider/>
            Telefone: <b>{{ currentNotificacao.telfone }}</b>
            <Divider/>
            Cliente: <b>{{ currentNotificacao.usutipo == 1 ? 'Processo Eletrônico' : 'Chat BOT' }}</b>
       </div>
    </Dialog>
    <Dialog v-model:visible="motivoInvalidacao" modal header="Motivo" position="top">
       <div class="p-3">
         <p>{{ motivoInvalidacao }}</p>
       </div>
    </Dialog>
    <Dialog v-model:visible="dadosVisible" modal header="Dados" position="top" style="min-width: 600px;">
        <div class="p-3" v-if="dadosDoc">
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <tr v-for="(value, key) in dadosDoc" :key="key">
                    <td class="bg-gray-100 p-1" v-if="key !== 'calculos'">{{ key }}</td>
                    <td class="p-1" v-if="key !== 'calculos'">{{ value }}</td>
                </tr>
            </table>
            <Divider/>
            <h4>Cálculos:</h4>
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td class="p-1 bg-gray-100">Descrição</td>
                    <td class="p-1 bg-gray-100">Valor</td>
                    <td class="p-1 bg-gray-100">Isenção</td>
                </tr>
                <tr v-for="(value, key) in dadosDoc.calculos" :key="key">
                    <td class="p-1">
                        {{ value.calc_descricao }}
                    </td>
                    <td class="p-1">
                        {{ value.calc_valor }}
                    </td>
                    <td class="p-1">
                        {{ value.calc_isencao ?? '0.00' }}
                    </td>
                </tr>
            </table>
       </div>
    </Dialog>
    <div class="container mt-5">
        <Panel header="Filtro" class="w-6 m-auto">
        <div class="grid">
            <div class="col-12 flex flex-column gap-2 align-items-start">
                <a href="javascript:;" @click="$refs.dialogConsultaMatricula.toggleDialog()" class="link">
                    Matricula
                </a>
                <InputSearchMatricula v-model:id="filters.matricula" v-model:description="matriculaLabel" />
                <DialogConsultaMatriculaImovel ref="dialogConsultaMatricula" @selectRow="selectMatricula"/>
            </div>
            <div class="col-12 flex flex-column gap-2">
                <label for="exercicio">Exercicio: </label>
                <InputNumber :format="false" showButtons v-model="filters.exercicio" />
            </div>

            <div class="col-12">
                <Button label="Pesquisar"
                    icon="pi pi-search"
                    @click="getNotificacoes"
                    :loading="isFetching"
                />
            </div>
        </div>
        </Panel>

        <Divider/>

        <Card style="width: max-content;" v-if="notificacoes.length">
            <template #content>
                <DataTable :value="notificacoes" :loading="isFetching">
                    <Column header="Status">
                    <template #body="{ data }">
                        <div class="flex gap-1 align-items-center">
                            <Tag value="Visualizado" severity="success" v-if="data.visualizado"/>
                            <Tag value="Não visualizado" severity="warning" v-if="!data.visualizado"/>
                            <Tag value="Inválida" severity="danger" v-if="data.invalida || data.vencida"/>
                        </div>
                    </template>
                    </Column>
                    <Column field="sequencial" header="ID"></Column>
                    <Column field="matricula" header="Matricula"></Column>
                    <Column field="exercicio" header="Exercicio"></Column>
                    <Column header="Data de Emissao">
                        <template #body="{ data}">
                            {{ formatDateToBrazilian(data.datahoraemiss, true) }}
                        </template>
                    </Column>
                    <Column header="Data Visualização">
                        <template #body="{ data }">
                            {{ formatDateToBrazilian(data.datahoravisualizacao, true) }}
                        </template>
                    </Column>
                    <Column>
                        <template #body="{ data }">
                            <div class="flex gap-1">
                                <Button
                                    icon="pi pi-align-justify"
                                    outlined
                                    label="Dados"
                                    class="text-xs"
                                    @click="openDialogDados(data)">
                                </Button>
                                <Button
                                    icon="pi pi-user"
                                    label="Solicitante"
                                    outlined
                                    class="text-xs"
                                    @click="openDialogSolicitante(data)">
                                </Button>
                                <Button severity="danger"
                                    v-if="data.invalida"
                                    icon="pi pi-close"
                                    label="Motivo Invalidação"
                                    outlined
                                    class="text-xs"
                                    @click="motivoInvalidacao = data.motivoinvalidacao">
                                </Button>
                                <Button
                                    icon="pi pi-file-pdf"
                                    label="Documento"
                                    class="text-xs"
                                    @click="getDocument(data.uuid)"
                                    :loading="fetchingDoc == data.uuid">
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>

<style scoped>
.link {
    color: #4a789c;
    font-weight: bold;
}
</style>
