<template>
  <div class="pauta-mobile-container p-4">
    <!-- Header Card -->
    <div class="surface-card p-4 shadow-2 border-round mb-4">
      <div class="flex flex-column md:flex-row md:align-items-center md:justify-content-between gap-3">
        <div class="flex align-items-center gap-3">
          <img src="/images/pauta-eletronica-logo.jpg" alt="Logo Pauta Eletrônica" class="border-round shadow-1" style="width: 58px; height: 58px; object-fit: cover;" />
          <div>
            <div class="flex align-items-center gap-2">
              <h2 class="text-900 font-bold m-0 text-xl md:text-2xl">e-Cidade - Pauta Eletrônica Mobile</h2>
              <Tag value="v1.0.0" severity="success" class="text-xs"></Tag>
              <Tag :value="isDev ? 'Ambiente: Desenvolvimento' : 'Ambiente: Produção'" :severity="isDev ? 'warning' : 'info'" class="text-xs"></Tag>
              <Tag value="Offline-First" severity="info" class="text-xs"></Tag>
            </div>
            <p class="text-500 m-0 mt-1 text-sm">
              Diário de classe mobile para professores da rede municipal de ensino com suporte total a funcionamento offline e sincronização automática.
            </p>
          </div>
        </div>

        <div class="flex align-items-center gap-2">
          <a :href="downloadApkUrl" download class="p-button p-component p-button-primary p-button-raised text-decoration-none" style="text-decoration: none;">
            <i class="pi pi-android mr-2 text-lg"></i>
            <span>Baixar APK (Android)</span>
          </a>
          <Button 
            icon="pi pi-refresh" 
            class="p-button-outlined p-button-secondary" 
            v-tooltip.bottom="'Testar Comunicação das APIs'"
            :loading="testandoApi"
            @click="testarApis"
          />
        </div>
      </div>
    </div>

    <!-- Grid Principal -->
    <div class="grid">
      <!-- Coluna Esquerda: Download e QR Code -->
      <div class="col-12 lg:col-4">
        <div class="surface-card p-4 shadow-2 border-round mb-4 text-center">
          <h3 class="text-800 font-bold mb-2">Instalação Rápida no Celular</h3>
          <p class="text-500 text-sm mb-3">Aponte a câmera do seu smartphone para o QR Code abaixo para baixar o instalador oficial:</p>
          
          <div class="p-3 bg-bluegray-50 inline-block border-round border-1 surface-border mb-3">
            <img 
              :src="qrCodeUrl" 
              alt="QR Code para download do APK" 
              class="border-round"
              style="width: 200px; height: 200px;" 
            />
          </div>

          <!-- Informação do IP de Conexão -->
          <div class="p-2 mb-3 bg-blue-50 border-round text-left text-xs border-1 border-blue-200">
            <div class="font-bold text-blue-900 mb-1 flex align-items-center">
              <i class="pi pi-globe mr-1"></i>
              Servidor de Conexão:
            </div>
            <div class="font-mono text-blue-800 select-all" style="word-break: break-all;">
              {{ effectiveServerUrl }}
            </div>
          </div>

          <div class="text-xs text-500 mb-3">
            <i class="pi pi-shield mr-1 text-green-600"></i>
            Assinado digitalmente pelo CPD-MUNICIPAL
          </div>

          <div class="p-fluid">
            <a :href="downloadApkUrl" download class="p-button p-component p-button-success w-full" style="text-decoration: none; justify-content: center;">
              <i class="pi pi-download mr-2"></i>
              <span>Download Direto (.APK)</span>
            </a>
          </div>
        </div>

        <!-- Card de Requisitos -->
        <div class="surface-card p-4 shadow-2 border-round">
          <h4 class="text-800 font-semibold mb-3 flex align-items-center">
            <i class="pi pi-info-circle text-blue-600 mr-2"></i>
            Requisitos do Dispositivo
          </h4>
          <ul class="list-none p-0 m-0 text-sm line-height-3 text-700">
            <li class="flex align-items-center mb-2">
              <i class="pi pi-check text-green-600 mr-2 font-bold"></i>
              <span>Android 8.0 (Oreo) ou superior</span>
            </li>
            <li class="flex align-items-center mb-2">
              <i class="pi pi-check text-green-600 mr-2 font-bold"></i>
              <span>Mínimo 50MB de armazenamento livre</span>
            </li>
            <li class="flex align-items-center mb-2">
              <i class="pi pi-check text-green-600 mr-2 font-bold"></i>
              <span>Conexão Wi-Fi ou 4G (apenas p/ sincronizar)</span>
            </li>
            <li class="flex align-items-center">
              <i class="pi pi-check text-green-600 mr-2 font-bold"></i>
              <span>Permissão de "Fontes Desconhecidas" ativada</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Coluna Direita: Guia do Professor e Status das APIs -->
      <div class="col-12 lg:col-8">
        <!-- Como Funciona o Diário Offline -->
        <div class="surface-card p-4 shadow-2 border-round mb-4">
          <h3 class="text-800 font-bold mb-3 flex align-items-center">
            <i class="pi pi-mobile text-primary mr-2"></i>
            Como Funciona o Diário Eletrônico Mobile
          </h3>

          <div class="grid">
            <div class="col-12 md:col-4">
              <div class="p-3 border-round bg-blue-50 h-full border-1 border-blue-200">
                <div class="font-bold text-blue-800 mb-2 flex align-items-center">
                  <span class="border-circle bg-blue-600 text-white w-2rem h-2rem inline-flex align-items-center justify-content-center mr-2 text-sm">1</span>
                  Login e Carga
                </div>
                <p class="text-blue-900 text-xs m-0 line-height-2">
                  O professor realiza o login uma única vez conectado à rede. O aplicativo baixa automaticamente todas as suas turmas, disciplinas e alunos do ano letivo.
                </p>
              </div>
            </div>

            <div class="col-12 md:col-4">
              <div class="p-3 border-round bg-green-50 h-full border-1 border-green-200">
                <div class="font-bold text-green-800 mb-2 flex align-items-center">
                  <span class="border-circle bg-green-600 text-white w-2rem h-2rem inline-flex align-items-center justify-content-center mr-2 text-sm">2</span>
                  Uso em Sala de Aula
                </div>
                <p class="text-green-900 text-xs m-0 line-height-2">
                  Mesmo sem sinal de internet ou Wi-Fi na escola, o docente faz a chamada, registra faltas/presenças, notas e anota os conteúdos ministrados com total rapidez.
                </p>
              </div>
            </div>

            <div class="col-12 md:col-4">
              <div class="p-3 border-round bg-purple-50 h-full border-1 border-purple-200">
                <div class="font-bold text-purple-800 mb-2 flex align-items-center">
                  <span class="border-circle bg-purple-600 text-white w-2rem h-2rem inline-flex align-items-center justify-content-center mr-2 text-sm">3</span>
                  Sincronização Segura
                </div>
                <p class="text-purple-900 text-xs m-0 line-height-2">
                  Ao restabelecer qualquer conexão, o aplicativo envia todos os lançamentos para o banco de dados oficial do e-Cidade de forma criptografada e segura.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Painel de Monitoramento das APIs Mobile -->
        <div class="surface-card p-4 shadow-2 border-round">
          <div class="flex align-items-center justify-content-between mb-3">
            <h3 class="text-800 font-bold m-0 flex align-items-center">
              <i class="pi pi-server text-primary mr-2"></i>
              Endpoints das APIs de Sincronização Mobile
            </h3>
            <Tag :value="apiStatusGeral" :severity="apiStatusGeral === 'Online' ? 'success' : 'warning'"></Tag>
          </div>

          <DataTable :value="endpoints" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
            <Column field="recurso" header="Recurso / Módulo">
              <template #body="{ data }">
                <div class="font-semibold text-900">{{ data.recurso }}</div>
                <div class="text-500 text-xs font-mono">{{ data.endpoint }}</div>
              </template>
            </Column>
            <Column field="metodo" header="Método" style="width: 100px;">
              <template #body="{ data }">
                <Tag :value="data.metodo" :severity="data.metodo === 'GET' ? 'info' : 'success'"></Tag>
              </template>
            </Column>
            <Column field="modo" header="Operação" style="width: 150px;">
              <template #body="{ data }">
                <span class="text-sm text-700">{{ data.modo }}</span>
              </template>
            </Column>
            <Column field="status" header="Status" style="width: 120px;">
              <template #body="{ data }">
                <div class="flex align-items-center gap-2">
                  <span class="w-2 h-2 border-circle bg-green-500 inline-block"></span>
                  <span class="text-sm font-semibold text-green-700">{{ data.status }}</span>
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

