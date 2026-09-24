<template>
    <TabView @tab-change="onTabChange">
        <TabPanel header="Não assinados">
            <Assinaturas
                v-if="activeIndex === 0"
                :solicitacaoId="solicitacaoId"
                :configuracao="configuracao"
                :cpf_cnpj="cpf_cnpj"
                :filtro="filtro"
            />
        </TabPanel>

        <TabPanel header="Assinados">
            <Assinaturas
                v-if="activeIndex === 1"
                :configuracao="configuracao"
                :cpf_cnpj="cpf_cnpj"
                :filtro="filtro"
            />
        </TabPanel>

        <TabPanel header="Rejeitados">
            <Assinaturas
                v-if="activeIndex === 2"
                :configuracao="configuracao"
                :cpf_cnpj="cpf_cnpj"
                :filtro="filtro"
            />
        </TabPanel>

        <TabPanel header="Todos">
            <Assinaturas
                v-if="activeIndex === 3"
                :configuracao="configuracao"
                :cpf_cnpj="cpf_cnpj"
                :filtro="filtro"
            />
        </TabPanel>

        <TabPanel header="Minhas Solicitações">
            <Solicitacoes
                v-if="activeIndex === 4"
                :cpf_cnpj="cpf_cnpj"
            />
        </TabPanel>
    </TabView>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Assinaturas from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/Assinaturas/Assinaturas.vue";
import Solicitacoes from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/MinhasSolicitacoes/Solicitacoes.vue";

const props = defineProps({
    cpf_cnpj: String,
    solicitacao_id: {
        type: String,
        default: null
    }
});

const activeIndex = ref(0);
const configuracao = ref({});
const solicitacaoId = ref(props.solicitacao_id || null);
const filtro = ref({ status: 'nao-assinados' });

const onTabChange = (e) => {
    activeIndex.value = e.index;

    switch (e.index) {
        case 0:
            filtro.value.status = 'nao-assinados';
            break;
        case 1:
            filtro.value.status = 'assinados';
            break;
        case 2:
            filtro.value.status = 'rejeitados';
            break;
        case 3:
            filtro.value.status = 'todos';
            break;
    }
}

const getConfiguracoes = async () => {
  const resp = await window.axios.post(`v4/api/assinador/obter-configuracao`, {});
  configuracao.value = resp.data.data;
}

onMounted(() => {
    getConfiguracoes();
});
</script>

<style scoped>
:deep(.p-tabview-nav-link.p-tabview-header-action) {
    margin: 0 !important;
}
</style>
