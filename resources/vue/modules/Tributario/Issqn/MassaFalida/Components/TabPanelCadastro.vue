<script setup>
import { computed, ref } from 'vue';
import DataTablePesquisaEmpresa from './DataTablePesquisaEmpresa.vue';
import DataTablePesquisaMassaFalida from './DataTablePesquisaMassaFalida.vue';
import DialogConsultaCgm from '@modules/Patrimonial/Protocolo/Components/DialogConsultaCgm.vue';
import Processo from '@modules/Patrimonial/Protocolo/Components/Processo.vue';
import { toCnpj } from '@utils/Strings';
import EmpresaService from '../Services/EmpresaService';
import { useToast } from 'primevue/usetoast';
import CadastroService from '../Services/CadastroService';
import InputSearchEmpresa from '@modules/Tributario/Issqn/Components/InputSearchEmpresa.vue';
import InputSearchProcesso from '@modules/Patrimonial/Protocolo/Components/InputSearchProcesso.vue';
import InputSearchCgm from '@modules/Patrimonial/Protocolo/Components/InputSearchCgm.vue';

// service
const toast = useToast();

// emits
const emit = defineEmits(['enableMovimentacao', 'changeTab', 'setDadosEmpresa', 'fillMovs']);

//props
const props = defineProps(['instit'])

// data
const selectedTipoprocesso = ref(null);
const tiposProcesso = ref([
    { name: 'Processo Judicial', code: 1 },
    { name: 'Processo Extrajudicial', code: 2 }
]);

const fillMovs = ref(false);
const saving = ref(false);
const editing = ref(false);
const empresa = ref('');
const empresaLabel = ref('');
const numeroprocesso = ref('');
const dataprocesso = ref('');
const protprocesso = ref('');
const protprocessoLabel = ref('');
const protprocessoNumber = ref('');
const administrador = ref('');
const administradorLabel = ref('');
const observacoes = ref('');
const empresas = ref([]);
const showDialogEmpresa = ref(false);
const showDialogPesquisar = ref(false);
const raizCNPJ = ref('');
const isLoadingEmpresas = ref(false);
const codigo = ref('');

// methods
const labelSaveButton = computed(() => {
    if (editing.value) {
        return 'Alterar';
    }
    if (saving.value) {
        return 'Salvando';
    }
    return 'Salvar';
})

const selectEmpresa = (selectedEmpresa) =>{
    showDialogEmpresa.value = false;
    raizCNPJ.value = selectedEmpresa.cgccpf.replace(/\D/g, '').substring(0,8);
    empresa.value = selectedEmpresa.numcgm;
    empresaLabel.value = selectedEmpresa.nome;

    fillEmpresas();
}

const fillEmpresas = async () => {
    try {
        isLoadingEmpresas.value = true;
        const params = { raizCNPJ: raizCNPJ.value };
        empresas.value = await EmpresaService.getEmpresasRelacionadas(params);
        console.log(empresas.value)
    } catch (e) {
        empresas.value = [];
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao buscar empresas', life: 3000 });
        console.error(e);
    } finally {
        isLoadingEmpresas.value = false;
        return;
    }
}

const fillForm = (selectedEmpresa) => {
    const empresasJSON = JSON.parse(selectedEmpresa.empresas);
    empresaLabel.value = empresasJSON[0].empresa;
    empresa.value = empresasJSON[0].numcgm;
    numeroprocesso.value = selectedEmpresa.numeroprocesso;
    selectedTipoprocesso.value = selectedEmpresa.selectedTipoprocesso;
    dataprocesso.value = selectedEmpresa.dataprocesso;
    protprocesso.value = selectedEmpresa.protprocesso;
    protprocessoLabel.value = selectedEmpresa.processolabel;
    protprocessoNumber.value = selectedEmpresa.processo_ano;
    administradorLabel.value = selectedEmpresa.administradorlabel;
    administrador.value = selectedEmpresa.administradorcgm;
    raizCNPJ.value = selectedEmpresa.raizCNPJ;
    empresas.value = empresasJSON;
    observacoes.value = selectedEmpresa.observacoes;
    codigo.value = selectedEmpresa.codigo;

    editing.value = true;
    showDialogPesquisar.value = false;
    emit('enableMovimentacao', true);
    emit('fillMovs', true);
    emit('setDadosEmpresa', {cgccpf: empresas.value[0].cgccpf, codigo: selectedEmpresa.codigo, empresa: empresaLabel.value, numeroprocesso: numeroprocesso.value });
}