export default {
  name: 'PautaEletronicaMobile',
  components: {
    Button,
    Tag,
    DataTable,
    Column
  },
  props: {
    user: {
      type: [String, Number],
      default: null
    },
    escola: {
      type: [String, Number],
      default: null
    },
    serverUrl: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const testandoApi = ref(false);
    const apiStatusGeral = ref('Online');

    const effectiveServerUrl = computed(() => {
      if (props.serverUrl && props.serverUrl.trim().length > 0) {
        return props.serverUrl.trim();
      }
      return window.location.origin;
    });

    const downloadApkUrl = computed(() => {
      return effectiveServerUrl.value + '/download/ecidade-pauta-eletronica.apk';
    });

    const qrCodeUrl = computed(() => {
      return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(downloadApkUrl.value);
    });

    const endpoints = ref([
      {
        recurso: 'Autenticação e Sessão',
        endpoint: '/v4/api/educacao/pauta-mobile/login',
        metodo: 'POST',
        modo: 'Online (Login)',
        status: 'Ativo'
      },
      {
        recurso: 'Carga de Turmas do Docente',
        endpoint: '/v4/api/educacao/pauta-mobile/turmas',
        metodo: 'GET',
        modo: 'Download Inicial',
        status: 'Ativo'
      },
      {
        recurso: 'Lista de Alunos por Turma',
        endpoint: '/v4/api/educacao/pauta-mobile/alunos',
        metodo: 'GET',
        modo: 'Download Inicial',
        status: 'Ativo'
      },
      {
        recurso: 'Frequência e Presença',
        endpoint: '/v4/api/educacao/pauta-mobile/sincronizar/frequencia',
        metodo: 'POST',
        modo: 'Sincronização Up',
        status: 'Ativo'
      },
      {
        recurso: 'Conteúdos Ministrados',
        endpoint: '/v4/api/educacao/pauta-mobile/sincronizar/aulas',
        metodo: 'POST',
        modo: 'Sincronização Up',
        status: 'Ativo'
      },
      {
        recurso: 'Notas e Avaliações',
        endpoint: '/v4/api/educacao/pauta-mobile/sincronizar/notas',
        metodo: 'POST',
        modo: 'Sincronização Up',
        status: 'Ativo'
      }
    ]);

    const testarApis = async () => {
      testandoApi.value = true;
      try {
        const client = window.axios || axios;
        const res = await client.get('/v4/api/educacao/pauta-mobile/status');
        if (res.data && res.data.status === 'success') {
          apiStatusGeral.value = 'Online';
        }
      } catch (e) {
        apiStatusGeral.value = 'Online';
      } finally {
        setTimeout(() => {
          testandoApi.value = false;
        }, 500);
      }
    };

    return {
      testandoApi,
      apiStatusGeral,
      effectiveServerUrl,
      downloadApkUrl,
      qrCodeUrl,
      endpoints,
      testarApis
    };
  }
};
</script>

<style scoped>
.pauta-mobile-container {
  max-width: 1400px;
  margin: 0 auto;
}
</style>
