<script setup>
import {ref, watch} from 'vue';
import {useToast} from "primevue/usetoast";
import Calendar from 'primevue/calendar';
import ModalLoading from "../../../Components/ModalLoading";

const showDialod = ref(false);
const isFisica = ref(false);
const isJuridica = ref(false);
const enderecos = ref([]);
const modalLoading = ref(false);
const toast = useToast();
const textLoad = ref('');
const mascaraInput = ref('##############');
const validaCpfCnpjAviso = ref(false);
const dataNascimentoError = ref(false);
const sexoError = ref(false);
const disabledBtnSubmit = ref(true);
const cpfCnpj = ref(['']);
const placeHolderSexo = ref('');
const acao = ref(0);
const sexos = ref([
  { name: 'Masculino', code: 'M' },
  { name: 'Feminino', code: 'F' },
]);
const emit = defineEmits(['dialogClosed']);

const newCgm = ref({
    cpfcnpj: null,
    nomeRazaoSocial: null,
    dataNascimento: null,
    sexo: null,
    email: null,
    nomeMae: null,
    nomePai: null,
    telefone: null,
    celular: null,
    cep: null,
    pais: null,
    estado: null,
    uf: null,
    municipio: null,
    bairro: null,
    logradouro: null,
    numeroEndereco: null,
    complemento: null
});

watch(cpfCnpj, (newValue) => {
    changeMask();
    clearCampos(false);
});

watch(() => newCgm.value.dataNascimento, (newValue) => {
    if (newValue !== null || newValue !== '') {
        dataNascimentoError.value = false;
    }
});

watch(() => newCgm.value.sexo, (newValue) => {
  if (newValue !== null || newValue !== '') {
    sexoError.value = false;
  }
});

function closeDialog() {
    clearCampos();
    isFisica.value = false;
    isJuridica.value = false;
    showDialod.value = false;
    validaCpfCnpjAviso.value = false;
    disabledBtnSubmit.value = true;
    dataNascimentoError.value = false;
    sexoError.value = false;
    acao.value = 0;
    placeHolderSexo.value = '';
    emit('dialogClosed');
}

const openDialog = (e) => {
    showDialod.value = true;
}

function clearCampos(cpfcnpj = true) {
    if (cpfcnpj) {
        cpfCnpj.value = null;
    }

    newCgm.value.nomeRazaoSocial = null;
    newCgm.value.dataNascimento = null;
    newCgm.value.sexo = null;
    newCgm.value.email = null;
    newCgm.value.nomeMae = null;
    newCgm.value.nomePai = null;
    newCgm.value.telefone = null;
    newCgm.value.celular = null;
    newCgm.value.cep = null;
    newCgm.value.pais = null;
    newCgm.value.estado = null;
    newCgm.value.uf = null;
    newCgm.value.municipio = null;
    newCgm.value.bairro = null;
    newCgm.value.logradouro = null;
    newCgm.value.numeroEndereco = null;
    newCgm.value.complemento = null;
}

const salvarCgm = async () => {
    const parametros = {};
    parametros.cpfcnpj = cpfCnpj.value;
    parametros.nomeRazaoSocial = newCgm.value.nomeRazaoSocial;
    parametros.dataNascimento = newCgm.value.dataNascimento;
    parametros.sexo = newCgm.value.sexo;
    parametros.email = newCgm.value.email;
    parametros.nomeMae = newCgm.value.nomeMae;
    parametros.nomePai = newCgm.value.nomePai;
    parametros.telefone = newCgm.value.telefone;
    parametros.celular = newCgm.value.celular;
    parametros.cep = newCgm.value.cep;
    parametros.pais = newCgm.value.pais;
    parametros.estado = newCgm.value.estado;
    parametros.uf = newCgm.value.uf;
    parametros.municipio = newCgm.value.municipio;
    parametros.bairro = newCgm.value.bairro;
    parametros.logradouro = newCgm.value.logradouro;
    parametros.numeroEndereco = newCgm.value.numeroEndereco;
    parametros.complemento = newCgm.value.complemento;
    textLoad.value = "Salvando CGM...";
    modalLoading.value = true;

    try {
        const resp = await axios.post(
            "v4/api/patrimonial/protocolo/salvar-cgm",
            parametros
        );

        if (resp.data.data.erro) {
            alert(resp.data.data.mensagem);
        } else {
            toast.add({severity: 'success', summary: 'Success', detail: 'CGM salvo com sucesso', life: 5000});
        }
        clearCampos();
        modalLoading.value = false;
        isFisica.value = false;
        isJuridica.value = false;
        validaCpfCnpjAviso.value = false;
        disabledBtnSubmit.value = true;
        dataNascimentoError.value = false;
        sexoError.value = false;
        acao.value = 0;
        placeHolderSexo.value = '';
        closeDialog();
    } catch (e) {
        clearCampos();
        isFisica.value = false;
        isJuridica.value = false;
        validaCpfCnpjAviso.value = false;
        modalLoading.value = false;
        disabledBtnSubmit.value = true;
        dataNascimentoError.value = false;
        sexoError.value = false;
        acao.value = 0;
        placeHolderSexo.value = '';
    }
}

