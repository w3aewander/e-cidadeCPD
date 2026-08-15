<script setup>
import { ref } from 'vue';
import { toCnpj } from '@utils/Strings';

// emits
const emit = defineEmits(['select', 'close']);

// data
const selectedEmpresa = ref(null);
const filters = ref({cgccpf: null, numcgm: null, numeroprocesso: null});
const dadosEmpresa = ref([]);
const isLoadingEmpresas = ref(false);

// methods
async function getMassasFalidas() {

    const url = 'v4/api/tributario/issqn/massafalida';

    try {
        isLoadingEmpresas.value = true;
        const response = await axios.get(url, {
            params: formattedFilters()
        });
        if (response.status === 200) {
            const { data: resp } = response;

        dadosEmpresa.value = resp.data

        } else {
            console.error('Erro: Status de resposta não esperado:', response.status);
        }
    } catch (e) {
        return console.error('Erro ao fazer a requisição:', e.message);
    } finally {
        isLoadingEmpresas.value = false;
    }
}

const formattedFilters = () => {
    return {
        numcgm: filters.value.numcgm?.trim().replace(/\D/g, ''),
        cgccpf: filters.value.cgccpf?.trim().replace(/\D/g, ''),
        numeroprocesso: filters.value.numeroprocesso?.trim()
    }
}

const clearFields = () => {
    filters.value.cgccpf = '';
    filters.value.numcgm = '';
    filters.value.numeroprocesso = '';
}

function onRowSelect() {
  emit('select', selectedEmpresa.value);
}
</script>

<template>
    <DataTable 
    v-model:selection="selectedEmpresa" 
    :value="dadosEmpresa" 
    :loading="isLoadingEmpresas"
    paginator
    :rows="15"
    selectionMode="single" 
    @rowSelect = "onRowSelect"
    tableStyle="min-width: 40rem">
        <template #header>
            <div class="flex justify-content-center gap-2">
                <div class="flex flex-column">
                    <label>CNPJ</label>
                    <InputText v-model="filters.cgccpf"/>
                </div>
                <div class="flex flex-column">
                    <label>Nº CGM</label>
                    <InputText v-model="filters.numcgm"/>
                </div>
                <div class="flex flex-column">
                    <label>Nº Processo</label>
                    <InputText v-model="filters.numeroprocesso"/>
                </div>
            </div>

                <div class="flex justify-content-center gap-2 mt-3 mb-2">
                    <Button severity="success" icon="pi pi-search" @click='getMassasFalidas'/>
                    <Button icon="pi pi-undo" @click="clearFields"/>
                    <Button severity="danger" icon="pi pi-times" @click="emit('close')"/>
                </div>

        </template>

        <Column field="numcgm" header="CGM">
            <template #body = "slotProps">
                {{ JSON.parse(slotProps.data.empresas)[0].numcgm }}
            </template>
        </Column>
        <Column field="empresa" header="Empresa">
            <template #body = "slotProps">
                {{ JSON.parse(slotProps.data.empresas)[0].empresa }}
            </template>
        </Column>
        <Column field="numeroprocesso" header="Nº Processo"></Column>
        <Column field="cgccpf" header="CNPJ">
            <template #body = "slotProps">
                {{ JSON.parse(slotProps.data.empresas)[0].cgccpf }}
            </template>
        </Column>

        <template #empty>
            Nenhum registro encontrado
        </template>

    </DataTable>

</template>
