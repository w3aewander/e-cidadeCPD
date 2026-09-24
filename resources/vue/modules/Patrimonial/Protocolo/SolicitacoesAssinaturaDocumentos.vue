<template>
  <div class="section-actions">
    <ProgressBar :value="percentualProgresso" :showValue="true"
                 style="width: 100%;height: 25px;margin: 10px"
                 v-if="exibirProgresso"
    >
      {{ parseInt(percentualProgresso) }}% Assinando {{
        documentosAssinados.length ?? 0
      }}/{{ selectedDocumentos.length ?? 0 }}
    </ProgressBar>

    <div style="display: flex;justify-content: center;align-items: center; width: 100%; margin-bottom: 10px">
       <span style="display: flex; align-items: center; font-size: 1.2rem;">
          <b v-if="!assinadorAtivo">Assinador A3 desconectado</b>
          <b v-if="assinadorAtivo">Assinador A3 conectado</b>
          <i class="pi pi-power-off"
             :style="{'font-size': '15px', 'color':assinadorAtivo ? 'green' : 'red', 'margin-left': '5px'}"
          ></i>
       </span>
    </div>

    <Button
        label="Assinar com E-cidade"
        rounded
        style="margin-right: 10px"
        @click="assinarEcidade()"
        :disabled="exibirProgresso"
    />

    <Button label="Assinar com A3" rounded @click="assinarA3()" :disabled="exibirProgresso"/>

    <div style="display: flex;justify-content: center;align-items: center;width: 100%;margin-top: 10px">
      <Dropdown :options="certificates"
                v-model="certificadoSelecionado"
                placeholder="Selecione um certificado"
                style="width: 250px"
                v-if="assinadorAtivo"
      />
    </div>
  </div>

  <SolicitacoesAssinaturaFilter
    :filter="{ ...filter, id: solicitacaoId }"
    :getSolicitacoes="getSolicitacoes"
    @onSearch="onSearch"
    @clearFilter="clearFilterAndReload"
  />

  <TabView @tab-change="filtrarArquivos">
    <TabPanel header="Não assinados"/>
    <TabPanel header="Assinados"/>
    <TabPanel header="Todos"/>
  </TabView>

  <div>
    <DataTable
        v-if="showTable"
        dataKey="id"
        :value="documentos"
        :loading="loadingDocumentos"
        v-model:selection="selectedDocumentos"
        @sort="sort"
    >
      <Column selectionMode="multiple" style="width: 3rem" :exportable="false" :disabledSelection="exibirProgresso"/>

      <Column field="id" header="ID" sortable>
          <template #body="{data}">
              <b style="font-size: 0.9rem;">{{ data.id }}</b>
          </template>
      </Column>

      <Column field="cgm_solicitante.z01_nome" header="Solicitante" sortable>
        <template #body="{data}">
          {{ data.cgm_solicitante.z01_nome }}
        </template>
      </Column>

      <Column field="cgm_solicitante.z01_numcgm" header="CGM" sortable>
        <template #body="{data}">
          {{ data.cgm_solicitante.z01_numcgm }}
        </template>
      </Column>

      <Column field="created_at" header="Data Solicitação" sortable>
        <template #body="{data}">
          {{ formatDateToBrazilian(data.created_at) }}
        </template>
      </Column>

      <Column field="data_assinatura" header="Data Assinatura" sortable v-if="filtro.assinado !== 'nao'">
        <template #body="{data}">
          {{ formatDateToBrazilian(data.data_assinatura) }}
        </template>
      </Column>

      <Column field="documento.descricao" header="Descrição" sortable></Column>

      <Column field="documento_id" header="Cod. Documento" sortable/>

      <Column field="documento.processo.numero" header="N° Processo/Ano" sortable>
        <template #body="{data}">
          {{ data.documento.processo.numero }}/{{ data.documento.processo.ano }}
        </template>
      </Column>

      <Column
        header="Ações"
        headerStyle="width: 5rem; text-align: center"
        bodyStyle="text-align: center; overflow: visible"
      >
        <template #body="{data}">
          <div style="display: flex; gap: 10px;">
            <Button type="button" title="Origem do documento" icon="pi pi-bars" rounded
                    @click="openOrigem(data.documento.processo.p58_codproc)"/>
            <Button
                type="button"
                title="Visualizar arquivo"
                icon="pi pi-file-pdf"
                rounded
                severity="danger"
                @click="visualizarArquivo(data.documento.documento_storage)"
            />
          </div>
        </template>
      </Column>
    </DataTable>

    <p
      v-if="!loadingDocumentos && documentos.length === 0"
      class="text-center"
      style="font-size: 1.2rem;"
    >
      Você não tem nenhuma solicitação de assinatura
    </p>
  </div>

  <Paginator
      v-if="paginate.total > 0"
      :rows="rowsPerPage"
      :totalRecords="paginate.total"
      :rowsPerPageOptions="[10, 20, 30, 50, 80, 100]"
      @page="onPageChange"
  >
    <template #start>
        {{ paginate.to }} de {{ paginate.total }}
    </template>
  </Paginator>
