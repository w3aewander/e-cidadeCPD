<template>
    <Toast position="center" />

    <Fieldset
        legend="Processar"
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
        <Column field="nome" header="Usuário" :sortable="true"></Column>
    </DataTable>
    <div class="flex justify-content-center">
        <Button label="Processar" icon="pi pi-upload" class="mt-2" @click="processar" />
    </div>
    </Fieldset>
</template>

<script setup>
import { useToast } from "primevue/usetoast";
import { onMounted, reactive, ref } from "vue";

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
            `v4/api/tributario/issqn/simples-nacional/listar-importacoes-divida-ativa`
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



async function processar() {
    if (!selecionado.value) {
        alert('Selecione uma linha primeiro!');
        return;
    }
    dados.loading = true;

    const body = {
        q203_sequencial: selecionado.value.q203_sequencial,
    };

    try {
        await window.axios.post(
            `v4/api/tributario/issqn/simples-nacional/processar-divida-ativa`,
            body
        ).then((data) => {    
                    toast.add({
                        severity: "success",
                        summary: "Sucesso",
                        detail: "Arquivo Processado com sucesso!",
                    });
                    getGrid();
                })
                .catch((erro) => {
                    toast.add({
                        severity: "error",
                        summary: "Erro",
                        detail: erro.response.data.message,
                    });
                });
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

</script>
