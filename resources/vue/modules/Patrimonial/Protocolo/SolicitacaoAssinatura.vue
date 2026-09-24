<template>
  <div>
    <ModalLoading :is-loading="loading"></ModalLoading>
    <DialogCGM
        ref="dialogCGM"
        :carregar-dados-automatico="false"
        @select="selecaoDoCgmPeloDialogCGM"
    />

    <div class="btn-enviar" v-if="cgmSelected.length > 0 && documentSelected.length > 0"
         @click="solicitarAssinaturas"
    >
      Enviar
    </div>

    <div class="section-card-cgm-selecionado">

      <div class="btn-add-selecionados" @click="openDialogCGM">
        +
      </div>

      <h3 v-if="cgmSelected.length <1" style="color: #9d9d9d;">Destinatários</h3>
      <div class="card-cgm-selecionado" v-for="cgm of cgmSelected">
        <b>CGM :</b> {{ cgm.cgm }}
        <b>Nome :</b> {{ cgm.nome }}
        <b>CPF/CNPJ :</b>{{ cgm.cpf_cnpj }}
        <div class="remove-cgm" @click="removerCgmSelecionado(cgm)">X</div>
      </div>
    </div>

    <h1 style="text-align:center;color:#326094;">Documentos</h1>
    <div class="section-card-documentos">
      <div v-if="loadingDocumentos" style="display:flex;justify-content: center;flex-direction:column">
        <ProgressSpinner/>
        <h3>Carregando documentos...</h3>
      </div>

      <div v-for="documento of documentos"
           class="card-documentos"
           :class="{'active-card': documentoEstaSelecionado(documento)}"
      >
        <h2 @click="selecionarDocumento(documento)"
        >
          {{ documento.p01_nomedocumento }}
        </h2>
        <hr>
        <h2 style="color:#326094;text-align:center">Assinantes</h2>
        <div class="selection-card-selecionados" v-if="documento.solicitacao_assinatura.length > 0">
          <div class="card-selecionados" v-for="solicitacao of documento.solicitacao_assinatura">
            <b>CGM: </b> {{ solicitacao.cgm_assinante.z01_numcgm }} <br>
            <b>Nome:</b> {{ solicitacao.cgm_assinante.z01_nome }}<br>
            <b>CPF/CNPJ:</b>{{ solicitacao.cgm_assinante.z01_cgccpf }}<br>
            <b>Assinado:</b>{{ solicitacao.data_assinatura ? 'Sim' : 'Não' }}<br>
            <b>Data Solicitação :</b>{{ solicitacao.created_at }}<br>
            <i v-if="!solicitacao.data_assinatura"
               class="remove-solicitante"
               @click="cancelarSolicitarAssinatura(solicitacao.id)"
            >
              X
            </i>
          </div>
        </div>
        <p v-if="documento.solicitacao_assinatura.length < 1" style="color:#326094;text-align:center">
          Não possuí assinantes!
        </p>
      </div>
    </div>

  </div>
</template>

<script setup>
import {onMounted, ref} from 'vue';
import DialogCGM from "./Components/DialogProtocoloDocumentoCGM";
import ModalLoading from '../../Components/ModalLoading';

const props = defineProps(['codigoProcesso', 'codigoDespacho']);
const cgmSelected = ref([]);
const documentos = ref([]);
const dialogCGM = ref(null);
const documentSelected = ref([]);
const loadingDocumentos = ref(false);
const loading = ref(false);

function CgmAssinatura() {
  this.cgm;
  this.nome;
  this.cpf_cnpj;
  this.assinado = false;
}

const openDialogCGM = () => {
  dialogCGM.value.openDialog();
}

const selecaoDoCgmPeloDialogCGM = (cgm) => {
  let cgmAssinatura = new CgmAssinatura();
  cgmAssinatura.cgm = cgm.z01_numcgm;
  cgmAssinatura.nome = cgm.z01_nome;
  cgmAssinatura.cpf_cnpj = cgm.z01_cgccpf;
  cgmSelected.value.push(cgmAssinatura);
}

const limpar = () => {
  documentSelected.value = [];
  cgmSelected.value = [];
  documentos.value = []
}
const getDocumentos = async () => {

  loadingDocumentos.value = true;
  try {

    let form = {
      codigoProcesso: props.codigoProcesso,
      codigoDespacho: props.codigoDespacho
    }

    let params = new URLSearchParams(form);

    const resp = await axios.get(
        `v4/api/patrimonial/protocolo/solicitacao-assinatura?` + params.toString(),
    );

    documentos.value = resp.data.data.filter((documento) => {
      let re = /(?:\.([^.]+))?$/;
      let extensao = re.exec(documento.p01_nomedocumento)[1];
      return !extensao || extensao.includes('pdf', 'PDF');
    });
    loadingDocumentos.value = false;
  } catch (e) {
    loadingDocumentos.value = false;
  }
}

