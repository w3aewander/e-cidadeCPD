<script setup>
import { ref, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import InputSearchInscricao from '@modules/Tributario/Issqn/Components/InputSearchInscricao.vue';
import DialogConsultaInscricaoMunicipal from '@modules/Tributario/Arrecadacao/components/DialogConsultaInscricaoMunicipal.vue';
import DataTableConsultaLogradouro from '@modules/Tributario/Arrecadacao/components/DataTableConsultaLogradouro';
import DataTableConsultaBairro from '@modules/Tributario/Issqn/Components/DataTableConsultaBairro';
import InputSearchLogradouro from '@modules/Tributario/Issqn/Components/InputSearchLogradouro.vue';
import InputSearchBairro from '@modules/Tributario/Issqn/Components/InputSearchBairro.vue';

//services
const toast = useToast();

// data
const inscricaoAtiva = true;
const inscricao = ref('');
const inscricaoLabel = ref('');
const cep = ref('');
const bairro = ref(null);
const bairrolabel = ref('');
const rua = ref(null);
const rualabel = ref('');
const numero = ref('');
const municipal = ref(null);
const destinatario = ref('');
const complemento = ref('');
const opcoesMunicipal = ref([
    { name: "Não", code: 0 },
    { name: "Sim", code: 1 }
]);
const openSearchLogradouro = ref(false);
const openSearchBairro = ref(false);
const isLoading = ref(false);

const selectInscricao = (data) => {
    inscricao.value = data.q02_inscmu || data.inscricao;
    inscricaoLabel.value = data.z01_nome || data.nome;
}

const selectLogradouro = (data) => {
    rua.value = data.sequencial;
    rualabel.value = (data.sigla) + ' ' + (data.nome);

    if (data.cep) {
        cep.value = data.cep;
    }
}

const selectBairro = (data) => {
    bairro.value = data.sequencial;
    bairrolabel.value = data.nome;
}

const cadastraEndereco = async () => {
    if (!validateForm()) { return };

    isLoading.value = true;
    const urlCadastro = 'v4/api/tributario/issqn/cadastro-endereco/salva-endereco'

    try {
        const response = await axios.post(urlCadastro, {
            inscricao: inscricao.value,
            municipal: municipal.value,
            cep: cep.value,
            rua: rua.value,
            rualabel: rualabel.value,
            numero: numero.value,
            destinatario: destinatario.value,
            bairro: bairro.value,
            bairrolabel: bairrolabel.value,
            complemento: complemento.value
        });

        if (response.data.data == 1) {
            toast.add({severity: 'success', summary: 'Sucesso!', detail: 'Endereço cadastrado.', life: 5000 });
            return;
        };

        toast.add({severity: 'error', summary: 'Erro!', detail: 'Inscrição já possui endereço cadastrado.', life: 5000 });
    } catch (e) {
        toast.add({severity: 'error', summary: 'Erro ao cadastrar endereço.', detail: e.message, life: 5000 })
    } finally {
        isLoading.value = false;
    }
}

const validateForm = () => {
    if (!inscricao.value) {
        toast.add({ severity: 'warn', summary: 'Informe a inscrição.', life: 5000 });
        return false;
    }

    if (!cep.value) {
        toast.add({ severity: 'warn', summary: 'Informe o CEP.', life: 5000 });
        return false;
    }

    if (cep.value < 8) {
        toast.add({ severity: 'warn', summary: 'Informe um CEP válido.', life: 5000 });
        return false;
    }

    if ((municipal.value != 0 && municipal.value != 1) || (municipal.value == null)) {
        toast.add({ severity: 'warn', summary: 'Informe se o endereço é municipal.', life: 5000 });
        return false;
    }

    if (!rualabel.value) {
        toast.add({ severity: 'warn', summary: 'Informe a rua.', life: 5000 });
        return false;
    }

    if (!bairrolabel.value) {
        toast.add({ severity: 'warn', summary: 'Informe o bairro.', life: 5000 });
        return false;
    }

    if (!numero.value) {
        toast.add({ severity: 'warn', summary: 'Informe o número.', life: 5000 });
        return false;
    }

    return true;
}

const clearDropdown = () => {
    rua.value = null;
    bairro.value = null;
    bairrolabel.value = null;
    rualabel.value = null;
    return;
}

const clearFields = () => {
    inscricao.value = '';
    inscricaoLabel.value = '';
    cep.value = '';
    bairro.value = null;
    bairrolabel.value = '';
    rua.value = null;
    rualabel.value = '';
    numero.value = '';
    municipal.value = null;
    destinatario.value = '';
    complemento.value = '';
}

const labelSaveButton = computed(() => {
    if (isLoading.value) {
        return 'Salvando';
    }
    return 'Salvar';
})

</script>

<template>

   <Dialog
       v-model:visible="openSearchLogradouro"
       position="top"
       :modal="true"
       :draggable="false"
       header="Consulta de rua"
       style="width: 800px;"
       class="shadow-4"
   >
       <section style="width: 100%" class="m-auto">
           <DataTableConsultaLogradouro
                @rowSelected="selectLogradouro"
                @close="openSearchLogradouro = false"
           />
       </section>

   </Dialog>

      <Dialog
       v-model:visible="openSearchBairro"
       position="top"
       :modal="true"
       :draggable="false"
       header="Consulta de Bairro"
       style="width: 800px;"
       class="shadow-4"
   >
       <section style="width: 100%" class="m-auto">
           <DataTableConsultaBairro
                @rowSelected="selectBairro"
                @close="openSearchBairro = false"
           />
       </section>
   </Dialog>

    <div class="flex justify-content-center align-items-center h-screen">
        <Panel id="panel" header="Cadastro de Endereço em Inscrição">
            <div class="grid gap-2">
                <div class="col-12 flex flex-column align-items-start">
                    <Button class="linkButton" label="Inscrição" @click="$refs.dialogConsultaInscricao.toggleDialog()" link/>
                    <InputSearchInscricao 
                        :params="{inscricaoAtiva: true}" 
                        v-model:id="inscricao" 
                        v-model:description="inscricaoLabel" 
                        @selected="selectInscricao"
                    />
                    <DialogConsultaInscricaoMunicipal 
                        ref="dialogConsultaInscricao" 
                        :inscricaoAtiva="inscricaoAtiva" 
                        @selectRow="selectInscricao"
                    />
                </div>

                <div class="col-5 flex flex-column gap-2">
                    <label for="municipal" class="font-bold">Endereço do Município?</label>
                    <Dropdown 
                        v-model="municipal" 
                        :options="opcoesMunicipal" 
                        optionLabel="name" 
                        optionValue="code"
                        placeholder="Selecione" 
                        @change="clearDropdown"
                    />
                </div>

                <div class="flex flex-column gap-2">
                    <label for="cep" class="font-bold" style="margin-top: 7px">CEP</label>
                    <InputText v-model="cep" style="width: 268px;" maxlength="8"/>
                </div>

                <div class="col-12 flex flex-column gap-2">
                    <label for="destinatario" class="font-bold">Nome Destinatário</label>
                    <InputText v-model="destinatario" maxlength="50"/>
                </div>

                <div class="col-9 flex flex-column gap-2">
                    <div v-if="municipal == 1">
                        <Button @click="openSearchLogradouro=true" label="Logradouro" class="font-bold linkButton" link/>
                        <InputSearchLogradouro 
                            v-model:id="rua" 
                            v-model:description="rualabel" 
                            @selected="selectLogradouro"
                        />
                    </div>
                    <div v-else class="flex flex-column">
                        <label for="rua" class="font-bold" style="margin-bottom: 6px;">Logradouro</label>
                        <InputText v-model="rualabel" maxlength="100"/>
                    </div>
                </div>

                <div class="flex flex-column gap-2">
                    <label for="numero" class="font-bold" style="margin-top: 6px;">Número</label>
                    <InputText v-model="numero" style="width: 106px;" maxlength="10"/>
                </div>

                <div class="col-12 flex flex-column gap-2">
                    <div v-if="municipal == 1">
                        <Button @click="openSearchBairro=true" label="Bairro" class="font-bold linkButton" link/>
                        <InputSearchBairro 
                            v-model:id="bairro" 
                            v-model:description="bairrolabel" 
                        />
                    </div>
                    <div v-else class="flex flex-column">
                        <label for="bairro" class="font-bold" style="margin-bottom: 6px;">Bairro</label>
                        <InputText v-model="bairrolabel" maxlength="30"/>
                    </div>
                </div>

                <div class="col-12 flex flex-column gap-2">
                    <label for="complemento" class="font-bold">Complemento</label>
                    <InputText v-model="complemento" maxlength="100"/>
                </div>
            </div>

            <div class="flex justify-content-center align-items-center font-bold mt-2">
                <Button 
                    icon="pi pi-replay"
                    aria-label="Limpar"
                    style="margin-right: 5px;"
                    @click="clearFields"
                />
                <Button 
                    :label="labelSaveButton"
                    @click="cadastraEndereco"
                    :loading="isLoading"
                />
            </div>

        </Panel>
    </div>
</template>

<style scoped>

:deep(.linkButton) {
    color: #4a789c;
    padding: 0;
    margin-bottom: 6px;
}

#panel {
    width: 500px;
}

</style>