<template>
    <Toast position="center" />

    <Fieldset
        legend="Consultar"
        class="m-auto mt-5 w-10 shadow-4"
    >
    <DataTable
        :value="dados.grid"
        :loading="dados.loading"
        :paginator="true"
        :rows="10"
        selectionMode="single"
        v-model:selection="selecionado"
        dataKey="q203_sequencial"
    >
        <Column field="q203_nome" header="Nome" :sortable="true"></Column>
        <Column field="q203_sequencial" header="Sequencial" :sortable="true"></Column>
        <Column field="q203_geracao" header="Geração" :sortable="true"></Column>
        <Column field="created_at" header="Importado" :sortable="true"></Column>
        <Column field="updated_at" header="Processada" :sortable="true"></Column>
        <Column field="nome" header="Usuário" :sortable="true"></Column>
    </DataTable>
    <div class="flex justify-content-center">
        <Button label="Consultar" icon="pi pi-upload" class="mt-2" @click="consultar" />
    </div>
    </Fieldset>
    <DialogDividaSimplesNacional ref="dialog" :selecionado="selecionado"/>

</template>

<script setup>
import { useToast } from "primevue/usetoast";
import { onMounted, reactive, ref } from "vue";
import DialogDividaSimplesNacional from "./Components/DialogDividaSimplesNacional.vue";

const dialog = ref(null);
const toast = useToast();
const props = defineProps(["usuario"]);
const dados = reactive({
    grid: [],
    loading: false,
});
const selecionado = ref(null);

async function getGrid() {
    dados.loading = true;
    try {
        const response = await window.axios.post(
            `v4/api/tributario/issqn/simples-nacional/listar-processamentos-divida-ativa`
        );
        dados.grid = response.data.data;
        dados.loading = false;
    } catch (error) {
        dados.loading = false;
        toast.add({
            severity: "error",
            summary: "Erro",
            detail: "Algo deu errado",
        });
    }
}

onMounted(async () => {
    await getGrid();
});

async function consultar() {

    if (selecionado.value) {
        dialog.value.toggleDialog();

    } else {
        toast.add({
            severity: "warn",
            summary: "Atenção",
            detail: "Selecione um item",
            life: 3000,
        });
    }
}

</script>


