<script setup>
import {ref, onMounted} from "vue";
import ProcessosList from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/ProcessosList.vue";
import ConsultaAtendimento from "@modules/Patrimonial/Protocolo/Atendimento/ConsultaAtendimento.vue";
const activeIndex = ref(0);
const aprovarAtendimentos = ref(false);
const props = defineProps(['orgao', 'permissoes', 'emiteRecibo', 'visualizaOutraJanela']);
const orgaoText = ref(null);

onMounted(() => {
    if (!props.orgao) {
        orgaoText.value = 'Órgão do departamento não encontrado para o exercício.';
    } else {
        orgaoText.value = props.orgao;
    }
})

const onTabChange = function (event) {
    activeIndex.value = event.index;
    aprovarAtendimentos.value = activeIndex.value === 1;
}
</script>

<template>
    <div class="tela-andamento-processo">
        <TabView @tab-change="onTabChange" style="width:100%">
            <TabPanel header="Processos">
                <ProcessosList
                    v-if="activeIndex === 0"
                    :orgao="orgaoText"
                    :permissoes="permissoes"
                    :visualizaEmOutraJanela="visualizaOutraJanela"
                />
            </TabPanel>
            <TabPanel header="Atendimentos">
                <ConsultaAtendimento
                    v-if="activeIndex === 1"
                    :aprovarAtendimentos="aprovarAtendimentos"
                    :orgao="orgaoText"
                    :visualizaOutraJanela="visualizaOutraJanela"
                    :emiteRecibo="emiteRecibo"
                />
            </TabPanel>
        </TabView>
    </div>
</template>

<style scoped>
.tela-andamento-processo {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