</template>

<script setup>
import {onMounted, ref, computed, watch, reactive} from 'vue';
import io from '../../../../../scripts/socket.io.js';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import ProgressBar from 'primevue/progressbar';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Dropdown from 'primevue/dropdown';
import {formatDateToBrazilian} from "../../../utils/Strings";
import SolicitacoesAssinaturaFilter from "@modules/Patrimonial/Protocolo/Components/SolicitacoesAssinaturaFilter.vue";
import Paginator from "primevue/paginator";
import Swal from "sweetalert2";

const props = defineProps({
    cpf_cnpj: String,
    solicitacao_id: {
        type: Number,
        default: null
    }
});

const documentos = ref([]);
const configuracao = ref({});
const selectedDocumentos = ref([]);
const documentosAssinados = ref([]);
const assinaturasComErros = ref([]);
const loadingDocumentos = ref(false);
const showTable = ref(true);
const exibirProgresso = ref(false);
const assinadorAtivo = ref(false);
const certificates = ref([]);
const certificadoSelecionado = ref(null);
const documentosAssinando = ref([]);
const sortOrder = ref(1);
const sortField = ref('');
const rowsPerPage = ref(10);
const timeout = ref(null);
const solicitacaoId = ref(props.solicitacao_id || null);

const filtro = ref({
    assinado: 'nao'
});

const filter = reactive({
    data_inicio: null,
    data_fim: null,
    id: null,
    solicitante: null,
    descricao: null,
    processo: null,
    cgm: null,
});

const paginate = reactive({
    rows: rowsPerPage.value,
    total: 0,
    to: 0
});

const urlEcidade = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;

const percentualProgresso = computed(() => {
  return (documentosAssinados.value.length ?? 0) / (selectedDocumentos.value.length ?? 0) * 100
});

const filtrarArquivos = (e) => {
  switch (e.index) {
    case 0:
      filtro.value.assinado = 'nao';
      break;
    case 1:
      filtro.value.assinado = 'sim';
      break;
    case 2:
      filtro.value.assinado = 'todos';
      break;
  }
  documentos.value = [];

  clearSort();
  clearFilter();

  getSolicitacoes();
}

const getConfiguracoes = async () => {
  const resp = await window.axios.post(`v4/api/assinador/obter-configuracao`, {});
  configuracao.value = resp.data.data;
}

