<script setup>
import { ref, onMounted } from "vue";
import { useToast } from 'primevue/usetoast';
import { useConfirm } from "primevue/useconfirm";
import ModalLoading from "../../../Components/ModalLoading.vue";

const toast = useToast();
const confirm = useConfirm();
const routes = {
    getCampos: `v4/api/educacao/matricula-online/configuracoes/campos-opcionais`,
    salvar: `v4/api/educacao/matricula-online/configuracoes/campos-opcionais/salvar`
}
const campos = ref()
const loading = ref(false)
const form = ref({
    btnSalvar: {
        disabled: false,
        label: 'Salvar'
    }
})

const salvar = async (event) => {
    try{
        loading.value = true
        let parametros = {
            campos:  campos.value
        }
        await window.axios.post(routes.salvar, parametros)
        toast.add({
            severity: 'success',
            summary: 'Sucesso!',
            detail: 'Sucesso ao Salvar!',
            life: 5000
        });
        loading.value = false
        await buscarCampos()
    } catch (e) {
        loading.value = false
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: e.response.data.message,
            life: 5000
        });
    }
}
const buscarCampos = async () => {
    try {
        loading.value = true
        campos.value = (await window.axios.get(routes.getCampos)).data.data
        loading.value = false
    } catch (e) {
        loading.value = false
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: e.response.data.message,
            life: 5000
        });
    }
}

onMounted(async () => {
    await buscarCampos()
})
</script>
<template>
    <section class="container">
        <Panel header="Campos Opcionais" style="width: 500px; margin: 0 auto">
            <DataTable :value="campos" scrollable scrollHeight="300px" showGridlines>
                <Column field="tipo.name" header="Campo">
                </Column>
                <Column headerStyle="width: 9rem" header="Apresenta">
                    <template #body="slotProps">
                        <Checkbox v-model="slotProps.data.apresenta" :binary="true" />
                    </template>
                </Column>
                <Column headerStyle="width: 9rem" header="Obrigatório">
                    <template #body="slotProps">
                        <Checkbox v-model="slotProps.data.obrigatorio" :binary="true" />
                    </template>
                </Column>
            </DataTable>
            <div class="p-fluid grid">
                <div class="field col-12 md:col-2 col-offset-5 mt-2">
                    <Button class="p-button" :label="form.btnSalvar.label"
                            @click="salvar"
                    ></Button>
                </div>
            </div>
        </Panel>
    </section>
    <ModalLoading :isLoading="loading"/>
</template>

<style scoped>

.container {
    width: 1000px
}

i {
    cursor: pointer
}
</style>
