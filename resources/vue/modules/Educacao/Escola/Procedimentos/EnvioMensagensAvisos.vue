<template>
    <!-- Verifica se o departamento é uma escola -->
    <div v-if="!isEscola && !isSecretaria">
        <VerificaEscola
            @is-escola="atualizaIsEscola"
            :departamento="departamento"
        ></VerificaEscola>
    </div>
    <!-- Caso seja uma escola, exibe a página -->
    <div class="container-wrapper">
        <div v-if="isSecretaria" class="card flex justify-content-center">
            <Fieldset style="width: 50vw; margin: 0 auto 1rem;" legend="Selecione a Escola">
                <div class="select-escola">
                    <span class="p-float-label">
                        <Dropdown
                            filter
                            id="slctEscola"
                            class="w-full md:w-30rem"
                            v-model="form.slctEscola.data"
                            :options="escolas"
                            option-label="name"
                            @change="buscarCalendarios">
                        </Dropdown>
                        <label for="slctEscola">{{ form.slctEscola.label }}</label>
                    </span>
                </div>
            </Fieldset>
        </div>
        <div v-if="isTurmasCarregadas" class="card flex justify-content-center">
            <Fieldset style="width: 75vw; margin: 0 auto 1rem;" legend="Selecione a Turma">
                <div class="select-turma">
                    <span class="p-float-label">
                        <Dropdown
                            filter
                            id="slctTurma"
                            class="w-full md:w-30rem"
                            v-model="form.slctTurma.data"
                            :options="turmas"
                            option-label="name"
                            @change="buscarAlunos">
                        </Dropdown>
                        <label for="slctTurma">{{ form.slctTurma.label }}</label>
                    </span>
                </div>
            </Fieldset>
        </div>
        <div v-if="alunos.length > 0" class="card">
            <DataTable
                v-model:selection="form.alunosSelecionados"
                :value="alunos"
                paginator
                :rows="10"
                dataKey="code"
                filterDisplay="menu"
                :globalFilterFields="['name', 'matricula', 'situacao_matricula']"
                editMode="cell"
                @cell-edit-complete="onCellEditComplete"
            >
                <template #empty> Não foram encontrados estudantes. </template>
                <!-- Coluna do CheckBox -->
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <!-- Coluna Código do Aluno -->
                <Column field="code" header="Cód. Aluno" sortable style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.code }}
                    </template>
                </Column>
                <!-- Coluna Nome do Aluno -->
                <Column field="name" header="Nome" sortable style="min-width: 14rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <!-- Coluna Matrícula do Aluno -->
                <Column field="matricula" header="Matricula" sortable style="min-width: 10rem">
                    <template #body="{ data }">
                        {{ data.matricula }}
                    </template>
                </Column>
                <!-- Coluna Situação da Matrícula -->
                <Column field="situacao_matricula" header="Status" sortable style="min-width: 12rem">
                    <template #body="{ data }">
                        <Tag :value="data.situacao_matricula" :severity="getSeverity(data.situacao_matricula)" />
                    </template>
                </Column>
                <!-- Coluna Email do Responsável -->
                <Column field="email_responsavel" header="Email Responsável" sortable style="min-width: 20rem">
                    <template #body="{ data }">
                        {{ data.email_responsavel }}
                    </template>
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" autofocus/>
                    </template>
                </Column>
                <!-- Coluna Telefone Responsável com editor habilitado -->
                <Column field="telefone_responsavel" header="Telefone Responsável" sortable style="min-width: 15rem">
                    <template #body="{ data }">
                        {{ data.telefone_responsavel }}
                    </template>
                    <template #editor="{ data, field }">
                        <InputText v-model="data[field]" autofocus v-mask="['(##)#####-####', '(##)####-####']"/>
                    </template>
                </Column>
            </DataTable>

            <div style="text-align: center; margin-top: 1rem">
                <Button icon="pi pi-file-edit" label="Escrever Mensagem" @click="verificaSelecaoAlunos" />
            </div>
        </div>
        <div v-else-if="alunosResponse && alunos.length <= 0">
            <div class="card d-flex flex-column justify-content-center align-items-center text-center">
                <i class="pi pi-exclamation-triangle text-yellow-300" style="font-size: 4rem"></i>
                <h2>Essa turma não possui alunos matriculados!</h2>
            </div>
        </div>
        <div v-if="exibirModalMsg" class="card flex justify-center">
            <Dialog v-model:visible="exibirModalMsg"
                    modal
                    header="Mensagem"
                    :style="{ width: '45vw', height: 'auto' }"
                    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
                <div class="msg-wrapper">
                    <div class="metodos-envio-container">
                        <h4 class="mb-1">Método de envio:</h4>
                        <SelectButton
                            v-model="form.slctMetodoEnvio"
                            :options="isSecretaria ? metodoEnvio : metodoEnvioFiltrado"
                            option-label="label"
                            option-value="value"
                            dataKey="value"
                            aria-labelledby="basic"
                            allowEmpty
                            @change="changeMetodoEnvio">
                        </SelectButton>
                    </div>
                    <div v-if="!isWhatsApp">
                        <span class="p-float-label mt-1">
                            <Textarea class="mx-auto" id="mensagem" v-model="form.mensagem.value" rows="5" cols="50" />
                            <label for="mensagem">{{ form.mensagem.label }}</label>
                        </span>
                    </div>
                    <div v-else class="whats-container">
                        <Dropdown
                            v-model="form.slctTemplate"
                            :options="templates"
                            optionLabel="resumo"
                            placeholder="Selecione um template"
                            class="w-full md:w-35rem"
                            @change="formataMensagem"
                        />
                        <div class="whatsapp">
                            <div class="whats-message" v-if="htmlContent !== ''">
                                <div v-html="htmlContent"></div>
                                <span> {{ horaAtual }} </span>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div style="text-align: center; margin-top: 1rem">
                        <Button icon="pi pi-send" label="Enviar Mensagem" @click="validador" />
                    </div>
            </Dialog>
        </div>
        <!-- Caixa de diálogo para confirmação de alteração do contato do responsável -->
        <Dialog v-model:visible="confirmDialog" modal header="Salvar" :style="{ width: '25rem' }">
            <div v-if="!flagAlteraEmail">
                <p>Confirma a alteração de <b>{{ numAnterior }}</b> para <b>{{ numComMascara }}</b>?</p>
            </div>
            <div v-else>
                <p>Confirma a alteração de <b>{{ emailAnterior }}</b> para <b>{{ emailNovo }}</b>?</p>
            </div>
            <div class="flex justify-content-end gap-2">
                <Button type="button" label="Cancelar" severity="secondary" @click="[confirmDialog=false, flagAlteraEmail=false]"></Button>
                <Button type="button" label="Salvar" @click="salvarContato"></Button>
            </div>
        </Dialog>
        <div v-if="semPermissao" class="img-sem-permissao">
            <Image
                :src="imgSrc"
                alt="Imagem em desenho de uma mulher em pé olhando uma mensagem escrita 'Sem Permissão'."
                width="640">
            </Image>
        </div>
    </div>
    <ModalLoading :isLoading="loading" :message="mensagemModal"/>
