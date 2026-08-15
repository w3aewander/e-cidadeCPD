<template>
    <Toast/>
    <ModalLoading :is-loading="modalLoading"/>
    <div class="col-10 d-flex mt-7 ml-auto mr-auto">
        <Fieldset legend="Manutenção de Atividade">
            <InputPesquisaUsuario
                :instituicao_sessao="true"
                @select="selectUsuario"
                label="Usuário que possui permissão"
            />

            <InputPesquisaUsuario
                :instituicao_sessao="true"
                label="Usuário que vai receber Permissão"
                @select="selectUsuarioPermissao"
            />
        </Fieldset>
    </div>
    <div class="col-10 d-flex mt-2 ml-auto mr-auto">
        <Button
            label="Atribuir Permissão"
            v-if="selectPermissoesUsuarios.length && usuarioReceberPermissao"
            icon="pi pi-unlock"
            class="p-button-success p-button-rounded"
            iconPos="right"
            @click="atribuirPermissao"
        />
        <Button
            label="Remover Permissão"
            icon="pi pi-trash"
            class="p-button-danger p-button-rounded"
            v-if="selectPermissoesUsuarios.length && usuarioPermissao"
            iconPos="right"
            @click="removerPermissao"
        />
        <Button
            label="Substituir Permissão"
            icon="pi pi-sort-alt"
            class="p-button-rounded"
            v-if="selectPermissoesUsuarios.length && usuarioPermissao && usuarioReceberPermissao"
            iconPos="right"
            @click="substituirPermissao"
        />
    </div>
    <div class="col-10 mt-3 ml-auto mr-auto">
        <DataTable
            :value="permissoesUsuarios"
            :loading="loading"
            v-model:selection="selectPermissoesUsuarios"
            dataKey="p116_codigo"
            paginator
            :rows="10"
            :rowsPerPageOptions="[10, 20, 50]"
            sortMode="multiple"
            filterDisplay="menu"
            v-model:filters="filters"

        >
            <template #header>
                <div class="flex justify-content-between">
                    <Button type="button"
                            icon="pi pi-filter-slash"
                            label="Limpar filtro da tabela"
                            outlined
                            @click="clearFilter()"/>
                </div>
            </template>
            <template #empty> Nenhum documento encontrado.</template>
            <Column selectionMode="multiple"
                    headerStyle="width: 3rem"></Column>
            <Column field="p116_codigo"
                    header="Código"
                    sortable="true"
                    filterMatchMode="startsWith">
                <template #body="{ data }">
                    {{ data.p116_codigo }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value"
                               type="text"
                               @input="filterCallback()"
                               class="p-column-filter"
                               placeholder="Código"/>
                </template>
            </Column>
            <Column field="numero_documento"
                    header="Número"
                    sortable="true">
                <template #body="{ data }">
                    {{ data.numero_documento }}
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value"
                               type="text"
                               @input="filterCallback()"
                               class="p-column-filter"
                               placeholder="Número"/>
                </template>
            </Column>
            <Column field="documento"
                    header="Documento"
                    sortable="true">
                <template #body="{ data }">
                    {{ data.documento }}
                </template>
                <template #filter="{ filterModel }">
                    <Dropdown v-model="filterModel.value"
                              :options="tiposDeDocumentos()"
                              placeholder="Selecione o documento"
                              class="p-column-filter"
                              showClear>
                    </Dropdown>
                </template>
            </Column>
            <Column field="p114_atividade"
                    header="Atividade"
                    sortable="true">
                <template #body="{ data }">
                    {{ data.p114_atividade }}
                </template>
                <template #filter="{ filterModel }">
                    <Dropdown v-model="filterModel.value"
                              :options="tiposDeAtividades()"
                              placeholder="Selecione a atividade"
                              class="p-column-filter"
                              showClear>
                    </Dropdown>
                </template>
            </Column>
            <Column field="login"
                    header="Usuário"></Column>
            <Column field="nome"
                    header="Nome"></Column>
        </DataTable>
    </div>
</template>

<script setup>
import {onMounted, ref} from 'vue';
import {useToast} from 'primevue/usetoast';
import ModalLoading from "../../../Components/ModalLoading";
import InputPesquisaUsuario from '../../../Configuracao/Components/InputPesquisaUsuario'
import {FilterMatchMode} from "primevue/api";

const usuarioPermissao = ref(null);
const usuarioReceberPermissao = ref(null);
const loading = ref(false);
const permissoesUsuarios = ref([]);
const selectPermissoesUsuarios = ref([]);
const modalLoading = ref(false);
const toast = useToast();

const filters = ref();

const initFilters = () => {
    filters.value = {
        p116_codigo: {value: null, matchMode: FilterMatchMode.CONTAINS},
        numero_documento: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
        documento: {value: null, matchMode: FilterMatchMode.EQUALS},
        p114_atividade: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
    }
}
const tiposDeDocumentos = () => {
    const tipos = [];
    permissoesUsuarios.value.map(item => {
        if (!tipos.includes(item.documento)) {
            tipos.push(item.documento);
        }
    });

    return tipos;
}

const tiposDeAtividades = () => {
    const tipos = [];
    permissoesUsuarios.value.map(item => {
        if (!tipos.includes(item.p114_atividade)) {
            tipos.push(item.p114_atividade);
        }
    });

    return tipos;
}

const selectUsuario = (usuario) => {
    usuarioPermissao.value = usuario
    if (!usuario || usuario.id_usuario) {
        searchDocumentos();
    }
}

const selectUsuarioPermissao = (usuario) => {
    usuarioReceberPermissao.value = usuario;
}
const searchDocumentos = async () => {

    initFilters();
    loading.value = true;
    try {
        const id_usuario = usuarioPermissao.value ? usuarioPermissao.value.id_usuario : null;
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/documentos/atividade-em-execucao/usuario/${id_usuario}`
        );
        permissoesUsuarios.value = await resp.data.data;
    } catch (e) {
        permissoesUsuarios.value = [];
    }
    selectPermissoesUsuarios.value = [];
    loading.value = false;
}

const atribuirPermissao = async () => {

    if (!usuarioReceberPermissao.value.id_usuario) {
        toast.add({
            severity: 'info',
            detail: "Selecione um usuário para atribuir permissão",
        });
        return;
    }

    if (selectPermissoesUsuarios.value.length < 1) {
        toast.add({
            severity: 'info',
            detail: "Selecione no minímo uma permissão",
        });
        return;
    }
    const form = {permissoes: []};
    selectPermissoesUsuarios.value.forEach(el => {
        form.permissoes.push({
            processo: el.p116_protprocesso,
            atividadeexecuacao: el.p118_atividadesexecucao,
            usuario: usuarioReceberPermissao.value.id_usuario
        });
    });

    modalLoading.value = true;
    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/documentos/atribuir-permissao',
            form
        );
        const data = await resp.data;
        modalLoading.value = false;
        toast.add({
            severity: 'success',
            detail: "Atribuído com sucesso!",
        });
        await searchDocumentos();
    } catch (e) {
        modalLoading.value = false;
        if (e.response.status === 402) {
            let keys = Object.keys(e.response.data);
            toast.add({
                severity: 'warn',
                detail: e.response.data[keys[0]].join(""),
            });
            return;
        }
        toast.add({
            severity: 'error',
            detail: e.response.data[keys[0]].join(""),
        });
    }
}

const removerPermissao = async () => {

    if (!usuarioPermissao.value.id_usuario) {
        toast.add({
            severity: 'info',
            detail: "Selecione um usuário para remover permissão",
        });
        return;
    }

    if (selectPermissoesUsuarios.value.length < 1) {
        toast.add({
            severity: 'info',
            detail: "Selecione no minímo uma permissão",
        });
        return;
    }
    const form = {permissoes: []};
    selectPermissoesUsuarios.value.forEach(el => {
        form.permissoes.push({
            processo: el.p116_protprocesso,
            atividadeexecuacao: el.p118_atividadesexecucao,
            usuario: usuarioPermissao.value.id_usuario
        });
    });

    modalLoading.value = true;
    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/documentos/remover-permissao',
            form
        );
        const data = await resp.data;
        modalLoading.value = false;
        toast.add({
            severity: 'success',
            detail: "Removido com sucesso!",
        });
        await searchDocumentos();
    } catch (e) {
        modalLoading.value = false;
        if (e.response.status === 402) {
            let keys = Object.keys(e.response.data);
            toast.add({
                severity: 'warn',
                detail: e.response.data[keys[0]].join(""),
            });
            return;
        }
        toast.add({
            severity: 'error',
            detail: "Ocorreu um erro",
        });
    }
}

const substituirPermissao = async () => {

    if (!usuarioPermissao.value.id_usuario) {
        toast.add({
            severity: 'info',
            detail: "Selecione o usuário que terá permissão substituída",
        });
        return;
    }


    if (selectPermissoesUsuarios.value.length < 1) {
        toast.add({
            severity: 'info',
            detail: "Selecione no minímo uma permissão",
        });
        return;
    }

    if (!usuarioReceberPermissao.value.id_usuario) {
        toast.add({
            severity: 'info',
            detail: "Selecione um usuário para atribuir permissão",
        });
        return;
    }

    const form = {permissoes: []};
    selectPermissoesUsuarios.value.forEach(el => {
        form.permissoes.push({
            processo: el.p116_protprocesso,
            atividadeexecuacao: el.p118_atividadesexecucao,
            usuario: usuarioPermissao.value.id_usuario,
            usuario_receber: usuarioReceberPermissao.value.id_usuario
        });
    });
    modalLoading.value = true;
    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/documentos/substituir-permissao',
            form
        );
        const data = await resp.data;
        modalLoading.value = false;
        toast.add({
            severity: 'success',
            detail: "Substituído com sucesso!",
        });
        await searchDocumentos();
    } catch (e) {
        modalLoading.value = false;
        if (e.response.status === 402) {
            let keys = Object.keys(e.response.data);
            toast.add({
                severity: 'warn',
                detail: e.response.data[keys[0]].join(""),
            });
            return;
        }
        toast.add({
            severity: 'error',
            detail: "Ocorreu um erro",
        });
    }
}

const clearFilter = () => {
    initFilters();
};

onMounted(() => {
    searchDocumentos();
})
</script>

<style scoped>

</style>
