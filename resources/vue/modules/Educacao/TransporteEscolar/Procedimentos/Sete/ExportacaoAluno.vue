<script setup>
import { ref,computed } from "vue";
import { useToast } from 'primevue/usetoast';
import ModalLoading from "../../../../Components/ModalLoading.vue"
import MultiDownload from "../../../../Components/MultiDownload.vue";

const download = ref(null);
const props = defineProps(['exercicio']);
const selectedCompetencia = ref();

const competencia = computed(() => {
    return Array.from({ length: 5 }, (_, index) => {
      const year = props.exercicio - index;
      return {
        name: year.toString(),
        value: year.toString(),
        selected: year === props.exercicio,
      };
    });
});

selectedCompetencia.value = competencia.value[0];

const loading = ref(false);
const toast = useToast();
const routes = {
    exportarAlunos:`v4/api/educacao/transporte-escolar/sete/exportar-alunos`,
    verificarInconsistencias:`v4/api/educacao/transporte-escolar/sete/verificar-inconsistencias-alunos`,
    relatorioInconsistencias:`v4/api/educacao/transporte-escolar/sete/relatorio-inconsistencias-alunos`
};

const visibleDialog = ref(false);

const verificarInconsistencias = async () => {
    loading.value = true;
    try{
        let parametros = {};
        parametros.competencia = selectedCompetencia.value.value;
        let response = (await window.axios.post(routes.verificarInconsistencias,parametros)).data;

        if(response.data){
            visibleDialog.value = true;
            loading.value = false; 
        } else {
            exportarAlunos();
        }
                      
    } catch(e){
        loading.value = false;
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: e.response.data.message,
            life: 5000
        });
    }       
}

const gerarRelatorioInconsistenciasAlunos = async () => {
    loading.value = true;
    try{
        let parametros = {};
        parametros.competencia = selectedCompetencia.value.value;
        let response = (await window.axios.post(routes.relatorioInconsistencias,parametros)).data;
        if(!response.error){
            loading.value = false;
            toast.add({ 
                severity: 'success',
                summary: 'Concluído!', 
                detail: response.message, 
                life: 3000 
            });
            download.value.addFile(
                `${response.data.url}`,
                `${response.data.name}`
            );
            download.value.openModal();             
        }
             
    } catch(e){
        loading.value = false;
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: e.response.data.message,
            life: 5000
        });
    }       
}

const exportarAlunos = async () => {
    loading.value = true;
    try{
        let parametros = {};
        parametros.competencia = selectedCompetencia.value.value;
        let response = (await window.axios.post(routes.exportarAlunos,parametros)).data;
        if(!response.error){
            loading.value = false;
            toast.add({ 
                severity: 'success',
                summary: 'Concluído!', 
                detail: response.message, 
                life: 3000 
            });
            download.value.addFile(
                `${response.data.url}`,
                `${response.data.name}`
            );
            download.value.openModal();             
        }
             
    } catch(e){
        loading.value = false;
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: e.response.data.message,
            life: 5000
        });
    }     
};

</script>

<template>
    <ModalLoading :is-loading="loading"/>
    <Toast />
    <section class = "container w-6">
        <Panel header="Exportação dos alunos para o sete">                                        
            
            <section class="flex justify-content-left">              
                <div class="grid">
                    <div class="col-12">
                        <label><b>Competência:</b></label>                    
                    </div>     
                    <div class="col-12">                    
                        <Dropdown v-model="selectedCompetencia" :options="competencia" optionLabel="name" selected/>
                    </div>                
                </div>
            </section>

            <div class= "flex justify-content-center align-items-center">                
                <Button type="button" label="Exportar" icon="pi pi-file-export" @click="verificarInconsistencias()"/>
            </div>                     
        </Panel>
    </section>    
    <section>
        <Dialog v-model:visible="visibleDialog" :visible="visibleDialog" modal
            class="p-dialog p-component" :pt="{content:{style:'background-color:#e0dddd'}}">
            <template #header>
                <div class="inline-flex align-items-center justify-content-center">
                    
                    <span class="font-bold white-space-nowrap" style="font-size: 1.5rem;">
                        <i class="pi pi-exclamation-circle" style="color: yellow;font-size: 2.5rem"></i>
                        Alerta de inconsistências!!
                    </span>
                </div>
            </template>            
            <section class="flex flex-column w-full" style="flex-grow:1; margin-top: 1rem;">
                <section class="flex justify-content-center w-full" style="flex-grow: 1;">
                    <Panel header="Foram encontradas inconsistências nos dados de alunos!">
                        <label>                             
                            Gostaria de gerar o relatório com os alunos inconsistentes ou
                            exportar mesmo assim só os alunos com dados consistentes?
                        </label>
                    </Panel>
                </section>
            </section>
            
        
            <section class="flex justify-content-center" style="margin-top: 2rem;font-size: 2rem;">    
                <Button type="button"  class="mr-3" label="Inconsistências Alunos" icon="pi pi-file" severity="warning" @click="gerarRelatorioInconsistenciasAlunos()"/>
                <Button type="button" class="ml-auto" label="Exportar alunos" icon="pi pi-file-export" @click="exportarAlunos()"/>
            </section>
                        
        </Dialog>        
    </section>
    <MultiDownload :header="'Arquivos para Download'" :position="'center'" ref="download"/>  
</template>

<style scoped>

</style>