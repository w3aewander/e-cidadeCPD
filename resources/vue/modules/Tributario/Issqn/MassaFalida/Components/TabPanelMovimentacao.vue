<script setup>
import { ref, watch, computed, reactive } from 'vue';
import MovimentacaoService from '../Services/MovimentacaoService';
import HistoricoService from '../Services/HistoricoService';
import { useToast } from 'primevue/usetoast';
import DataTableVisualizaHistorico from './DataTableVisualizaHistorico.vue';
import { toCnpj } from '@utils/Strings';

// service
const toast = useToast();

//emits
const emit = defineEmits(['fillMovs']);

// props
const props = defineProps({
    dadosEmpresa: {type: Object},
    fillMovs: {type: Boolean},
});

// data
const state = reactive({
    saving: false,
    loadingMovs: false,
    tipoMovFinalizadas: false,
    dataMovFinalizadas: false,
    isloadingTipos: false,
});
const maxDate = new Date();
const tipoMovs = ref([]);
const filteredMov = ref([]);
const selectedMov = ref(1);
const dateMov = ref();
const movimentacao = ref();
const codMovimentacao = ref('');
const dadosHistorico = ref();
const editing = ref(false);
const showDialogHistorico = ref(false);
const cnpjEmpresa = ref();

// methods
const save = async () => {
    if (!validateForm()) { return false };

    if (!validateOrder()) { return false };

    try {
        state.tipoMovFinalizadas = true;
        if(editing.value) {
            await MovimentacaoService.edit({
                datamov: dateMov.value,
                codMovimentacao: dadosHistorico.value.codMovimentacao,
                selectedMov: selectedMov.value
            });

            HistoricoService.save({
                params: dadosHistorico.value
            });

            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Alteração Realizada', life: 3000 });
        } else {
            state.saving = true;

            if (selectedMov.value == 3 && filteredMov.value.length == 2) {
                codMovimentacao.value = await MovimentacaoService.save({
                    tipo: 2,
                    datamov: dateMov.value,
                    massafalida: props.dadosEmpresa.codigo
                });
            }

            codMovimentacao.value = await MovimentacaoService.save({
                tipo: selectedMov.value,
                datamov: dateMov.value,
                massafalida: props.dadosEmpresa.codigo
            });
        }
    } catch {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao salvar', life: 3000 });
    } finally {
        dateMov.value = null;
        state.saving = false;
        editing.value = false;
        buscarMovimentacao();
    }
}

const buscarMovimentacao = async () => {
    state.loadingMovs = true;
    try {
        movimentacao.value = await MovimentacaoService.get({ 
            codigo: props.dadosEmpresa.codigo
        });

        movimentacao.value = movimentacao.value.map(obj => ({
            tipo: obj.tipo,
            tipoID: obj.tipoId,
            datamov: obj.datamov.split("-").reverse().join("/"),
            codMovimentacao: obj.codMovimentacao,
            id: obj.id,
            datahora: obj.datahora
        }));
        validateDropdown();
    } catch {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao buscar movimentações', life: 3000 });
    } finally {
        state.loadingMovs = false;
    }
}

const deleteMovimentacao = async(data) => {
    state.tipoMovFinalizadas = true;
        
    try {
        await MovimentacaoService.delete({
            codMovimentacao: data.codMovimentacao
        });
        toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Movimentação excluída', life: 3000 });
    } catch {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao excluir movimentação', life: 3000 });
    } finally {
        editing.value = false
        dateMov.value = null;
        selectedMov.value = null;
        buscarMovimentacao();
    }
}

const editMovimentacao =  (data) => {
    editing.value = true;
    state.tipoMovFinalizadas = true;
    state.dataMovFinalizadas = false;

    filteredMov.value = [{ name: data.tipo, code: data.tipoID }];
    selectedMov.value = data.tipoID;

    let [day, month, year] = data.datamov.split('/').map(Number);
    dateMov.value = new Date(year, month - 1, day);

    dadosHistorico.value = { ...data };
}

const isFinished = () => {
    const finalizado = movimentacao.value.length === 4;
    state.tipoMovFinalizadas = state.dataMovFinalizadas = finalizado;

    if (finalizado) {
        filteredMov.value = [];
    }
}

const validateDropdown = async () => {
    state.isloadingTipos = true;
    try {
        filteredMov.value = await MovimentacaoService.getTiposMovs({
            codMassafalida: props.dadosEmpresa.codigo
        });
        tipoMovs.value = filteredMov.value;

        isFinished();

    } catch {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao buscar opções de movimentação', life: 3000 });
    } finally {
        state.loadingMovs = false;
        state.isloadingTipos = false;
    }
};

const validateOrder = () => {
    let dayLimite, monthLimite, yearLimite;
    let totalMovs = movimentacao.value.length;

    if (totalMovs === 0) {
        return true;
    }

    if (editing.value && totalMovs >= 2) {
        [dayLimite, monthLimite, yearLimite] = movimentacao.value[totalMovs - 2]?.datamov.split('/').map(Number);
    } else {
        [dayLimite, monthLimite, yearLimite] = movimentacao.value[totalMovs - 1]?.datamov.split('/').map(Number);
    }

    let dataLimite = new Date(yearLimite, monthLimite - 1, dayLimite);
    let dataSelecionada = new Date(dateMov.value);

    if (editing.value) {
        let [dayAtual, monthAtual, yearAtual] = movimentacao.value[totalMovs - 1]?.datamov.split('/').map(Number);
        let dataAtual = new Date(yearAtual, monthAtual - 1, dayAtual);

        if (dataSelecionada.getTime() === dataAtual.getTime()) {
            toast.add({ severity: 'warn', summary: 'Erro', detail: 'Nenhuma alteração a ser salva', life: 3000 });
            return false;
        }
        
        if (dataSelecionada < dataLimite && totalMovs != 1) {
            toast.add({ severity: 'warn', summary: 'Erro', detail: 'Data da movimentação fora de ordem', life: 3000 });
            return false;
        }
    } else {
        if (dataSelecionada < dataLimite) {
            toast.add({ severity: 'warn', summary: 'Erro', detail: 'Data da movimentação fora de ordem', life: 3000 });
            return false;
        }
    }

    return true;
};

