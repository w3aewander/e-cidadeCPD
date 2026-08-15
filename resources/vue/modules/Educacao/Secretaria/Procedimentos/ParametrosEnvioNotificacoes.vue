<template>
    <div v-show="showBackground" class="background">
    </div>
    <div class="card card-style">
        <DataTable
            tableStyle="min-width: 50rem"
            :value="escolas.data"
            :filters="filtersTable"
            :global-filter-fields="['name', 'code']"
            paginator :rows="10"
            dataKey="code"
        >
            <template #header>
                <div class="flex justify-content-between align-items-center p-2 mr-2 relative">
                    <div class="image-wrapper">
                        <Image :src="imgSrc" alt="Imagem de uma engrenagem azul" width="75"></Image>
                    </div>
                    <span class="font-span-header relative">Configurações de Envio</span>
                    <span class="p-input-icon-left flex justify-content-between">
                        <i class="pi pi-search" />
                        <InputText id="pesquisar" v-model="filtersTable['global'].value" placeholder="Pesquisar escolas" />
                    </span>
                </div>
            </template>
            <Column field="code" header="Cod. da Escola" style="min-width: 2rem" header-style="font-size: 1.1rem"></Column>
            <Column field="name" header="Nome da Escola" style="min-width: 14rem" header-style="font-size: 1.1rem"></Column>
            <Column field="allowSMS" header="Permite SMS" header-style="font-size: 1.1rem">
                <template #body="slotProps">
                    <Checkbox v-model="slotProps.data.allowSMS" :binary="true" @change="markAsModified(slotProps.data)"></Checkbox>
                </template>
            </Column>
            <Column field="allowEmail" header="Permite Email" header-style="font-size: 1.1rem">
                <template #body="slotProps">
                    <Checkbox v-model="slotProps.data.allowEmail" :binary="true" @change="markAsModified(slotProps.data)"></Checkbox>
                </template>
            </Column>
            <Column field="allowWhats" header="Permite WhatsApp" header-style="font-size: 1.1rem">
                <template #body="slotProps">
                    <Checkbox v-model="slotProps.data.allowWhats" :binary="true" @change="markAsModified(slotProps.data)"></Checkbox>
                </template>
            </Column>
            <Column field="numberWhats" header="Número WhatsApp" header-style="font-size: 1.1rem">
                <template #body="slotProps">
                    <InputText
                        id="numberWhats"
                        v-model="slotProps.data.numberWhats"
                        v-mask="['(##)#####-####', '(##)####-####']"
                        placeholder="(99)99999-9999"
                        :disabled="!slotProps.data.allowWhats"
                        :required="slotProps.data.allowWhats"
                        @input="markAsModified(slotProps.data)"
                    />
                </template>
            </Column>
        </DataTable>
        <div class="button-wrapper">
            <Button icon="pi pi-save" label="Salvar" severity="contrast" @click="salvarAlteracoes" :disabled="!isModified"/>
        </div>
    </div>
    <ModalLoading :is-loading="loading" :message="mensagemModal"/>
</template>
<script setup>
import {onMounted, ref} from 'vue';
import ModalLoading from "@modules/Components/ModalLoading.vue";
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import {FilterMatchMode} from "primevue/api";
import {useToast} from "primevue/usetoast";
import Button from 'primevue/button';

const escolas = ref({
    data: []
});
const filtersTable = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS}
});
const isModified = ref(false);
const ecidadepath = window.ECIDADE_PATH;
const imgSrc = ecidadepath + 'imagens/educacao/secretaria/settings-gear-svgrepo-com.svg';
const loading = ref(false);
const mensagemModal = ref(null);
const mensagens = {
    escolas: 'Buscando escolas...',
    salvar: 'Salvando alterações...'
};
const parametrosModificados = ref({ dados: [{}] });
const originalEscolas = ref([]);
const routes = {
    escolas: `v4/api/educacao/secretaria/parametros/escolas`,
    salvarParametros: `v4/api/educacao/secretaria/parametros/save`
};
const showBackground = ref(true);
const toast = useToast();

