<script setup>
import { ref, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import InputSearchInscricao from '@modules/Tributario/Issqn/Components/InputSearchInscricao.vue';
import DataTableEnderecosCadastrados from '@modules/Tributario/Issqn/Components/DataTableEnderecosCadastrados.vue'
import DataTableConsultaLogradouro from '@modules/Tributario/Arrecadacao/components/DataTableConsultaLogradouro';
import DataTableConsultaBairro from '@modules/Tributario/Issqn/Components/DataTableConsultaBairro';
import InputSearchLogradouro from '@modules/Tributario/Issqn/Components/InputSearchLogradouro.vue';
import InputSearchBairro from '@modules/Tributario/Issqn/Components/InputSearchBairro.vue';

//services
const toast = useToast();

// data
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
const enderecoCadastrado = ref(false);
const endereco = ref(null);
const openSearchInscricao = ref(false);

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

const buscaEndereco = async () => {
    isLoading.value = true;

    if (!inscricao.value) {
        toast.add({severity: 'warn', summary: 'Selecione uma inscrição.', detail: e.message, life: 5000 })
        return;
    }

    const urlCadastro = 'v4/api/tributario/issqn/cadastro-endereco/busca-endereco'
    try {
        const response = await axios.get(urlCadastro, {
            params: {
                sequencial: inscricao.value
            }
        });

        const { data: resp } = response;
        endereco.value = resp.data ? resp.data : {data: null};

        if (endereco.value.length == 1) {
            preencheCampos(endereco.value[0]);
            return;
        }

        toast.add({ severity: 'warn', summary: 'Inscrição sem endereço cadastrado.', life: 5000 });
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Erro ao buscar cadastro.', detail: e.message, life: 5000 });
    } finally {
        isLoading.value = false;
    }
}

const alteraEndereco = async () => {
    if (!validateForm()) { return };

    isLoading.value = true;
    const urlCadastro = 'v4/api/tributario/issqn/cadastro-endereco/altera-endereco'

    try {
        await axios.post(urlCadastro, {
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
        buscaEndereco();
        toast.add({severity: 'success', summary: 'Sucesso!', detail: 'Dados alterados.', life: 5000 });
    } catch (e) {
        toast.add({severity: 'error', summary: 'Erro ao alterar endereço.', detail: e.message, life: 5000 });
    } finally {
        isLoading.value = false;
    }
}

const validateForm = () => {
    if (!cep.value) {
        toast.add({ severity: 'warn', summary: 'Informe o CEP.', life: 5000 });
        return false;
    }

    if (cep.value.length < 8) {
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

    if (municipal.value == 1 && ((!rua.value) || (!bairro.value))) {
        toast.add({ severity: 'warn', summary: 'Endereço municipal deve possuir código de logradouro e bairro.', life: 5000 });
        return false;
    }

    if (cep.value == endereco.value.cep 
        && destinatario.value == endereco.value.destinatario 
        && rualabel.value == endereco.value.rualabel
        && bairrolabel.value == endereco.value.bairrolabel
        && complemento.value == endereco.value.complemento
    ) {
        toast.add({ severity: 'warn', summary: 'Não há alterações a serem realizadas.', life: 5000 });
        return false;
    }

    return true;
}

const clearDropdown = () => {
    if (municipal.value == 1) {
        rua.value = endereco.value.rua;
        bairro.value = endereco.value.bairro;
        bairrolabel.value = endereco.value.bairrolabel;
        rualabel.value = endereco.value.rualabel;
        return;
    }

    bairrolabel.value = endereco.value.bairrolabel;
    rualabel.value = endereco.value.rualabel;
    rua.value = '';
    bairro.value = '';
}

const preencheCampos = (data) => {
    endereco.value = data;

    inscricao.value = endereco.value.sequencial;
    inscricaoLabel.value = endereco.value.nome;
    municipal.value = 0;
    cep.value = endereco.value.cep;
    rualabel.value = endereco.value.rualabel;
    bairrolabel.value = endereco.value.bairrolabel;
    numero.value = endereco.value.numero;
    destinatario.value = endereco.value.destinatario;
    complemento.value = endereco.value.complemento;

    if (endereco.value.municipal) {
        bairro.value = endereco.value.bairro
        rua.value = endereco.value.rua
        municipal.value = 1;
    }

    enderecoCadastrado.value = true;
}

const labelPesquisaButton = computed(() => {
    if (isLoading.value) {
        return 'Pesquisando';
    }
    return 'Pesquisar';
})

const labelAlteraButton = computed(() => {
    if (isLoading.value) {
        return 'Alterando';
    }
    return 'Alterar';
})

</script>

<template>

    <Dialog
       v-model:visible="openSearchInscricao"
       position="top"
       :modal="true"
       :draggable="false"
       header="Consulta de Endereços Cadastrados"
       style="width: 800px;"
       class="shadow-4"
   >
       <section style="width: 100%" class="m-auto">
           <DataTableEnderecosCadastrados
                @rowSelected="preencheCampos"
                @close="openSearchInscricao = false"
           />
       </section>
   </Dialog>

    <Dialog
        v-model:visible="openSearchLogradouro"
        position="top"
        :modal="true"
        :draggable="false"
        header="Consulta de Logradouro"
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
    
    <div v-if="!enderecoCadastrado" class="flex justify-content-center align-items-center h-screen">
        <Panel id="panelVerificacao" header="Alteração de Endereço em Inscrição" style="width: 500px;">
            <div class="grid gap-2">
                <div class="col-12 flex flex-column align-items-start">
                    <Button class="linkButton" label="Inscrição" @click="openSearchInscricao = true" link/>
                    <InputSearchInscricao 
                        :params="{inscricaoAtiva: true}" 
                        v-model:id="inscricao" 
                        v-model:description="inscricaoLabel" 
                        @selected="selectInscricao"
                    />
                </div>
            </div>
            
            <div class="flex justify-content-center align-items-center mt-2">
                <Button 
                    class="font-bold" 
                    :label="labelPesquisaButton"
                    :loading="isLoading"
                    @click="buscaEndereco"
                />
            </div>
        </Panel>
   </div>

    <div v-if="enderecoCadastrado" class="flex justify-content-center align-items-center h-screen">
        <Panel id="panel">
            <template #header>
                <div class="custom-header font-bold">
                    <i class="pi pi-arrow-left custom-icon mr-1"
                        @click="enderecoCadastrado = false"
                    />
                    Alteração de Endereço em Inscrição
                </div>
            </template>

            <div class="grid gap-2">
                <div class="col-12 flex flex-column align-items-start">
                    <label class="flex flex-column font-bold" for="inscricao" style="margin-bottom: 6px;"> Inscrição </label>
                    <InputSearchInscricao 
                        :params="{inscricaoAtiva: true}" 
                        v-model:id="inscricao" 
                        v-model:description="inscricaoLabel" 
                        style="pointer-events: none !important;"
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

            <div class="flex justify-content-center align-items-center mt-2">
                <Button 
                    class="font-bold" 
                    :label="labelAlteraButton"
                    @click="alteraEndereco"
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