<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';

//services
const toast = useToast();

//emits
const emits = defineEmits(['rowSelected', 'close']);

//data
const cadastros = ref();
const filters = ref({
    nome: '',
    sequencial: ''
});
const isLoading = ref(false);
         
//methods
const pesquisarCadastros = async() => {
    isLoading.value = true;
    const urlCadastros = 'v4/api/tributario/issqn/cadastro-endereco/busca-endereco'

    try {
        const response = await axios.get(urlCadastros, {
            params: filters.value
        });

        const { data: resp } = response.data;
        cadastros.value = resp ? resp : {data: null};
    } catch (e) {
        toast.add({severity: 'error', summary: 'Erro ao buscar cadastros.', detail: e.message, life: 5000 })
    } finally {
        isLoading.value = false;
    }
}

const limparCampos = () => {
    filters.value.nome = "";
    filters.value.sequencial = "";
}

const onRowSelect = (event) => {
    emits('rowSelected', event.data);
    emits('close');
}

onMounted(() => {
    pesquisarCadastros();
});

</script>

<template>
   <section style="width: 100%" class="m-auto">
       <DataTable
            :value="cadastros"
            paginator
            :rows="10" 
            :loading="isLoading"
            stripedRows
            selectionMode="single"
            dataKey="sequencial"
            @row-select="onRowSelect"
        >
           <template #empty> Nenhum resultado foi encontrado </template>
           <template #header>
               <form class="m-auto flex flex-col justify-content-center items-center">
                   <span class="p-input-icon m-2 flex flex-column">
                       <label for="inscricao">Inscrição:</label>
                       <InputText
                           :useGrouping="false"
                           placeholder=""
                           class="padding-custom p-inputtext-sm shadow-4"
                           v-model="filters.sequencial"
                           type="number"
                           min="1"
                       />
                   </span>

                    <span class="p-input-icon m-2 flex flex-column">
                       <label for="nome">Nome:</label>
                       <InputText
                           :useGrouping="false"
                           placeholder=""
                           class="padding-custom p-inputtext-sm shadow-4"
                           v-model="filters.nome"
                       />
                   </span>
               </form>

               <div class="m-auto flex justify-content-center flex-wrap">
                   <Button @click="pesquisarCadastros" class="m-2 p-button-success shadow-4">
                       <i class="pi pi-search"/>
                   </Button>

                   <Button @click="limparCampos" class="m-2 shadow-4">
                       <i class="pi pi-undo"/>
                   </Button>

                   <Button @click="emits('close')" class="m-2 p-button-danger shadow-4">
                       <i class="pi pi-times"/>
                   </Button>
               </div>
           </template>

        <Column field="sequencial" header="Sequencial" style="width: 13%;" sortable/>
        <Column field="nome" header="Nome" style="width: 48%;" sortable/>
        <Column field="destinatario" header="Destinatário" style="width: 48%;" sortable/>
        <Column field="rualabel" header="Endereço" style="width: 48%;" sortable/>
        <Column field="municipal" header="Municipal?" style="width: 48%;" sortable>
            <template #body="slotProps">
                {{ slotProps.data.municipal ? 'Sim' : 'Não' }}
            </template>
        </Column>

       </DataTable>

   </section>
</template>

<style scoped>
:deep(.inputPesquisa .p-multiselect .p-multiselect-trigger) {
    color: white;
}

:deep(.inputPesquisa .p-placeholder) {
    color: white;
}

:deep(.inputPesquisa .p-multiselect) {
    border-color: #4a789c;
}
</style>