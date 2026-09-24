<script setup>
import { ref, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import DialogConsultaInscricaoMunicipal from '@modules/Tributario/Arrecadacao/components/DialogConsultaInscricaoMunicipal.vue';
import DataTableEnderecosCadastrados from '@modules/Tributario/Issqn/Components/DataTableEnderecosCadastrados.vue'
import InputSearchInscricao from '@modules/Tributario/Issqn/Components/InputSearchInscricao.vue';
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
const isLoading = ref(false);
const enderecoCadastrado = ref(false);
const endereco = ref(null);
const openSearchInscricao = ref(false);

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

const excluiEndereco = async () => {
    isLoading.value = true;
    const urlCadastro = 'v4/api/tributario/issqn/cadastro-endereco/exclui-endereco'

    try {
        await axios.post(urlCadastro, {
            inscricao: inscricao.value
        });

        enderecoCadastrado.value = false;
        toast.add({severity: 'success', summary: 'Sucesso!', detail: 'Endereço excluído.', life: 5000 });
    } catch (e) {
        toast.add({severity: 'error', summary: 'Erro ao excluir endereço.', detail: e.message, life: 5000 });
    } finally {
        isLoading.value = false;
    }
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

const labelExcluiButton = computed(() => {
    if (isLoading.value) {
        return 'Excluindo';
    }
    return 'Excluir';
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

    <div v-if="!enderecoCadastrado" class="flex justify-content-center align-items-center h-screen">
        <Panel id="panelVerificacao" header="Exclusão de Endereço em Inscrição" style="width: 500px;">
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
                    Exclusão de Endereço em Inscrição
                </div>
            </template>

            <div class="grid gap-2">
                <div class="col-12 flex flex-column align-items-start">
                    <label class="flex flex-column font-bold" for="inscricao" style="margin-bottom: 6px;"> Inscrição </label>
                    <InputSearchInscricao 
                        v-model:id="inscricao" 
                        v-model:description="inscricaoLabel"
                        style="pointer-events: none !important; opacity: 0.6 !important;"
                        class="inputsearch"
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
                        disabled="true"
                    />
                </div>

                <div class="flex flex-column gap-2">
                    <label for="cep" class="font-bold" style="margin-top: 7px">CEP</label>
                    <InputText v-model="cep" style="width: 268px;" disabled="true"/>
                </div>

                <div class="col-12 flex flex-column gap-2">
                    <label for="destinatario" class="font-bold">Nome Destinatário</label>
                    <InputText v-model="destinatario" disabled="true"/>
                </div>

                <div class="col-9 flex flex-column gap-2">
                    <div v-if="municipal == 1">
                        <label for="rua" class="flex flex-column font-bold" style="margin-bottom: 7px;">Logradouro</label>
                        <InputSearchLogradouro 
                            v-model:id="rua" 
                            v-model:description="rualabel" 
                            style="pointer-events: none !important; opacity: 0.6 !important;"
                            class="inputsearch"
                        />
                    </div>
                    <div v-else class="flex flex-column">
                        <label for="rua" class="font-bold" style="margin-bottom: 6px;">Logradouro</label>
                        <InputText v-model="rualabel" disabled="true"/>
                    </div>
                </div>

                <div class="flex flex-column gap-2">
                    <label for="numero" class="font-bold" style="margin-top: 6px;">Número</label>
                    <InputText v-model="numero" style="width: 106px;" disabled="true"/>
                </div>

                <div class="col-12 flex flex-column gap-2">
                    <div v-if="municipal == 1">
                        <label for="bairo" class="flex flex-column font-bold" style="margin-bottom: 6px;">Bairro</label>
                        <InputSearchBairro 
                            v-model:id="bairro" 
                            v-model:description="bairrolabel"
                            style="pointer-events: none !important; opacity: 0.6 !important;"
                            class="inputsearch"
                        />
                    </div>
                    <div v-else class="flex flex-column">
                        <label for="bairro" class="font-bold" style="margin-bottom: 6px;">Bairro</label>
                        <InputText v-model="bairrolabel" disabled="true"/>
                    </div>
                </div>

                <div class="col-12 flex flex-column gap-2">
                    <label for="complemento" class="font-bold">Complemento</label>
                    <InputText v-model="complemento" disabled="true"/>
                </div>
            </div>

            <div class="flex justify-content-center align-items-center mt-2">                
                <Button 
                    class="font-bold" 
                    :label="labelExcluiButton"
                    @click="excluiEndereco"
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

:deep(.inputsearch .w-6rem) {
    border: none;
}

:deep(.inputsearch .w-full) {
    border: none;
}

</style>