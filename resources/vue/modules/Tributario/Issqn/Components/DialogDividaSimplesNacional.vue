<template>
  <Dialog v-model:visible="data.dialogAberto" :style="{width: '800px'}" :maximizable="true" :modal="true"
    header="Consulta Lista" position="top" class="shadow-4">
    <div class="p-fluid">
      <div class="mb-3"></div>
      <DataTable :value="data.dividas" :lazy="true" :totalRecords="data.totalRecords" :loading="data.loading" @page="onPage($event)"
        :paginator="true" :rows="10" responsiveLayout="scroll"
        dataKey="id"
        >
        <Column field="cgm" header="CGM"></Column>
        <Column field="nome" header="Nome"></Column>
        <Column field="cnpj" header="CNPJ"></Column>
        <Column field="venc" header="Vencimento">
          <template #body="{data}">
            {{ new Date(data.venc).toLocaleString() }}
          </template>
        </Column>
        <Column field="parc" header="Parcela"></Column>
        <Column field="exerc" header="Exercício"></Column>
        <Column field="valor" header="Valor">
          <template #body="{data}">
            {{ data.valor.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'}) }}
          </template>
        </Column>
      </DataTable>
    </div>
  </Dialog>
</template>

<script setup>
import { reactive, ref } from 'vue';

const props = defineProps({
    selecionado: {
        type: Number,
        required: true
    }
});

const data = reactive({
    dialogAberto: false,
    loading: false,
    dividas: [],
    totalRecords: 0,
    offset: 0,
    q203_sequencial: 0,
});

async function getDividas() {
    if (data.dialogAberto && data.q203_sequencial !== 0) {
        data.loading = true;
        const { data: response } = await window.axios.get(`v4/api/tributario/issqn/simples-nacional/consultar-processamentos-divida-ativa?q203_sequencial=${data.q203_sequencial}&page=${Math.ceil(data.offset / 10) + 1}`);
        data.dividas = response.data.results;
        data.totalRecords = response.data.total;
        data.loading = false;
    }
}

function onPage(event) {
    data.offset = event.first;
    getDividas();
}

function toggleDialog() {
    data.dialogAberto = !data.dialogAberto;
    if (data.dialogAberto) {
        data.q203_sequencial = props.selecionado.q203_sequencial || 0;
        getDividas();
    } 
}

defineExpose({
    toggleDialog
});
</script>