async function buscarEscolas() {
    try {
        mensagemModal.value = mensagens.escolas;
        loading.value = true;
        showBackground.value = true;
        const response = await window.axios.get(routes.escolas);
        if (response.data.data.length > 0) {
            escolas.value.data = response.data.data.map(escola => ({
                code: escola.ed18_i_codigo,
                name: escola.ed18_c_nome,
                allowWhats: escola.ed204_permite_whatsapp,
                allowSMS: escola.ed204_permite_sms,
                allowEmail: escola.ed204_permite_email,
                numberWhats: escola.ed204_telefone_whatsapp,
                isModified: false
            }));
            originalEscolas.value = JSON.parse(JSON.stringify(escolas.value.data));
        } else {
            toast.add({
                severity: 'warn',
                summary: 'Atenção!',
                detail: 'Não há escolas cadastradas no sistema!',
                life: 15000
            });
        }
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: `${e.response.data.message}`,
            life: 15000
        });
    } finally {
        loading.value = false;
        showBackground.value = false;
    }
}

async function salvarAlteracoes() {
    parametrosModificados.value.dados = escolas.value.data.filter(escola => escola.isModified);

    if (parametrosModificados.value.dados.length > 0 && validaCampoWhatsApp()) {
        try {
            mensagemModal.value = mensagens.salvar;
            loading.value = true;
            await window.axios.post(routes.salvarParametros, parametrosModificados.value);
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Parâmetros salvos com sucesso!',
                life: 15000
            });
            clearIsModified();
        } catch (e) {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: `${e.response.data.message}`,
                life: 15000
            });
        } finally {
            loading.value = false;
        }
    } else {
        toast.add({
            severity: 'info',
            summary: 'Nenhuma alteração!',
            detail: 'Não há alterações a serem salvas.',
            life: 15000
        });
    }
}

function markAsModified(data) {
    isModified.value = true;
    data.isModified = true;
}

function clearIsModified() {
    isModified.value = false;
}

function validaCampoWhatsApp() {
    const fieldInvalidWhats = escolas.value.data.filter(escola => escola.allowWhats && !escola.numberWhats);

    if (fieldInvalidWhats.length > 0) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Se o campo Permite WhatsApp estiver marcado o Número WhatsApp deve ser informado!',
            life: 15000
        });
        fieldInvalidWhats.forEach(escola => {
            toast.add({
                severity: 'info',
                summary: 'Informar número na escola:',
                detail: `${escola.name}`,
                life: 25000
            });
        });
        return false;
    }
    return true;
}

onMounted(() => {
   showBackground.value = true;
   buscarEscolas();
});
</script>
<style scoped>

.card-style {
    max-width: 75vw;
    margin: 2rem auto;
    border-radius: 1rem;
}

.font-span-header {
    font-size: 2rem;
    font-weight: lighter;
}

#numberWhats,
#pesquisar {
    border-radius: 1rem;
}

#pesquisar {
    width: 25rem;
    margin-right: 3.8rem;
}

.background {
    background-color: #E0E0E0;
    width: 100vw;
    height: 100vh;
}

.button-wrapper {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin: 2rem auto;
}

.p-datatable-thead > tr > th {
    text-align: center !important;
    vertical-align: middle !important;
    white-space: nowrap;
}

.image-wrapper {
    position: absolute;
    left: 5px; /* Ajusta a posição da imagem para que ela fique parcialmente visível */
    top: 55%;
    transform: translateY(-50%);
    z-index: 0; /* Coloca a imagem atrás do texto */
}

.font-span-header {
    color: #043147;
    font-size: 2.4rem;
    font-weight: lighter;
    margin: 0.5rem 0 0 5rem;
    position: relative;
    z-index: 1; /* Garante que o texto fique sobre a imagem */
}

.relative {
    position: relative;
}

</style>
