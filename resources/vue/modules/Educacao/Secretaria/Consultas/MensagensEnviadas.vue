<script setup>
import Fieldset from 'primevue/fieldset';
import {onMounted, ref} from "vue";
import {useToast} from "primevue/usetoast";
import Dropdown from 'primevue/dropdown';
import ModalLoading from "@modules/Components/ModalLoading.vue";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import {FilterMatchMode, FilterOperator} from "primevue/api";
import Calendar from 'primevue/calendar';

const escolas = ref({
    data: []
});

const filtersTable = ref();

const initFilters = () => {
    filtersTable.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        nome_aluno: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        data_envio: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.DATE_IS }]}
    }
};

const form = ref({
    slctEscola: []
});
const loading = ref(false);
const mensagemModal = ref(null);
const mensagens = {
    escolas: 'Buscando escolas...',
    mensagens: 'Buscando mensagens...'
};
const notificacoesEnviadas = ref({
    data: [] // dados que serão filtrados e exibidos na tabela
});
const notificacoesEnviadasOriginal = ref({
    data: [] // dados originais que nunca serão alterados
});
const toast = useToast();
const routes = {
    escolas: `v4/api/educacao/secretaria/consultas/mensagens-enviadas/escolas`,
    mensagens: `v4/api/educacao/secretaria/consultas/mensagens-enviadas`
}

