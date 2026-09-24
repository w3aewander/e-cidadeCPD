<script setup>
import { ref, onMounted } from 'vue';
import HistoricoService from '../Services/HistoricoService';
import { useToast } from 'primevue/usetoast';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';

// service
const toast = useToast();

// props
const props = defineProps({
    codMovimentacao: {type: Object},
});

// data
const historico = ref();
const loadingHistorico = ref(false);

const buscarMovimentacao = async () => {
    try {
        loadingHistorico.value = true;
        historico.value = await HistoricoService.get({
            codMovimentacao: props.codMovimentacao
        });

        historico.value = historico.value.map(obj => ({
            data: obj.datamov.split("-").reverse().join("/"),
            datahora: formatarDataHora(obj.datahora),
            usuario: `${obj.id} - ${obj.usuario}`,
            tipo: obj.tipo,
        }));
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao buscar movimentações', life: 3000 });
    } finally {
        loadingHistorico.value = false;
    }
}

const formatarDataHora = (dataHora) => {
    const [data, hora] = dataHora.split(" ");
    const [ano, mes, dia] = data.split("-");
    const [horas, minutos] = hora.split(":");
    return `${dia}/${mes}/${ano} às ${horas}:${minutos}`;
}

onMounted(() => {
  buscarMovimentacao();
});
</script>

<template>
    <DataTable
        tableStyle="width: 55rem;"
        maximizable
        modal
        :value="historico"
        :loading="loadingHistorico"
    >
        <ColumnGroup type="header">
        <Row>
            <Column header="Dados" :colspan="2" style="background: #4a789c00!important; color: #4a789c!important"/>
            <Column header="Alterações" :colspan="2" style="background: #4a789c00!important; color: #4a789c!important"/>
        </Row>
        <Row>
            <Column header="Tipo" sortable field="tipo" />
            <Column header="Data da Movimentação" sortable field="data" />
            <Column header="Responsável" sortable field="usuario" />
            <Column header="Data da Alteração" sortable field="datahora" />
        </Row>
        </ColumnGroup>
            <Column field="tipo" sortable></Column>
            <Column field="data" sortable style="text-align: center"></Column>
            <Column field="usuario" class="content-details"></Column>
            <Column field="datahora" sortable></Column>
    </DataTable>
</template>

<style scoped>
:deep(.p-datatable-wrapper) {
    margin-top: 1rem;
}
</style>