const selectAdministrador = (selectedAdministrador) => {
    administrador.value = selectedAdministrador.numcgm;
    administradorLabel.value = selectedAdministrador.nome;
}

const selectProcesso = (selectedProcesso) => {
    protprocesso.value = selectedProcesso.p58_codproc;
    protprocessoLabel.value = selectedProcesso.p58_requer;
    protprocessoNumber.value = selectedProcesso.processo_ano;
}

const setProtProcesso = (data) => {
    protprocesso.value = data.p58_codproc;
}

const save = async () => {
    try {
        saving.value = true;
        if (!validateForm()) { return false }

        if (editing.value) {
            await CadastroService.edit(formFields());
            emit('setDadosEmpresa', {cgccpf: empresas.value[0].cgccpf, codigo: codigo.value, empresa: empresaLabel.value, numeroprocesso: numeroprocesso.value });
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Alteração Realizada', life: 3000 });

        } else {
            codigo.value = await CadastroService.save(formFields());
            emit('enableMovimentacao', true);
            emit('changeTab', 1);
            emit('fillMovs', true);
            emit('setDadosEmpresa', {cgccpf: empresas.value[0].cgccpf, codigo: codigo.value, empresa: empresaLabel.value, numeroprocesso: numeroprocesso.value });
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Cadastro Realizado', life: 3000 });
            editing.value = true;
        }
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao salvar', life: 3000 });
        console.error(e);
    } finally {
        saving.value = false;
    }
}

const clearFields = () => {
    editing.value = false;
    empresas.value = [];
    empresa.value = '';
    empresaLabel.value = '';
    selectedTipoprocesso.value = null;
    numeroprocesso.value = '';
    dataprocesso.value = '';
    protprocesso.value = '';
    protprocessoLabel.value = '';
    protprocessoNumber.value = '';
    administrador.value = '';
    administradorLabel.value = '';
    observacoes.value = '';
    raizCNPJ.value = '';
    codigo.value = '';

    emit('enableMovimentacao', false);
}

const deleteEmpresaRelacionada = async(event) => {
    isLoadingEmpresas.value = true;
    const url = 'v4/api/tributario/issqn/massafalida/delete-empresa-relacionada';
    try {
        const response = await axios.delete(url, {
            data: {numcgm: event.numcgm}
        });

        if (response.status === 200) {
            toast.add({ severity:'success', summary: 'Sucesso', detail: 'Empresa relacionada removida com sucesso', life: 3000 });
        }

        empresas.value = empresas.value.filter(empresa => empresa.numcgm !== event.numcgm);
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao remover empresa relacionada', life: 3000 });
    } finally {
        isLoadingEmpresas.value = false;
    }
}

const formFields = () => {
    return {
        codigo: codigo.value,
        raizcnpj: raizCNPJ.value,
        tipoprocesso: selectedTipoprocesso.value,
        numeroprocesso: numeroprocesso.value,
        dataprocesso: dataprocesso.value,
        protprocesso: protprocesso.value,
        administrador: administrador.value,
        observacoes: observacoes.value,
        cgms: empresas.value.map((empresa) => empresa.numcgm)
    }
}

const validateForm = () => {
    if (!raizCNPJ.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Empresa obrigatorio'});
        return false;
    }

    if (!selectedTipoprocesso.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Tipo Processo obrigatorio'});
        return false;
    }

    if (!numeroprocesso.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Numero Processo obrigatorio'});
        return false;
    }

    if (!dataprocesso.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Data Processo obrigatorio'});
        return false;
    }

    if (!administrador.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Administrador obrigatorio'});
        return false;
    }

    if (!protprocesso.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Processo de protocolo obrigatorio'});
        return false;
    }

    return true;
}
</script>