const getSolicitacoes = async (options = {}) => {
    selectedDocumentos.value = [];
    loadingDocumentos.value = true;
    showTable.value = true;

    let { page, rows = rowsPerPage.value } = options;

    const { id, solicitante, cgm, data_inicio, data_fim, descricao, processo } = filter;
    const search = { id: solicitacaoId.value, solicitante, cgm, data_inicio, data_fim, descricao, processo };

    const params = new URLSearchParams({
        page: page ? ++page : 1,
        perPage: rows,
        search: JSON.stringify(search)
    });

    try {
        documentos.value = [];
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura/cpf-cnpj/${props.cpf_cnpj}?` +
            params.toString() +
            `&assinado=${filtro.value.assinado}&sortField=${sortField.value}&sortOrder=${sortOrder.value}`
        );
        const data = resp.data.data;
        documentos.value = data.data || [];

        if (documentos.value.length === 0) {
            showTable.value = false;
        }

        handlePaginate(data);
    } catch (e) {
        alert("Não foi possível carregar os documentos.");
        console.error(e);
    }

    loadingDocumentos.value = false;
}

const openOrigem = (codigoProcesso) => {
  js_OpenJanelaIframe(
      'CurrentWindow.corpo',
      'db_iframe_origem',
      urlEcidade + `pro3_consultaprocesso002.php?codproc=${codigoProcesso}`,
      'Origem documento',
      true
  );
}

const assinarEcidade = async () => {
    documentosAssinados.value = [];
    assinaturasComErros.value = [];

    if (selectedDocumentos.value.length < 1) {
        return alert("Selecione pelo menos um documento para assinar!");
    }

    exibirProgresso.value = true;

    for (const documento of selectedDocumentos.value) {
        const arquivoAssinado = await assinarDocumentoEcidade(documento);
        await salvarArquivoAssinado(arquivoAssinado);
    }

    exibirProgresso.value = false;
    getSolicitacoes();
}

const assinarDocumentoEcidade = async (documento) => {
  let parametrosAssinaturaECidade = {...configuracao.value};
  parametrosAssinaturaECidade.fileID = documento.documento.documento_storage;
  // parametrosAssinaturaECidade.qrcode_link = documento.value;
  parametrosAssinaturaECidade.qrcode_hash = documento.documento.documento_hash;
  parametrosAssinaturaECidade.sequencial = documento.documento.p01_sequencial;
  parametrosAssinaturaECidade.id_estorage = documento.documento.documento_storage;
  parametrosAssinaturaECidade.solicitacao_id = documento.id;

  const resp = await window.axios.post(`v4/api/assinador/assinar-ecidade`, parametrosAssinaturaECidade);
  const data = await resp.data;
  let arquivoAssinado = {...parametrosAssinaturaECidade};
  arquivoAssinado.base64 = data.data.base64_file;

  return arquivoAssinado;
}

const salvarArquivoAssinado = async (arquivoAssinado) => {
    let solicitacaoId = arquivoAssinado.solicitacao_id;

    try {
        await window.axios.post(
            `v4/api/patrimonial/protocolo/documentos/atualizar-documento-assinado`,
            arquivoAssinado
        );
    } catch (e) {
        if (e.response.data.message) {
            return assinaturasComErros.value.push(`(ID ${solicitacaoId}) ` + e.response.data.message);
        }

        return assinaturasComErros.value.push(
            `(ID ${solicitacaoId}) Não foi possível assinar o documento. Tente novamente mais tarde.`
        );
    }

    documentosAssinados.value.push(arquivoAssinado);
}

const obterBase64DoArquivo = async (codigoEstorage) => {
  const resp = await window.axios.get(
      `v4/api/assinador/obter-arquivo-base64/${codigoEstorage}`
  );
  return resp.data.data;
}

const visualizarArquivo = (storage_id) => {
  window.open(
      urlEcidade + `db_visualizar_estorage.php?id=${storage_id}`,
      null,
      'left=100,top=100,width=500,height=500;'
  );
}

const assinarA3 = async () => {
  documentosAssinados.value = [];
  assinaturasComErros.value = [];

  if (selectedDocumentos.value.length < 1) {
    alert("Selecione pelo menos um documento para assinar!");
    return;
  }

  if (empty(certificadoSelecionado.value)) {
    alert("Selecione o certificado");
    return;
  }

  exibirProgresso.value = true;

  for (const documento of selectedDocumentos.value) {
    await assinarDocumentoA3(documento);
  }
}

const assinarDocumentoA3 = async (documento) => {
  const codigoEstorage = documento.documento.documento_storage;
  let parametrosAssinatura = {...configuracao.value};
  parametrosAssinatura.fileID = codigoEstorage;
  parametrosAssinatura.fileB64 = "";
  parametrosAssinatura.fileCertificate = certificadoSelecionado.value;
  parametrosAssinatura.isCertBase64 = false;
  //parametrosAssinatura.qrcode_link = inputQrcodeLink.value;
  const data = await obterBase64DoArquivo(codigoEstorage);
  parametrosAssinatura.fileB64 = data.content_estorage;
  parametrosAssinatura.id_estorage = codigoEstorage;
  parametrosAssinatura.sequencial = documento.documento.p01_sequencial;
  parametrosAssinatura.qrcode_hash = documento.documento.documento_hash;
  documentosAssinando.value.push(parametrosAssinatura);
  atualizaTimeout();
  socket.emit('sign', parametrosAssinatura);
}

const onSearch = (localFilter) => {
    Object.assign(filter, localFilter);
    getSolicitacoes();
};

const limpar = () => {
  exibirProgresso.value = false;
  certificadoSelecionado.value = null;
  timeout.value = null;
  getSolicitacoes();
}

const atualizaTimeout = () => {
  clearTimeout(timeout.value);
  let timeoutTime = (selectedDocumentos.value.length - documentosAssinados.value.length) * 20000;
  if (timeoutTime < 0) {
    timeout.value = setTimeout(() => {
      alert("Tempo de assinatura expirado!");
      limpar();
    }, timeoutTime);
  }
}

const sort = (event) => {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;

    getSolicitacoes();
}

const clearFilterAndReload = () => {
    clearFilter();
    getSolicitacoes();
}

const clearFilter = () => {
    filter.id = null;
    filter.solicitante = null;
    filter.cgm = null;
    filter.data_inicio = null;
    filter.data_fim = null;
    filter.descricao = null;
    filter.processo = null;
    solicitacaoId.value = null;
}

const clearSort = () => {
    sortField.value = '';
    sortOrder.value = 1;
}

const handlePaginate = (data) => {
    paginate.rows = rowsPerPage.value || 10;
    paginate.total = data.total || 0;
    paginate.current_page = data.current_page || 1;
    paginate.to = data.to || 0;
}

const onPageChange = (event) => {
    rowsPerPage.value = event.rows;
    getSolicitacoes({ page: event.page, rows: rowsPerPage.value });
}

const socket = io('http://localhost:9000');

socket.on('connect', () => {
  socket.emit('getCertificates', {});
  assinadorAtivo.value = true;
});

socket.on('connect_error', () => {
  assinadorAtivo.value = false;
});

socket.on('disconnect', () => {
  assinadorAtivo.value = false;
});

socket.on('certificates', (event) => {
  certificates.value = event.certList;
});

socket.on('signed', async (event) => {
  let fileB64 = event.fileB64;
  let arquivoAssinado = documentosAssinando.value.filter(file => {
    return parseInt(event.fileID) === parseInt(file.id_estorage);
  }).shift();
  arquivoAssinado.base64 = fileB64;
  await salvarArquivoAssinado(arquivoAssinado);
  documentosAssinados.value.push(arquivoAssinado);
  atualizaTimeout();
});

watch(() => ({
        totalAssinados: documentosAssinados.value.length,
        totalErros: assinaturasComErros.value.length,
    }),
    ({ totalAssinados, totalErros }) => {
        const totalProcessados = totalAssinados + totalErros;
        const totalSelecionados = selectedDocumentos.value.length;

        if (totalProcessados === totalSelecionados) {
            if (totalErros === 0) {
                Swal.fire({
                    text: `Documentos assinados com sucesso!`,
                    icon: "success",
                    confirmButtonColor: "#4a789c",
                });
            } else {
                const errosString = assinaturasComErros.value.join("<br>");
                Swal.fire({
                    html: "Não foi possível assinar os seguintes documentos: <br><br>" + errosString,
                    icon: "error",
                    confirmButtonColor: "#4a789c",
                    width: "fit-content"
                });
            }

            selectedDocumentos.value = [];
            limpar();
        }
    },
    { deep: true }
);

onMounted(() => {
  getConfiguracoes();
  getSolicitacoes();
});
</script>

<style scoped>
.section-actions {
  width: 100%;
  margin: 20px 0;
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
}

:deep(.p-tabview-nav-link.p-tabview-header-action) {
    margin: 0 !important;
}
</style>