function changeMask() {
    mascaraInput.value = '##############';

    if (cpfCnpj.value != null) {
        var cpfcnpjAux = cpfCnpj.value.replace(/[^\d]/g, '');
        if (cpfcnpjAux.length === 11) {
            if (validarCPF(cpfCnpj.value)) {
                mascaraInput.value = '###.###.###-##';
                isJuridica.value = false;
                isFisica.value = true;
                validaCpfCnpjAviso.value = false;
            } else {
                validaCpfCnpjAviso.value = true;
            }
        }

        if (cpfcnpjAux.length === 14) {
            if (validarCNPJ(cpfCnpj.value)) {
                mascaraInput.value = '##.###.###/####-##';
                isFisica.value = false;
                isJuridica.value = true;
                validaCpfCnpjAviso.value = false;
            } else {
                validaCpfCnpjAviso.value = true;
            }
        }
    }
}

function cleanMask() {
    if (cpfCnpj.value != null) {
        if (cpfCnpj.value.length < 14) {
            mascaraInput.value = '##############';
            isFisica.value = false;
            isJuridica.value = false;
            validaCpfCnpjAviso.value = false;
            disabledBtnSubmit.value = true;
            clearCampos(false);
        }
    }
}

async function pesquisarEnderecos() {
  if (newCgm.value.cep.length !== null) {
      if (newCgm.value.cep.length === 9) {
          clearCamposEndereco();
          try {
              textLoad.value = "Buscando CEP...";
              modalLoading.value = true;
              const resp = await window.axios.get(
                  `v4/api/patrimonial/protocolo/endereco-localidade-cep?cep=${newCgm.value.cep}`
              );
              enderecos.value = resp.data.data;
              modalLoading.value = false;
              completeCampos();
          } catch (e) {
              modalLoading.value = false;
              enderecos.value = [];
          }
      }
  }
}

const completeCampos = () => {
    var endreco = enderecos.value;
    newCgm.value.cep = endreco.cep;
    newCgm.value.pais = endreco.descricao_pais;
    newCgm.value.estado = endreco.descricao_estado;
    newCgm.value.uf = endreco.sigla_estado;
    newCgm.value.municipio = endreco.descricao_cidade;
    newCgm.value.bairro = endreco.descricao_bairro;
    newCgm.value.logradouro = endreco.descricao_rua;
}

function clearCamposEndereco() {
  newCgm.value.pais = null;
  newCgm.value.estado = null;
  newCgm.value.uf = null;
  newCgm.value.municipio = null;
  newCgm.value.bairro = null;
  newCgm.value.logradouro = null;
}

function validarCPF(cpf) {
    // Remove caracteres não numéricos
    cpf = cpf.replace(/[^\d]/g, '');

    if (cpf.length !== 11 || !/^\d{11}$/.test(cpf)) return false;

    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1+$/.test(cpf)) return false;

    // Calcula os dígitos verificadores
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }

    let resto = 11 - (soma % 11);
    let digito1 = resto >= 10 ? 0 : resto;

    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }

    resto = 11 - (soma % 11);
    let digito2 = resto >= 10 ? 0 : resto;

    // Verifica se os dígitos calculados são iguais aos dígitos informados
    return digito1 === parseInt(cpf.charAt(9)) && digito2 === parseInt(cpf.charAt(10));
}