<template>
    <Dialog
        v-model:visible="showDialogEmpresa"
        maximizable
        modal
        header="Selecione uma empresa"
        position="top"
    >
        <DataTablePesquisaEmpresa
            @select="selectEmpresa"
            @close="showDialogEmpresa = false"/>
    </Dialog>

    <Dialog
        v-model:visible="showDialogPesquisar"
        maximizable
        modal
        header="Pesquisar dados de empresa"
        position="top"
    >
        <DataTablePesquisaMassaFalida
            @select="fillForm"
            @close="showDialogPesquisar = false"
        />
    </Dialog>

    <div class="container mt-3">
        <div class="grid gap-2">
            <!-- form -->
            <Panel class="col" header="Dados">
                <div class="grid gap-2">

                    <div v-if="!editing" class="col-12 flex flex-column gap-2 align-items-start">
                        <Button label="Empresa*" @click="showDialogEmpresa = true" class="buttonLinks" link/>
                        <InputSearchEmpresa
                            v-model:id="empresa"
                            v-model:description="empresaLabel"
                            @selected="selectEmpresa"
                        />
                    </div>

                    <div v-else class="col-12 flex flex-column gap-2 align-items-start">
                        <label for="empresa" class="font-bold">Empresa</label>
                        <InputText v-model="empresaLabel" readonly class="w-full" />
                    </div>

                    <div class="col-12 flex flex-column gap-2">
                        <label for="tipoprocesso" class="font-bold">Tipo Processo*</label>
                        <Dropdown v-model="selectedTipoprocesso" :options="tiposProcesso" optionLabel="name" optionValue="code"
                            placeholder="Selecione o tipo" />
                    </div>

                    <div class="col-12 flex flex-column gap-2">
                        <label for="numeroprocesso" class="font-bold">Número Processo*</label>
                        <InputText v-model="numeroprocesso" class="w-full" />
                    </div>

                    <div class="col-12 flex flex-column gap-2">
                        <label for="dataprocesso" class="font-bold">Data Processo*</label>
                        <Calendar v-model="dataprocesso" showIcon />
                    </div>

                    <div class="col-12 flex flex-column gap-2 align-items-start">
                        <Button label="Processo de protocolo*" @click="$refs.dialogProcesso.toggleDialog()" class="buttonLinks" link/>
                        <InputSearchProcesso
                            v-model:id-value="protprocessoNumber"
                            v-model:description-value="protprocessoLabel"
                            @selected="setProtProcesso"
                            v-tooltip.left="'Número do Processo/Ano'"
                            :processo="protprocessoNumber"
                            :instit="instit"

                        />
                        <Processo ref="dialogProcesso" @selectRow="selectProcesso"/>
                    </div>
                    <div class="col-12 flex flex-column gap-2 align-items-start">
                        <Button label="Administrador*" @click="$refs.dialogConsultaAdmin.toggleDialog()" class="buttonLinks" link/>
                        <InputSearchCgm
                            v-model:id="administrador"
                            v-model:description="administradorLabel"
                            v-tooltip.left="'CGM Administrador da falencia ou recuperação'"
                        />
                        <DialogConsultaCgm ref="dialogConsultaAdmin" @selectRow="selectAdministrador" />
                    </div>

                    <div class="col-12 flex flex-column gap-2">
                        <label for="observacoes" class="font-bold">Observações</label>
                        <Textarea v-model="observacoes" rows="2" cols="30" />
                    </div>
                </div>
            </Panel>

            <!-- cgm list -->
            <div class="col">
                <Card>
                    <template #title>Empresas</template>
                    <template #content>
                        <DataTable :value="empresas" :loading="isLoadingEmpresas">
                            <Column field="numcgm" header="CGM"></Column>
                            <Column field="cgccpf" header="CNPJ">
                                <template #body="data">
                                    {{ toCnpj(data.data.cgccpf) }}
                                </template>
                            </Column>
                            <Column field="empresa" header="Nome"></Column>
                            <Column header="Ações" v-if="editing && empresas.length > 1">
                                <template #body="slotProps">
                                    <Button
                                        v-if="slotProps.data.cgccpf != empresas[0].cgccpf"
                                        outlined
                                        rounded
                                        @click="deleteEmpresaRelacionada(slotProps.data)"
                                        severity="danger"
                                        icon="pi pi-times"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </div>

            <div class="col-12">
                <Button icon="pi pi-save"
                    :label="labelSaveButton"
                    @click="save"
                    class="mr-2"
                    :loading="saving"
                />
                <Button icon="pi pi-replay"
                    label="Limpar"
                    @click="clearFields"
                    class="mr-2"
                />
                <Button @click="showDialogPesquisar = true" icon="pi pi-search" label="Pesquisar"/>
            </div>
        </div>
    </div>
</template>

<style scoped>

.buttonLinks {
    padding: 0px!important;
    color: #4a789c!important;
    text-decoration: underline;
}

</style>