const documentoEstaSelecionado = (documento) => {
  return documentSelected.value.find(el => {
    return el.p01_sequencial === documento.p01_sequencial
  })
}

const removerDocumentoSelecionado = (documento) => {
  documentSelected.value = documentSelected.value.filter(el => {
    return el.p01_sequencial != documento.p01_sequencial
  });

  console.log('remove =>', documentSelected.value);
}

const removerCgmSelecionado = (cgm) => {
  cgmSelected.value = cgmSelected.value.filter(el => {
    return el.cgm != cgm.cgm
  });
}
const selecionarDocumento = (documento) => {
  if (!documentoEstaSelecionado(documento)) {
    documentSelected.value.push(documento);
  } else {
    removerDocumentoSelecionado(documento);
  }
}


const solicitarAssinaturas = async () => {

  if (cgmSelected.value.length < 1 || documentSelected.value.length < 1) {
    alert("Para solicitar assinatura é necessário selecionar no mínimo um CGM e um Documento!");
    return;
  }

  let form = {};
  form.documentos = [];
  documentSelected.value.forEach(documento => {
    cgmSelected.value.forEach(cgm => {
      form.documentos.push({
        documento_id: documento.p01_sequencial,
        cgm_assinante: cgm.cgm
      })
    })
  })

  loading.value = true;
  try {
    const resp = await axios.post(
        "v4/api/patrimonial/protocolo/solicitacao-assinatura",
        form
    );

    let data = resp.data;
    if (data.data.error) {
      alert(data.data.message);
      return;
    }

    loading.value = false;
    alert("Solicitação feita com sucesso!");
    limpar();
    await getDocumentos();

  } catch (e) {
    loading.value = false;
    if (e.response) {
      alert(e.response.data.message);
      return;
    }
    alert("Ocorreu um erro!");
  }

}

const cancelarSolicitarAssinatura = async (solicitacao_id) => {

  loading.value = true;
  try {
    const resp = await axios.delete(
        `v4/api/patrimonial/protocolo/solicitacao-assinatura/${solicitacao_id}`
    );

    let data = resp.data;
    if (data.data.error) {
      alert(data.data.message);
      return;
    }

    loading.value = false;
    alert("Cancelamento da solicitação efetuado com sucesso!");
    limpar();
    await getDocumentos();

  } catch (e) {
    loading.value = false;
    if (e.response) {
      alert(e.response.data.message);
      return;
    }
    alert("Ocorreu um erro!");
  }

}

onMounted(() => {
  getDocumentos();
})
</script>

<style scoped>
.card-documentos {
  background: #ccc;
  width: 90%;
  border-radius: 10px;
  padding: 10px;
  margin: 2px;
}

.card-documentos h2 {
  text-align: center;
}

.selection-card-selecionados {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

.section-card-documentos {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
}

.card-selecionados {
  margin: 5px;
  background: #fff;
  border-radius: 5px;
  padding: 10px;
  box-shadow: 2px 2px 5px #000;
  position: relative;
}

.active-card {
  background: #4a789b;
}

.btn-add-selecionados {
  width: 30px;
  height: 30px;
  background: #326094;
  color: white;
  border-radius: 50px;
  position: absolute;
  right: 1px;
  text-align: center;
  justify-content: center;
  display: flex;
  align-items: center;
  box-shadow: 2px 2px 10px #000;
}

.card-cgm-selecionado {
  width: 300px;
  background: #ffff;
  border-radius: 10px;
  padding: 5px;
  box-shadow: 2px 2px 5px #000;
  position: relative;
  margin: 5px;
}

.section-card-cgm-selecionado {
  margin: 50px;
  min-height: 30px;
  box-shadow: 1px 1px 10px #898181cc;
  background: #e1dede;
  border-radius: 5px;
  position: relative;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}

.remove-solicitante, .remove-cgm {
  position: absolute;
  right: 1px;
  border: 1px solid #ccc;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #ccc;
  top: 2px;
}

.btn-enviar {
  position: fixed;
  bottom: 10px;
  right: 20px;
  background: #1d4b1d;
  padding: 10px;
  color: white;
  width: 90px;
  text-align: center;
  animation: pulse 2s infinite;
  border-radius: 5px;
}

@keyframes pulse {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.7);
  }

  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(0, 0, 0, 0);
  }

  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
  }
}
</style>