</template>

<script>
import ModalLoading from "@modules/Components/ModalLoading.vue";
import VerificaEscola from "@modules/Educacao/Escola/Components/VerificaEscola.vue";
import Fieldset from "primevue/fieldset";
import Dropdown from 'primevue/dropdown';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

import {ref} from 'vue';
import {useToast} from "primevue/usetoast";

export default {
    props: {
        departamento: String,
        modulo: String
    },
    components: {
        ModalLoading,
        Fieldset,
        VerificaEscola,
        DataTable,
        Column,
        Dropdown,
        InputText,
        Button,
        Tag,
    },
    data() {
        return {
            escola: '',
            confirmDialog: ref(false),
            numAnterior: ref(''),
            numNovo: ref(''),
            numComMascara: ref(''),
            idAluno: ref(0),
            emailAnterior: ref(''),
            emailNovo: ref(''),
            flagAlteraEmail: ref(false),
            tempData: null,
            htmlContent: ref(''),
            ecidadepath : window.ECIDADE_PATH,
            inputs : [],
            imgSrc : ref(''),
            semPermissao: ref(false),
            metodosEnviosEscola: {
                permiteSMS: ref(false),
                permiteWhatsApp: ref(false),
                permiteEmail: ref(false),
            },
            routes: {
                escolas: `v4/api/educacao/escola/${this.departamento}`,
                atualizaContato: `v4/api/educacao/escola/alunos/atualiza-contato-responsavel`,
                parametrosEscola: `v4/api/educacao/secretaria/parametros`,
                templates: `v4/api/educacao/secretaria/cadastros/templates/templates-ativos`
            },
            form: {
                slctEscola: {
                    label: 'Escola',
                    data: []
                },
                slctTurma: {
                    label: 'Turma',
                    data: []
                },
                slctTemplate: {
                    label: 'Selecione',
                    data: []
                },
                alunosSelecionados: ref(null),
                slctMetodoEnvio: null,
                mensagem: {
                    label: 'Mensagem',
                    value: ''
                },
                parametros: {},
            },
            escolas: [{
                name: 'Selecione',
                code: null
            }],
            calendarios: [],
            codCalendarios: null,
            turmas: [{
                name: 'Selecione',
                code: null
            }],
            alunos: [],
            templates: [{
                code: null,
                mensagem: ''
            }],
            exibirModalMsg: ref(false),
            mensagemModal: ref(''),
            mensagens: {
                salvarContato: 'Alterando contato do responsável!',
                buscandoParametros: 'Buscando parâmetros...',
                buscandoAlunos: 'Buscando alunos...',
                enviandoNotificacao: 'Enviando notificação...'
            },
            templateMessage: ref(''),
            metodoEnvio: [
                {label: 'WhatsApp', value: 1},
                {label: 'SMS', value: 2},
                {label: 'Email', value: 3}
            ],
            metodoEnvioFiltrado: [],
            isTurmasCarregadas: false,
            isSecretaria: false,
            isEscola: false,
            codigoEscola: '',
            loading: false,
            isWhatsApp: false,
            alunosResponse: false,
            alunosExcluidos: [],
            toast: useToast(),
            situacoesMatricula: [
                'AVANÇADO',
                'CLASSIFICADO',
                'MATRICULADO',
                'RECLASSIFICADO'
            ],
            horaAtual: '',
        };
    },
    methods: {
        async inicializaComponente() {
            this.isSecretaria = this.modulo == 7159;
            this.escola = this.departamento;
            await this.verificaDepartamento();
            if (this.isSecretaria) {
                await this.buscaTemplatesWhatsApp();
                await this.buscaEscolas();
            }
            if (!this.isSecretaria && this.isEscola) {
                await this.verificaParametros();

                if (this.metodosEnviosEscola.permiteSMS || this.metodosEnviosEscola.permiteEmail || this.metodosEnviosEscola.permiteWhatsApp) {
                    await this.buscarCalendarios();
                    if (this.metodosEnviosEscola.permiteWhatsApp) {
                        await this.buscaTemplatesWhatsApp();
                    }
                } else {
                    this.$toast.add({
                        severity: 'error',
                        summary: 'Erro',
                        detail: 'A escola não tem permissão para envio de mensagens e notificações. Entre em contato com a Secretaria de Educação!',
                        life: 15000
                    });
                    this.imgSrc = this.ecidadepath + 'imagens/educacao/secretaria/SemPermissao.png';
                    this.semPermissao = true;
                }
            }
        },
        async verificaParametros() {
            this.loading = true;
            this.mensagemModal = this.mensagens.buscandoParametros;
            const escola = this.isSecretaria ? this.form.slctEscola.data.code : this.departamento;

            try {
                const response = await window.axios.get(`${this.routes.parametrosEscola}/${escola}`);

                if (response.data.data === null || response.data.data.length <= 0) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Atenção!',
                        detail: 'A escola selecionada não possui parâmetros de envio configurados!',
                        life: 15000
                    });
                    return false;
                }

                response.data.data.map(parametro => {
                    this.metodosEnviosEscola.permiteSMS = parametro.ed204_permite_sms
                    this.metodosEnviosEscola.permiteEmail = parametro.ed204_permite_email
                    this.metodosEnviosEscola.permiteWhatsApp = parametro.ed204_permite_whatsapp
                });
                this.metodosEnviosFiltrados();
                return true;

            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erro!',
                    detail: `${e.response.data.message}`,
                    life: 15000
                });
            } finally {
                this.loading = false;
            }
        },
        atualizaIsEscola(e) {
            this.isEscola = e;
        },
        async verificaDepartamento() {
            try {
                this.loading = true;
                const response = await window.axios.get(this.routes.escolas);
                this.isEscola = response.data.data !== null;
                this.loading = false;
            } catch (error) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erro',
                    detail: 'Ocorreu um erro ao processar a solicitação',
                    life: 10000
                });
                this.loading = false;
            }
        },
        async buscaEscolas() {
            this.loading = true;
            try {
                const response = await window.axios.get(
                    `v4/api/educacao/escola/`
                );
                this.loading = false;
                response.data.data.map(escola => {
                    this.escolas.push({
                        name: escola.ed18_c_nome,
                        code: escola.ed18_i_codigo
                    });
                });
            } catch (e) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erro',
                    detail: `${e.response.data.message}`,
                    life: 5000
                });
                this.loading = false;
            }
        },
        async buscarCalendarios() {
            if (await this.verificaParametros()) {
                this.loading = true;
                this.isTurmasCarregadas = false;
                this.alunosResponse = false;
                this.codigoEscola = this.isSecretaria ? this.form.slctEscola.data.code : this.departamento;

                try {
                    const response = await window.axios.get(
                        `v4/api/educacao/escola/${this.codigoEscola}/calendario`
                    );
                    this.loading = false;
                    const anoAtual = new Date().getFullYear();
                    this.calendarios = [];
                    response.data.data
                        .filter(calendario => calendario.ano === anoAtual)
                        .map(calendario => {
                            this.calendarios.push({
                                code: calendario.codigo
                            });
                        });

                    if (this.calendarios.length > 0) {
                        this.codCalendarios = this.calendarios.map(calendario => calendario.code).join('&');
                        await this.buscarTurmas();
                    } else {
                        this.toast.add({
                            severity: 'warn',
                            summary: 'Atenção',
                            detail: 'Escola não possui turmas no calendário atual!',
                            life: 10000
                        });
                    }
                } catch (e) {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erro',
                        detail: `${e.response.data.message}`,
                        life: 5000
                    });
                } finally {
                    this.loading = false;
                }
            }
        },
        async buscarTurmas() {
            this.turmas = [];
            this.loading = true;
            try {
                const response = await window.axios.get(
                    `v4/api/educacao/escola/turmas-por-escola-calendarios/${this.codigoEscola}/${this.codCalendarios}`
                );
                response.data.data.map(turma => {
                    this.turmas.push({
                        name: turma.ed57_c_descr,
                        code: turma.ed57_i_codigo
                    });
                });
                this.loading = false;
                this.isTurmasCarregadas = true;
            } catch (e) {
                this.$toast.add({
                    severity: 'error',
                    summary: 'Erro',
                    detail: `${e.response.data.message}`,
                    life: 5000
                });
                this.loading = false;
                this.isTurmasCarregadas = false;
            }
        },
        async buscarAlunos() {
            this.form.alunosSelecionados = [];
            this.loading = true;
            this.mensagemModal = this.mensagens.buscandoAlunos
            this.alunosResponse = false;
            try {
                const response = await window.axios.get(
                    `v4/api/educacao/escola/alunos/alunos-por-turma/${this.form.slctTurma.data.code}`
                );
                this.alunos = response.data.data
                    .filter(aluno => this.situacoesMatricula.includes(aluno.situacao_matricula))
                    .map(aluno => {
                        return {
                            name: aluno.nome,
                            code: aluno.codigo,
                            matricula: aluno.matricula,
                            situacao_matricula: aluno.situacao_matricula.toUpperCase(),
                            email_responsavel: aluno.email_responsavel.toLowerCase() || 'N/A',
                            telefone_responsavel: aluno.telefone_responsavel || 'N/A'
                        };
                    });
                this.alunosResponse = true;
                this.loading = false;
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erro',
                    detail: `${e.response.data.message}`,
                    life: 5000
                });
                this.loading = false;
            }
        },
        getSeverity(status) {
            switch (status) {
                case 'MATRICULADO':
                case 'AVANÇADO':
                case 'CLASSIFICADO':
                case 'RECLASSIFICADO':
                    return 'success';
                case 'PENDENTE':
                case 'INFREQUENTE':
                    return 'warning';
                case 'TRANSFERIDO FORA':
                case 'TRANSFERIDO REDE':
                case 'TROCA DE TURMA':
                    return 'info';
                case 'CANCELADO':
                case 'EVADIDO':
                case 'MATRICULA TRANCADA':
                case 'FALECIDO':
                case 'MATRICULA INDEFERIDA':
                case 'MATRICULA INDEVIDA':
                case 'DESISTENTE':
                default:
                    return null;
            }
        },
        verificaSelecaoAlunos() {
            if (this.form.alunosSelecionados !== null) {
                const qtdAlunos = this.form.alunosSelecionados.map(aluno => {
                    return {
                        codigo: aluno.code,
                        name: aluno.name
                    }
                });

                if (qtdAlunos.length > 0) {
                    this.exibirModalMsg = true;
                } else {
                    this.toast.add({
                        severity: 'warn',
                        summary: 'Atenção',
                        detail: 'Por favor, selecione ao menos um aluno.',
                        life: 10000
                    });
                }
            } else {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Atenção',
                    detail: 'Por favor, selecione ao menos um aluno.',
                    life: 10000
                });
            }
        },
        changeMetodoEnvio() {
            this.isWhatsApp = this.form.slctMetodoEnvio === 1;
            this.getHoraAtual();
        },
        metodosEnviosFiltrados() {
            this.metodoEnvioFiltrado =  this.metodoEnvio.filter(opcao => {
                if (opcao.value === 1 && this.metodosEnviosEscola.permiteWhatsApp) {
                    return true;
                }
                if (opcao.value === 2 && this.metodosEnviosEscola.permiteSMS) {
                    return true;
                }
                if (opcao.value === 3 && this.metodosEnviosEscola.permiteEmail) {
                    return true;
                }
                return false;
            });
        },
        getHoraAtual() {
            const now = new Date();
            const horas = now.getHours().toString().padStart(2, '0');
            const minutos = now.getMinutes().toString().padStart(2, '0');
            this.horaAtual = `${horas}:${minutos}`;
        },
        validador() {
            if (this.isSecretaria) {
                this.form.parametros.escola = this.form.slctEscola.data.code
            } else {
                this.form.parametros.escola = this.escola
            }

            this.form.parametros.turma =  this.form.slctTurma.data.code

            this.form.parametros.alunos = [];

            this.form.alunosSelecionados.forEach(aluno => {
                this.form.parametros.alunos.push({
                    name: aluno.name,
                    code: aluno.code,
                    matricula: aluno.matricula,
                    situacao_matricula: aluno.situacao_matricula,
                    email_responsavel: aluno.email_responsavel,
                    telefone_responsavel: aluno.telefone_responsavel
                })
            });

            if (this.form.slctMetodoEnvio !== 1 && this.form.mensagem.value === '') {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Atenção!',
                    detail: 'Insira a mensagem de envio',
                    life: 10000
                });
                return false;
            }

            if (!this.form.slctMetodoEnvio) {
                this.toast.add({
                    severity: 'warn',
                    summary: 'Atenção!',
                    detail: 'Selecione o método de envio!',
                    life: 10000
                });
                return false;
            }

            this.form.parametros.metodoEnvio = this.form.slctMetodoEnvio;

            if (this.form.parametros.metodoEnvio === 1) {
                this.form.parametros.mensagem = this.gerarMensagemWhats()
                this.form.parametros.templateWhats = this.form.slctTemplate.code
            } else {
                this.form.parametros.mensagem = this.form.mensagem.value
            }

            if (this.verificaMetodoEnvioEAlunos()) {
                this.emitir()
            }
        },
        verificaMetodoEnvioEAlunos() {

            this.alunosExcluidos = [];

            if (this.form.parametros.metodoEnvio === 3) {
                this.form.parametros.alunos.forEach(aluno => {
                    if (aluno.email_responsavel === 'N/A') {
                        this.alunosExcluidos.push({
                            name: aluno.name
                        })
                    }
                });

                this.form.parametros.alunos = this.form.parametros.alunos.filter(aluno => aluno.email_responsavel !== 'N/A');

                if (this.form.parametros.alunos.length <= 0) {
                    this.toast.add({
                        summary: 'Erro!',
                        severity: 'error',
                        detail: 'Nenhum dos alunos selecionados possuem email cadastrado!',
                        life: 10000
                    });
                    return false;
                }

                if (this.alunosExcluidos.length > 0 && this.form.parametros.alunos.length > 0) {
                    this.toast.add({
                        summary: 'Atenção!',
                        severity: 'warn',
                        detail: 'Os responsáveis dos alunos que não possuem email cadastrado, não receberão a mensagem!',
                        life: 10000
                    });
                    return true;
                }
                return true;
            }

            this.form.parametros.alunos.forEach(aluno => {
                if (aluno.telefone_responsavel === 'N/A') {
                    this.alunosExcluidos.push({
                        name: aluno.name
                    })
                }
            });

            this.form.parametros.alunos = this.form.parametros.alunos.filter(aluno => aluno.telefone_responsavel !== 'N/A');

            this.trataNumeroTelefone();

            if (this.form.parametros.alunos.length <= 0) {
                this.toast.add({
                    summary: 'Erro!',
                    severity: 'error',
                    detail: 'Nenhum dos alunos selecionados possuem telefone do responsável cadastrado!',
                    life: 10000
                });
                return false;
            }

            if (this.alunosExcluidos.length > 0 && this.form.parametros.alunos.length > 0) {
                this.toast.add({
                    summary: 'Atenção!',
                    severity: 'warn',
                    detail: 'Os responsáveis dos alunos que não possuem telefone cadastrado, não receberão a mensagem!',
                    life: 10000
                });
                return true;
            }
            return true;
        },
        trataNumeroTelefone() {
            this.form.parametros.alunos.map(aluno => {
                if ((aluno.telefone_responsavel.length === 8) && !aluno.telefone_responsavel.startsWith('+55')) {
                    aluno.telefone_responsavel = '+559' + aluno.telefone_responsavel;
                }
                if (!aluno.telefone_responsavel.startsWith('+55')) {
                    aluno.telefone_responsavel = '+55' + aluno.telefone_responsavel;
                }
            });
        },
        async emitir() {
            try {
                this.mensagemModal = this.mensagens.enviandoNotificacao;
                this.loading = true;
                const response = await window.axios.post(
                    'v4/api/educacao/escola/envio-notificacao',
                    this.form.parametros
                );
                this.loading = false;
                if (response.status === 200) {
                    this.toast.add({
                       severity: 'success',
                       summary: 'Sucesso!',
                       detail: 'Notificação enviada com sucesso!',
                       life: 15000
                    });
                    this.exibirModalMsg = false;
                } else {
                    this.toast.add({
                        severity: 'error',
                        summary: 'Erro!',
                        detail: 'Falha ao enviar a notificação.',
                        life: 5000
                    });
                    this.exibirModalMsg = false;
                }
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erro',
                    detail: `${e.response.data.message}`,
                    life: 5000
                });
                this.loading = false;
                this.exibirModalMsg = false;
            }
            this.loading = false;
            this.exibirModalMsg = false;
        },
        onCellEditComplete(event) {
            let { data, newValue, field } = event;

            if (field === 'telefone_responsavel') {
                this.idAluno = data.code;
                this.numAnterior = data.telefone_responsavel;
                this.numComMascara = newValue;
                this.numNovo = this.numComMascara.replace(/\D/g, '');
                this.tempData = data;

                this.numAnterior !== this.numNovo ? this.confirmDialog = true : this.confirmDialog = false;
            } else {
                this.flagAlteraEmail = true;
                this.idAluno = data.code;
                this.emailAnterior = data.email_responsavel;
                this.emailNovo = newValue;
                this.tempData = data;

                this.emailAnterior !== this.emailNovo ? this.confirmDialog = true : this.confirmDialog = false;
            }
        },
        async salvarContato() {
            const dados = {
                id: this.idAluno,
                telefone_responsavel: this.numNovo,
                email_responsavel: this.emailNovo
            }

            try {
                this.loading = true;
                this.mensagemModal = this.mensagens.salvarContato;
                this.confirmDialog = false

                await window.axios.put(this.routes.atualizaContato, dados);

                // Atualiza o número de telefone ou email localmente após o sucesso da requisição
                if (this.tempData) {
                    if (this.flagAlteraEmail) {
                        this.tempData.email_responsavel = this.emailNovo;
                    } else {
                        this.tempData.telefone_responsavel = this.numNovo;
                    }
                }
                this.toast.add({
                    severity: 'success',
                    summary: 'Sucesso!',
                    detail: 'O contato do responsável foi atualizado com sucesso!',
                    life: 15000
                });
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erro!',
                    detail: 'Não foi possível atualizar o contato do responsável!!',
                    life: 15000
                });
            } finally {
                this.numNovo = '';
                this.emailNovo = '';
                this.loading = false;
                this.tempData = null;
                this.flagAlteraEmail = false;
            }
        },
        async buscaTemplatesWhatsApp() {
            try {
                const response = await window.axios.get(this.routes.templates);
                if (response.data.data.length > 0) {
                    response.data.data.map(template => {
                       this.templates.push({
                           code: template.ed203_template_id,
                           mensagem: template.ed203_mensagem,
                           resumo: template.ed203_mensagem.slice(0,50) + ' (...)'
                       });
                    });
                }
            } catch (e) {
                this.toast.add({
                    severity: 'error',
                    summary: 'Erro',
                    detail: `${e.response.data.message}`,
                    life: 5000
                });
            } finally {
                this.loading = false;
            }
        },
        formataMensagem() {
            const mensagem = this.form.slctTemplate.mensagem
            let novaMensagem = '';
            let inputIndex = 0;

            if (mensagem.includes('[####]')) {
                window.inputTemplate = (e) => this.inputTemplate(e);
                novaMensagem = mensagem.replace(
                    /\[####]/g,
                    () => {
                        const inputHtml = `<input style="height: 1rem" placeholder="Digite aqui" name="input-${inputIndex}" data-index="${inputIndex}" onchange="window.inputTemplate(event)"/>`;
                        inputIndex++;
                        return inputHtml;
                    }
                );
                this.htmlContent = '<p>' + novaMensagem + '</p>';
            } else {
                this.htmlContent = '<p>' + mensagem + '</p>';
            }
        },
        inputTemplate(e) {
            const inputIndex = e.currentTarget.getAttribute('data-index');
            const inputValue = e.currentTarget.value;
            // Verifica se o índice já existe no array
            const existingInput = this.inputs.find(input => input.indice === inputIndex);

            if (existingInput) {
                // Se o índice já existe, atualiza o valor
                existingInput.valor = inputValue;
            } else {
                // Se o índice não existe, adiciona o novo objeto
                this.inputs.push({
                    indice: inputIndex,
                    valor: inputValue
                });
            }
        },
        gerarMensagemWhats() {
            let mensagem = this.form.slctTemplate.mensagem;
            let inputIndex = 0;

            // Substitui cada ocorrência de [####] pelos valores armazenados na variável inputs
            mensagem = mensagem.replace(/\[####]/g, () => {
                // Encontra o input correspondente ao índice atual
                const input = this.inputs.find(input => input.indice == inputIndex);
                // Retorna o valor correspondente ou [####] se não encontrado
                const inputValue = input ? input.valor : '[####]';
                inputIndex++; // Incrementa para a próxima ocorrência de [####]
                return inputValue;
            });

            return mensagem;
        }
    },
    async mounted() {
        await this.inicializaComponente();
    }
}
</script>