const validateForm = () => {
    if (!selectedMov.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Tipo de movimentação obrigatorio', life: 3000});
        return false;
    }

    if (!dateMov.value) {
        toast.add({ severity: 'warn', summary: 'Erro', detail: 'Data da movimentação obrigatorio', life: 3000});
        return false;
    }

    return true
}

const historicoOpen = (data) => {
    codMovimentacao.value = data.codMovimentacao;
    showDialogHistorico.value = true
}

const clearFields = () => {
    editing.value = false;
    dateMov.value = null;
    selectedMov.value = null;
    codMovimentacao.value = '';
    dadosHistorico.value = null;
    filteredMov.value = tipoMovs.value;

    isFinished();
}

const labelSaveButton = computed(() => {
    if (editing.value) {
        return 'Alterar';
    } 
    if (state.saving) {
        return 'Salvando';
    }
    return 'Salvar';
})

watch(() => props.fillMovs, (value) => {
    if (value === true) {
        buscarMovimentacao();
        cnpjEmpresa.value = toCnpj(props.dadosEmpresa.cgccpf);
    }
    emit('fillMovs', false);
});
</script>

<template>

    <Dialog
        v-model:visible="showDialogHistorico"
        maximizable
        modal
        header="Histórico de Movimentação"
        position="top"
        class="mb-10"
    >
        <DataTableVisualizaHistorico 
            :codMovimentacao="codMovimentacao"
        />
    </Dialog>

    <div class="container mt-3">
        <div class="grid gap-2">
            <Panel class="col" header="Dados">
                <!-- form -->
                <div class="grid gap-2">

                    <div class="col-12 flex flex-column gap-2">
                        <label class="font-bold">CNPJ </label>
                        <InputText v-model="cnpjEmpresa" readonly class="w-full" />
                    </div>

                    <!-- empresa -->
                    <div class="col-12 flex flex-column gap-2">
                        <label class="font-bold">Empresa </label>
                        <InputText v-model="dadosEmpresa.empresa" readonly class="w-full" />
                    </div>

                    <!-- numero do processo -->
                    <div class="col-12 flex flex-column gap-2">
                        <label class="font-bold">Número Processo Judicial </label>
                        <InputText v-model="dadosEmpresa.numeroprocesso" readonly class="w-full" />
                    </div>

                    <!-- tipo -->
                    <div class="col-12 flex flex-column gap-2">
                        <label class="font-bold">Tipo Movimentação</label>
                        <Dropdown v-model="selectedMov" :options="filteredMov" optionLabel="name" optionValue="code"
                            placeholder="Selecione o tipo" :disabled="state.tipoMovFinalizadas" :loading="state.isloadingTipos"/>
                    </div>

                    <!-- data -->
                    <div class="col-12 flex flex-column gap-2">
                        <label class="font-bold">Data Movimentação </label>
                        <Calendar showIcon :maxDate="maxDate" v-model="dateMov" :disabled="state.dataMovFinalizadas"/>
                    </div>

                    <div class="col-12">
                        <Button
                            icon="pi pi-save"
                            :loading="state.saving"
                            @click="save"
                            :label="labelSaveButton"
                            class="mr-2"
                        />
                        <Button
                            icon="pi pi-replay"
                            @click="clearFields"
                            class="mr-2"
                            style="background: #4a789c00!important; color: #4a789c!important"
                        />
                    </div>
                </div>
            </Panel>

            <!-- cgm list -->
            <div class="col">
                <Card>
                    <template #title>Movimentações</template>
                    <template #content>
                        <DataTable 
                            :value="movimentacao"
                            :loading="state.loadingMovs"
                        >
                            <Column field="tipo" header="Tipo"></Column>
                            <Column field="datamov" header="Data"></Column>
                            <Column>
                                <template #body="{ data }">
                                    <Button 
                                        v-if="data === movimentacao[movimentacao.length - 1]"
                                        v-tooltip="'Editar'" 
                                        @click="editMovimentacao(data)"
                                        outlined 
                                        rounded 
                                        icon="pi pi-pencil" 
                                        severity="success"
                                    />
                                </template>
                            </Column>
                            <Column>
                                <template #body="{ data }">
                                    <Button 
                                        v-if="data === movimentacao[movimentacao.length - 1]"
                                        v-tooltip="'Excluir'" 
                                        @click="deleteMovimentacao(data)"
                                        outlined 
                                        rounded 
                                        icon="pi pi-times"
                                        severity="danger"
                                    />
                                </template>
                            </Column>
                            <Column>
                                <template #body="{ data }">
                                    <Button 
                                        v-tooltip="'Detalhes'" 
                                        @click="historicoOpen(data)"
                                        outlined 
                                        rounded 
                                        icon="pi pi-ellipsis-h" />
                                </template>
                            </Column>
                            <template #empty> Nenhuma movimentação realizada </template>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>