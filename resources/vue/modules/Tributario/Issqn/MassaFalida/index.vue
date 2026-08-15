<script setup>
import { ref } from 'vue';
import TabPanelCadastro from './Components/TabPanelCadastro.vue';
import TabPanelMovimentacao from './Components/TabPanelMovimentacao.vue';

//props
const props = defineProps(['instit']);

// data
const enabledMovimentacao = ref(false);
const currentTabIndex = ref(0);
const dadosEmpresa = ref({codigo: '', empresa: '', numeroprocesso: ''});
const fillMovs = ref(false);

// methods
const enableMovimentacao = (value) => {
    enabledMovimentacao.value = value;
}

const changeTab = (index) => {
    currentTabIndex.value = index;
}

const setDadosEmpresa = (dados) => {
    dadosEmpresa.value = dados;
}

const fillMovimentacoes = (value) => {
    fillMovs.value = value;
}
</script>

<template>
    <TabView :activeIndex="currentTabIndex">
        <TabPanel header="Cadastro">
            <TabPanelCadastro
                @enableMovimentacao="enableMovimentacao"
                @changeTab="changeTab"
                @setDadosEmpresa="setDadosEmpresa"
                @fillMovs="fillMovimentacoes"
                :instit="instit"
            />
        </TabPanel>
        <TabPanel header="Movimentação" :disabled="!enabledMovimentacao">
            <TabPanelMovimentacao 
                :dadosEmpresa="dadosEmpresa"
                :fillMovs="fillMovs"
                @fillMovs="fillMovimentacoes"
            />
        </TabPanel>
    </TabView>
</template>