async function buscarEscolas() {
    try {
        mensagemModal.value = mensagens.escolas
        loading.value = true;
        const response = await window.axios.get(routes.escolas);

        if (response.data.data.length > 0) {
            escolas.value.data = response.data.data.map(escola => ({
                code: escola.ed18_i_codigo,
                name: escola.ed18_c_nome
            }));
        } else {
            toast.add({
                severity: 'warn',
                summary: 'Atenção!',
                detail: 'Nenhuma notificação enviada!',
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

async function buscarMensagens() {
    mensagemModal.value = mensagens.mensagens;
    loading.value = true;
    const escola = form.value.slctEscola.code;

    try {
        const response = await window.axios.get(`${routes.mensagens}/${escola}`);

        if (response.data.data.length > 0) {
            const dadosMapeados = response.data.data.map(notificacao => ({
                code: notificacao.ed200_codigo,
                id_usuario: notificacao.ed200_usuario_id,
                nome_usuario: notificacao.nome_usuario,
                code_escola: notificacao.ed200_escola_id,
                nome_escola: notificacao.nome_escola,
                code_turma: notificacao.ed200_turma_id,
                nome_turma: notificacao.nome_turma,
                code_aluno: notificacao.ed200_aluno_id,
                nome_aluno: notificacao.nome_aluno,
                metodo_envio: notificacao.metodo_envio,
                telefone_responsavel: notificacao.ed200_telefone_responsavel,
                email_responsavel: notificacao.ed200_email_responsavel,
                mensagem: notificacao.ed201_mensagem,
                data_envio: new Date(notificacao.created_at).toLocaleString('pt-BR', { day: "2-digit", month: "2-digit", year: "numeric"}),
                hora_envio: new Date(notificacao.created_at).toLocaleString('pt-BR', { hour: "2-digit", minute: "2-digit", second: "numeric"})
            }));

            notificacoesEnviadasOriginal.value.data = dadosMapeados;
            notificacoesEnviadas.value.data = dadosMapeados;
        } else {
            toast.add({
                severity: 'warn',
                summary: 'Atenção',
                detail: 'Não foram encontradas mensagens para serem carregadas!',
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
function formatarTelefone(telefone) {
    if (!telefone) return '';
    // Remove caracteres que não são números
    telefone = telefone.replace(/\D/g, '');

    // Adiciona a máscara no formato (XX) XXXXX-XXXX
    if (telefone.length === 11) {
        return telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }

    // Adiciona a máscara no formato (XX) XXXX-XXXX (caso tenha 10 dígitos)
    if (telefone.length === 10) {
        return telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }

    // Se não tiver 10 ou 11 dígitos, retorna sem formatação
    return telefone;
}

const getSeverity = (metodo_envio) => {
    switch (metodo_envio) {
        case 'WhatsApp':
            return 'success';
        case 'SMS':
            return 'error';
        case 'Email':
            return 'warning';
    }
}

function filterDataEnvio(filterModel) {
    const dataSelecionada = filterModel.constraints[0].value.toLocaleString('pt-BR', {
        day: "2-digit",
        month: "2-digit",
        year: "numeric"
    });

    notificacoesEnviadas.value.data = notificacoesEnviadasOriginal.value.data.filter((notificacao) =>
        notificacao.data_envio === dataSelecionada
    );
}

function limpaFiltro(filterModel) {
    notificacoesEnviadas.value.data = notificacoesEnviadasOriginal.value.data;
    filterModel.constraints[0].value = null;
}

onMounted(() => {
    buscarEscolas();
    initFilters();
});

</script>

<template>
    <div class="filters-container">
        <div v-if="escolas.data.length > 0" class="card container-escola">
            <Fieldset>
                <template #legend>
                    <div class="header-escolas flex align-items-baseline gap-3">
                        <i class="pi pi-filter" style="color: white"></i>
                        <span class="font-bold">Escolas</span>
                    </div>
                </template>
                <div class="card flex justify-content-center">
                    <Dropdown
                        v-model="form.slctEscola"
                        :options="escolas.data"
                        filter
                        optionLabel="name"
                        placeholder="Selecione uma Escola"
                        class="w-full md:w-25rem h-2rem"
                        @change="buscarMensagens"
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex align-items-center">
                                <div>{{ slotProps.value.name }}</div>
                            </div>
                            <span v-else>
                                {{ slotProps.placeholder }}
                            </span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex align-items-center">
                                <div>{{ slotProps.option.name }}</div>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </Fieldset>
        </div>
        <div v-if="notificacoesEnviadasOriginal.data.length > 0" class="card mt-5">
            <DataTable
                tableStyle="min-width: 50rem"
                :value="notificacoesEnviadas.data"
                :filters="filtersTable"
                filterDisplay="menu"
                :globalFilterFields="['code','id_usuario', 'nome_usuario', 'code_turma', 'nome_turma',
                'code_aluno', 'nome_aluno', 'metodo_envio', 'telefone_responsavel', 'email_responsavel', 'data_envio']"
                paginator :rows="7"
                :rowsPerPageOptions="[7, 10, 20, 50]"
                dataKey="code"
                :loading="loading"
            >
                <template #header>
                    <div class="flex justify-content-between align-items-center p-2 mr-2 relative">
                        <h2 class="font-span-header relative">Notificações enviadas</h2>
                        <span class="p-input-icon-left flex justify-content-between">
                        <i class="pi pi-search" />
                        <InputText id="pesquisar" v-model="filtersTable['global'].value" placeholder="Pesquisar" />
                    </span>
                    </div>
                </template>
                <template #loading> Carregando dados. Aguarde... </template>
                <Column field="code_turma" header="Cod. Turma" style="min-width: 2rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.code_turma }}
                    </template>
                </Column>
                <Column field="nome_turma" header="Nome da Turma" style="min-width: 8rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.nome_turma }}
                    </template>
                </Column>
                <Column field="code_aluno" header="Cod. Aluno" style="min-width: 2rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.code_aluno }}
                    </template>
                </Column>
                <Column field="nome_aluno" header="Nome do Aluno" style="min-width: 20rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.nome_aluno }}
                    </template>
                </Column>
                <Column field="metodo_envio" header="Método de Envio" style="min-width: 10rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        <Tag :value="data.metodo_envio" :severity="getSeverity(data.metodo_envio)" />
                    </template>
                </Column>
                <Column field="contato_responsavel" header="Contato Responsável" style="min-width: 10rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{
                            data.metodo_envio == 'Email'
                            ? data.email_responsavel
                            : formatarTelefone(data.telefone_responsavel)
                        }}
                    </template>
                </Column>
                <Column field="id_usuario" header="Id Usuário" style="min-width: 2rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.id_usuario }}
                    </template>
                </Column>
                <Column field="nome_usuario" header="Nome Usuário" style="min-width: 20rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.nome_usuario }}
                    </template>
                </Column>
                <Column field="data_envio" header="Data Envio" sortable filterField="data_envio" dataType="date" style="min-width: 8rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.data_envio }}
                    </template>
                    <template #filter="{ filterModel }">
                        <Calendar v-model="filterModel.value" dateFormat="dd/mm/yy" placeholder="dd/mm/yyyy" mask="99/99/9999"/>
                    </template>
                    <template #filterclear="{ filterModel }">
                        <Button type="button" icon="pi pi-times" @click="limpaFiltro(filterModel)" severity="secondary"></Button>
                    </template>
                    <template #filterapply="{ filterModel }">
                        <Button type="button" icon="pi pi-check" @click="filterDataEnvio(filterModel)" severity="success"></Button>
                    </template>
                </Column>
                <Column field="hora_envio" header="Hora Envio" sortable filterField="hora_envio" dataType="date" style="min-width: 8rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.hora_envio }}
                    </template>
                </Column>
                <Column field="mensagem_enviada" header="Mensagem" style="min-width: 20rem" header-style="font-size: 0.8rem">
                    <template #body="{ data }">
                        {{ data.mensagem }}
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
    <ModalLoading :is-loading="loading" :message="mensagemModal"/>
</template>

<style scoped>

.filters-container {
    width: 90vw;
    margin: 4rem auto;
}

fieldset {
    border-radius: 0.6rem;
}

.header-escolas {
    color: #FFF;
}

.container-escola {
    margin: 0 auto;
    width: 60%;
}

h2 {
    color: #0C3366;
    font-size: 1.5rem;
    margin: 0 0 0 2rem;
    padding: 0;
}

</style>