function validarCNPJ(cnpj) {
    // Remove caracteres não numéricos
    cnpj = cnpj.replace(/[^\d]/g, '');

    if (cnpj.length !== 14 || !/^\d{14}$/.test(cnpj)) return false;

    // Verifica se todos os dígitos são iguais
    if (/^(\d)\1+$/.test(cnpj)) return false;

    // Calcula os dígitos verificadores
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
        soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
        if (pos < 2) pos = 9;
    }

    let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado !== parseInt(digitos.charAt(0))) return false;

    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;

    for (let i = tamanho; i >= 1; i--) {
        soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
        if (pos < 2) pos = 9;
    }

    resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (resultado !== parseInt(digitos.charAt(1))) return false;

    return true;
}

async function pesquisaCpfCnpj(cgmEditar = null) {
    if (cgmEditar !== null) {
        cpfCnpj.value = cgmEditar.cpfcnpj;
        isFisica.value = cgmEditar.isFisica;
        isJuridica.value = cgmEditar.isJuridica;
    }

    if (isFisica.value === true || isJuridica.value === true) {
        try {
            placeHolderSexo.value = '';
            acao.value = 0;
            textLoad.value = "Verificando CPF/CNPJ...";
            modalLoading.value = true;

            const resp = await window.axios.get(
                `v4/api/patrimonial/protocolo/verifica-cgm?cpfCnpj=${cpfCnpj.value}`
            );

            if (resp.data.data.acao === 100) {
                clearCampos();
                isFisica.value = false;
                isJuridica.value = false;
                validaCpfCnpjAviso.value = false;
                disabledBtnSubmit.value = true;
                dataNascimentoError.value = false;
                sexoError.value = false;
                alert(resp.data.data.mensagem);
            }

            if (resp.data.data.acao === 200) {
                acao.value = 200;
                var retorno = resp.data.data[0];

                if (retorno[0].z01_cgccpf.length === 11) {
                    preecheCgm(retorno[0], true)
                    disabledBtnSubmit.value = false;
                }

                if (retorno[0].z01_cgccpf.length === 14) {
                    preecheCgm(retorno[0], false)
                    disabledBtnSubmit.value = false;
                }
            }

            if (resp.data.data.acao === 300) {
              disabledBtnSubmit.value = false;
            }

            if (resp.data.data.acao === 400) {
              disabledBtnSubmit.value = true;
            }

            modalLoading.value = false;
        } catch (e){
            modalLoading.value = false;
        }
    }
}

function preecheCgm(cgm, fisica = false) {

  if (fisica) {
    newCgm.value.dataNascimento = cgm.nascimentoMask;
    newCgm.value.nomeMae = cgm.z01_mae;
    newCgm.value.nomePai = cgm.z01_pai;

    if (cgm.z01_sexo === "M") {
        placeHolderSexo.value = "Masculino";
    }

    if (cgm.z01_sexo === "F") {
        placeHolderSexo.value = "Feminino";
    }

  }

  newCgm.value.nomeRazaoSocial = cgm.z01_nome;
  newCgm.value.email = cgm.z01_email;
  newCgm.value.telefone = cgm.z01_telef.replace(/[^\d]/g, '');
  newCgm.value.celular = cgm.z01_telcel.replace(/[^\d]/g, '');
  newCgm.value.cep = cgm.z01_cep.replace(/[^\d]/g, '');
  newCgm.value.uf = cgm.z01_uf;
  newCgm.value.municipio = cgm.z01_munic;
  newCgm.value.bairro = cgm.z01_bairro;
  newCgm.value.logradouro = cgm.z01_ender;
  newCgm.value.numeroEndereco = cgm.z01_numero;
}