<style lang="scss" scoped>
.container-wrapper {
    background-color: #E0E0E0;
    max-width: 80%;
    margin: 2rem auto;
    padding: 2rem;
    box-sizing: border-box;
    border-radius: 0.6rem;
}

.select-escola, .select-turma {
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    margin: 2rem auto;
    padding: 2rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    background-color: #fff;
    border-radius: 0.6rem;
}

.data-table {
    width: 100%;
    overflow-x: auto;
    margin-bottom: 1rem;
}

.data-table th, .data-table td {
    text-align: left;
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
}

.data-table th {
    font-weight: bold;
    background-color: #f8f9fa;
}

.msg-wrapper {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.button-metodo {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.whatsapp {
    width: 32rem;
    min-height: 10rem;
    background-color: lightgoldenrodyellow;
    background-image: url("https://i.imgur.com/IExa5Ik.png");
    border-radius: 0.5rem;
    display: flex;
    align-items: flex-end; /* Alinhar ao fundo */
    justify-content: flex-end; /* Alinhar à direita */
    padding: 1rem; /* Adicionar espaço interno */
    position: relative; /* Para posicionamento absoluto dentro */
    box-sizing: border-box; /* Inclui o padding no tamanho total */
    flex-direction: column;
}

.whats-message {
    box-shadow: rgba(0, 0, 0, 0.13) 0 1px 0.5px 0;
    background-color: #DCF8C6;
    margin: 1rem 0;
    padding: 0.5rem 2.5rem 0.5rem 0.5rem; /* Espaço extra à direita para o horário */
    border-radius: 0.3rem;
    display: flex;
    align-items: flex-end; /* Alinhar conteúdo ao fundo */
    position: relative; /* Para posicionamento absoluto do span */
    max-width: 80%; /* Limitar a largura da mensagem */
    word-wrap: break-word;
    box-sizing: border-box;
}

.whats-message p {
    font-size: 14px;
    margin: 0;
    padding: 0;
    max-width: calc(100% - 3rem); /* Evitar sobreposição com o span */
    word-wrap: break-word; /* Quebrar palavras longas */
}

.whats-message span {
    position: absolute;
    bottom: 0.3rem; /* Alinhar ao fundo */
    right: 0.5rem; /* Alinhar à direita */
    font-size: 11px;
    color: lightslategray;
}

.whats-container {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.p-dropdown-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; /* Trunca o texto do Dropdown */
    display: block;
}

.img-sem-permissao {
    height: 80vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.metodos-envio-container {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    align-items: center;
    justify-content: center;
}

</style>