function validaCampos() {
    var submit = false;

    if (isFisica.value) {
      if (newCgm.value.dataNascimento === null || newCgm.value.dataNascimento === '') {
        dataNascimentoError.value = true;
        scrollToDiv('dataNasc');
      } else if (acao.value !== 200 && (!newCgm.value.sexo || newCgm.value.sexo === '')) {
        sexoError.value = true;
        scrollToDiv('sexo');
      } else {
        submit = true;
      }
    }

    if (isJuridica.value) {
      submit = true;
    }

    if (submit) {
        salvarCgm();
    }
}

function scrollToDiv(divAviso) {
    const element = document.getElementById(`${divAviso}`);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    }
}

defineExpose({
    closeDialog,
    openDialog,
    pesquisaCpfCnpj
});
</script>

<template>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />
    <Dialog
        id="dialog-add-cgm"
        header="Adicionar CGM"
        :modal="true"
        :maximizable="true"
        :style="{ width: '1000px'}"
        :closable="true"
        :visible="showDialod"
        @update:visible="closeDialog"
    >
        <div class="mt-6 justify-content-center" id="campos">
            <form class="form-container" @submit.prevent="validaCampos">
                <fieldset>
                    <div class="field-group">
                        <div class="field direita">
                            <label for="cpfcnpj">CPF/CNPJ <span style="color: red;">*</span></label>
                            <InputText
                                name="cpfcnpj"
                                placeholder=""
                                v-model="cpfCnpj"
                                v-maska="mascaraInput"
                                @input="cleanMask"
                                @blur="pesquisaCpfCnpj(null)"
                                required
                            />
                            <div v-if="validaCpfCnpjAviso">
                                <p style="color: red;">CPF/CNPJ inválido</p>
                            </div>
                        </div>
                        <div class="field">
                            <label for="nomeRazaoSocial">Nome/Razão Social <span style="color: red;">*</span></label>
                            <InputText
                                name="nomeRazaoSocial"
                                placeholder=""
                                v-model="newCgm.nomeRazaoSocial"
                                required
                            />
                        </div>
                    </div>
                    <div v-if="isFisica">
                        <div class="field-group">
                            <div class="field direita">
                                <label for="dataNascimento">Data de Nascimento <span style="color: red;">*</span></label>
                                <Calendar
                                    id="dataNasc"
                                    v-model="newCgm.dataNascimento"
                                    dateFormat="dd/mm/yy"
                                    showIcon
                                    style="width: 30rem; border-radius: 4px;" name="dataNascimento"
                                />
                                <span v-show="dataNascimentoError" style="color: red;">Campo data de nascimento obrigatório</span>
                            </div>
                            <div class="field">
                                <label for="sexo">Sexo <span style="color: red;">*</span></label>
                                <Dropdown
                                    id="sexo"
                                    v-model="newCgm.sexo"
                                    :options="sexos"
                                    optionLabel="name"
                                    class="w-full md:w-30rem"
                                    :placeholder="placeHolderSexo"
                                />
                                <span v-show="sexoError" style="color: red;">Campo sexo obrigatório</span>
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="email">Email <span style="color: red;">*</span></label>
                                <InputText
                                    type="email"
                                    name="email"
                                    placeholder=""
                                    v-model="newCgm.email"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="nomeMae">Nome Mãe <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="nomeMae"
                                    placeholder=""
                                    v-model="newCgm.nomeMae"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="nomePai">Nome Pai:</label>
                                <InputText
                                    type="text"
                                    name="nomePai"
                                    placeholder=""
                                    v-model="newCgm.nomePai"
                                />
                            </div>
                            <div class="field">
                                <label for="telefone">Telefone:</label>
                                <InputText
                                    type="text"
                                    name="telefone"
                                    placeholder="(__)____-____"
                                    v-model="newCgm.telefone"
                                    v-maska="'(##)####-####'"
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="celular">Celular <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="celular"
                                    placeholder="(__)_____-____"
                                    v-model="newCgm.celular"
                                    v-maska="'(##)#####-####'"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="cep">CEP <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="cep"
                                    placeholder=""
                                    v-model="newCgm.cep"
                                    v-maska="'#####-###'"
                                    @blur="pesquisarEnderecos"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="país">País</label>
                                <InputText
                                    type="text"
                                    name="país"
                                    placeholder=""
                                    v-model="newCgm.pais"
                                />
                            </div>
                            <div class="field">
                                <label for="estado">Estado</label>
                                <InputText
                                    type="text"
                                    name="estado"
                                    placeholder=""
                                    v-model="newCgm.estado"
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="uf">UF <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="uf"
                                    placeholder=""
                                    v-model="newCgm.uf"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="municipio">Município <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="municipio"
                                    placeholder=""
                                    v-model="newCgm.municipio"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="uf">Bairro <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="bairro"
                                    placeholder=""
                                    v-model="newCgm.bairro"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="logradouro">Logradouro <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="logradouro"
                                    placeholder=""
                                    v-model="newCgm.logradouro"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="numeroEndereco">Número <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="numeroEndereco"
                                    placeholder=""
                                    v-model="newCgm.numeroEndereco"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="complemento">Complemento:</label>
                                <InputText
                                    type="text"
                                    name="complemento"
                                    placeholder=""
                                    v-model="newCgm.complemento"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-if="isJuridica">
                        <div class="field-group">
                            <div class="field direita">
                                <label for="email">Email <span style="color: red;">*</span></label>
                                <InputText
                                    type="email"
                                    name="email"
                                    placeholder=""
                                    v-model="newCgm.email"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="telefone">Telefone:</label>
                                <InputText
                                    type="text"
                                    name="telefone"
                                    placeholder="(__)____-____"
                                    v-model="newCgm.telefone"
                                    v-maska="'(##)####-####'"
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="celular">Celular <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="celular"
                                    placeholder="(__)_____-____"
                                    v-model="newCgm.celular"
                                    v-maska="'(##)#####-####'"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="cep">CEP <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="cep"
                                    placeholder=""
                                    v-model="newCgm.cep"
                                    v-maska="'#####-###'"
                                    @blur="pesquisarEnderecos"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="país">País</label>
                                <InputText
                                    type="text"
                                    name="país"
                                    placeholder=""
                                    v-model="newCgm.pais"
                                />
                            </div>
                            <div class="field">
                                <label for="estado">Estado</label>
                                <InputText
                                    type="text"
                                    name="estado"
                                    placeholder=""
                                    v-model="newCgm.estado"
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="municipio">Município <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="municipio"
                                    placeholder=""
                                    v-model="newCgm.municipio"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="uf">Bairro <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="bairro"
                                    placeholder=""
                                    v-model="newCgm.bairro"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="logradouro">Logradouro <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="logradouro"
                                    placeholder=""
                                    v-model="newCgm.logradouro"
                                    required
                                />
                            </div>
                            <div class="field">
                                <label for="numeroEndereco">Número <span style="color: red;">*</span></label>
                                <InputText
                                    type="text"
                                    name="numeroEndereco"
                                    placeholder=""
                                    v-model="newCgm.numeroEndereco"
                                    required
                                />
                            </div>
                        </div>
                        <div class="field-group">
                            <div class="field direita">
                                <label for="complemento">Complemento:</label>
                                <InputText
                                    type="text"
                                    name="complemento"
                                    placeholder=""
                                    v-model="newCgm.complemento"
                                />
                            </div>
                        </div>
                    </div>
                </fieldset>
                <button :class="['btn btn-block']" type="submit" v-if="!disabledBtnSubmit">Salvar</button>
            </form>
        </div>
    </Dialog>
</template>

<style scoped>
.direita {
    margin-right: 50px;
}

input {
    height: 40px;
    width: 30rem;
    border-radius: 4px;
    border-width: 1px;
}

fieldset {
    border: none;
    font-weight: bold;
}

button {
    text-align: center;
    border-radius: 25px;
    width: 170px;
    height: 60px;
    border: 1px solid #2a60ff;
    background: #2a60ff;
    color: white;
}

button:hover {
    cursor: pointer;
    background: #597af1;
}

.field-group {
    margin-bottom: 20px;
    display: flex;
    flex-direction: row;
}

.form-container {
    flex-direction: column;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}

.field label{
    flex-direction: column;
    display: block;
    margin-bottom: 5px;
}

</style>
