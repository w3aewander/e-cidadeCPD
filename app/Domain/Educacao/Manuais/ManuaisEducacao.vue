<template>
  <div class="manuais-container p-4">
    <!-- Header -->
    <div class="surface-card p-4 shadow-2 border-round mb-4">
      <div class="flex flex-column md:flex-row md:align-items-center md:justify-content-between gap-3">
        <div class="flex align-items-center gap-3">
          <div class="flex align-items-center justify-content-center bg-blue-100 border-round" style="width: 48px; height: 48px">
            <i class="pi pi-book text-blue-600 text-2xl"></i>
          </div>
          <div>
            <h2 class="text-900 font-bold m-0 text-xl md:text-2xl">Manuais da Educação</h2>
            <p class="text-500 m-0 mt-1 text-sm">Consulte e baixe os manuais, guias e documentos oficiais da Educação.</p>
          </div>
        </div>

        <div class="flex align-items-center gap-2">
          <Button 
            v-if="isAdmin" 
            label="Novo Manual" 
            icon="pi pi-plus" 
            class="p-button-primary"
            @click="abrirModalCadastro"
          />
          <Button 
            icon="pi pi-refresh" 
            class="p-button-outlined p-button-secondary" 
            v-tooltip.bottom="'Atualizar Lista'"
            :loading="carregando"
            @click="buscarManuais"
          />
        </div>
      </div>
    </div>

    <!-- Barra de Busca e Filtros -->
    <div class="surface-card p-3 shadow-1 border-round mb-4">
      <div class="flex flex-column md:flex-row align-items-center justify-content-between gap-3">
        <div class="p-input-icon-left w-full md:w-20rem">
          <i class="pi pi-search"></i>
          <InputText 
            v-model="filtroBusca" 
            placeholder="Pesquisar por título ou descrição..." 
            class="w-full"
          />
        </div>

        <div class="text-500 text-sm">
          Exibindo <strong>{{ manuaisFiltrados.length }}</strong> de <strong>{{ manuais.length }}</strong> manuais
        </div>
      </div>
    </div>

    <!-- Lista de Manuais (DataTable) -->
    <div class="surface-card p-4 shadow-2 border-round">
      <DataTable 
        :value="manuaisFiltrados" 
        :loading="carregando"
        responsiveLayout="scroll"
        paginator 
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50]"
        emptyMessage="Nenhum manual encontrado."
        class="p-datatable-sm"
      >
        <Column field="titulo" header="Documento" sortable style="min-width: 250px">
          <template #body="slotProps">
            <div class="flex align-items-center gap-2">
              <i class="pi pi-file-pdf text-red-500 text-2xl"></i>
              <div>
                <span class="font-bold text-900 block">{{ slotProps.data.titulo }}</span>
                <span v-if="slotProps.data.descricao" class="text-500 text-xs block">{{ slotProps.data.descricao }}</span>
                <span class="text-400 text-xs"><i class="pi pi-file mr-1"></i>{{ slotProps.data.nome_arquivo_original }}</span>
              </div>
            </div>
          </template>
        </Column>

        <Column field="tamanho_formatado" header="Tamanho" sortable style="width: 120px">
          <template #body="slotProps">
            <Tag :value="slotProps.data.tamanho_formatado" severity="info" rounded></Tag>
          </template>
        </Column>

        <Column field="criado_em" header="Data de Envio" sortable style="width: 160px">
          <template #body="slotProps">
            <span class="text-700 text-sm">{{ slotProps.data.criado_em || '-' }}</span>
          </template>
        </Column>

        <Column field="usuario_nome" header="Enviado por" sortable style="width: 180px">
          <template #body="slotProps">
            <span class="text-700 text-sm">{{ slotProps.data.usuario_nome || 'Sistema' }}</span>
          </template>
        </Column>

        <Column header="Ações" style="width: 180px; text-align: right">
          <template #body="slotProps">
            <div class="flex justify-content-end gap-1">
              <Button 
                icon="pi pi-eye" 
                class="p-button-rounded p-button-text p-button-info" 
                v-tooltip.top="'Visualizar PDF'"
                @click="visualizarManual(slotProps.data)"
              />
              <Button 
                icon="pi pi-download" 
                class="p-button-rounded p-button-text p-button-success" 
                v-tooltip.top="'Baixar Arquivo'"
                @click="baixarManual(slotProps.data)"
              />
              <Button 
                v-if="isAdmin"
                icon="pi pi-trash" 
                class="p-button-rounded p-button-text p-button-danger" 
                v-tooltip.top="'Excluir Manual'"
                @click="confirmarExclusao(slotProps.data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Dialog de Cadastro / Upload (Apenas Admin) -->
    <Dialog 
      v-model:visible="modalCadastroAberto" 
      header="Enviar Novo Manual (PDF)" 
      :modal="true" 
      :style="{ width: '550px' }"
      class="p-fluid"
      :closable="!enviando"
    >
      <div class="p-fluid flex flex-column gap-3 mt-2">
        <div class="field">
          <label for="titulo" class="font-bold">Título do Documento <span class="text-red-500">*</span></label>
          <InputText 
            id="titulo" 
            v-model="formulario.titulo" 
            placeholder="Ex: Manual de Matrícula 2026" 
            :disabled="enviando"
            maxlength="255"
          />
          <small v-if="erros.titulo" class="p-error">{{ erros.titulo }}</small>
        </div>

        <div class="field">
          <label for="descricao" class="font-bold">Descrição / Observações (Opcional)</label>
          <Textarea 
            id="descricao" 
            v-model="formulario.descricao" 
            rows="3" 
            placeholder="Breve resumo ou instruções sobre o manual..." 
            :disabled="enviando"
            maxlength="1000"
          />
        </div>

        <div class="field">
          <label class="font-bold">Arquivo PDF <span class="text-red-500">*</span></label>
          
          <div 
            class="upload-dropzone border-2 border-dashed surface-border border-round p-4 text-center cursor-pointer transition-colors transition-duration-150"
            :class="{ 'surface-hover': isDragOver, 'border-red-400': erros.arquivo, 'border-primary': arquivoSelecionado }"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
            @drop.prevent="onDropArquivo"
            @click="acionarInputArquivo"
          >
            <input 
              type="file" 
              ref="fileInputRef" 
              class="hidden" 
              accept="application/pdf,.pdf" 
              @change="onArquivoSelecionado"
            />

            <div v-if="!arquivoSelecionado" class="flex flex-column align-items-center gap-2">
              <i class="pi pi-cloud-upload text-4xl text-primary"></i>
              <div class="font-semibold text-900">Clique para selecionar ou arraste o arquivo PDF aqui</div>
              <div class="text-500 text-xs">Apenas arquivos <strong>.pdf</strong> (Tamanho máximo: 200MB)</div>
            </div>

            <div v-else class="flex align-items-center justify-content-between p-2 bg-blue-50 border-round">
              <div class="flex align-items-center gap-2">
                <i class="pi pi-file-pdf text-red-500 text-3xl"></i>
                <div class="text-left">
                  <span class="font-bold text-900 text-sm block">{{ arquivoSelecionado.name }}</span>
                  <span class="text-500 text-xs">{{ formatarBytes(arquivoSelecionado.size) }}</span>
                </div>
              </div>
              <Button 
                icon="pi pi-times" 
                class="p-button-rounded p-button-text p-button-danger" 
                v-tooltip.top="'Remover arquivo'"
                @click.stop="removerArquivo"
                :disabled="enviando"
              />
            </div>
          </div>
          <small v-if="erros.arquivo" class="p-error block mt-1">{{ erros.arquivo }}</small>
        </div>

        <div v-if="enviando" class="field mt-2">
          <div class="flex justify-content-between text-sm mb-1">
            <span>Enviando arquivo...</span>
            <span>{{ progressoUpload }}%</span>
          </div>
          <ProgressBar :value="progressoUpload"></ProgressBar>
        </div>
      </div>

      <template #footer>
        <Button 
          label="Cancelar" 
          icon="pi pi-times" 
          class="btn-action-cancelar" 
          @click="fecharModalCadastro"
          :disabled="enviando"
        />
        <Button 
          label="Enviar Manual" 
          icon="pi pi-check" 
          class="btn-action-salvar" 
          :loading="enviando"
          @click="salvarManual"
        />
      </template>
    </Dialog>

    <!-- Dialog de Visualização do PDF -->
    <Dialog 
      v-model:visible="modalVisualizacaoAberto" 
      :header="manualSelecionado ? manualSelecionado.titulo : 'Visualização do Manual'" 
      :modal="true" 
      :style="{ width: '85vw', height: '90vh' }"
      :maximizable="true"
      @hide="fecharModalVisualizacao"
    >
      <div v-if="urlVisualizacao" class="w-full h-full" style="min-height: 70vh;">
        <iframe 
          :src="urlVisualizacao" 
          class="w-full h-full border-none border-round" 
          style="min-height: 70vh; width: 100%;"
        ></iframe>
      </div>
      <template #footer>
        <Button 
          label="Baixar PDF" 
          icon="pi pi-download" 
          class="btn-action-baixar" 
          @click="baixarManual(manualSelecionado)"
        />
        <Button 
          label="Fechar" 
          icon="pi pi-times" 
          class="btn-action-fechar" 
          @click="fecharModalVisualizacao"
        />
      </template>
    </Dialog>

    <!-- Diálogo de Confirmação de Exclusão -->
    <Dialog 
      v-model:visible="modalExclusaoAberto" 
      header="Confirmar Exclusão" 
      :modal="true" 
      :style="{ width: '420px' }"
    >
      <div class="flex align-items-center gap-3">
        <i class="pi pi-exclamation-triangle text-red-500 text-3xl"></i>
        <span v-if="manualExcluir">
          Tem certeza de que deseja excluir o manual <strong>{{ manualExcluir.titulo }}</strong>?
        </span>
      </div>
      <template #footer>
        <Button 
          label="Não" 
          icon="pi pi-times" 
          class="btn-action-cancelar" 
          @click="modalExclusaoAberto = false"
          :disabled="excluindo"
        />
        <Button 
          label="Sim, Excluir" 
          icon="pi pi-trash" 
          class="btn-action-excluir" 
          :loading="excluindo"
          @click="executarExclusao"
        />
      </template>
    </Dialog>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';

export default defineComponent({
  name: 'ManuaisEducacao',
  props: {
    isAdmin: {
      type: Boolean,
      default: false
    },
    usuarioId: {
      type: Number,
      default: 0
    },
    usuarioNome: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const toast = useToast();
    const manuais = ref([]);
    const carregando = ref(false);
    const enviando = ref(false);
    const excluindo = ref(false);
    const progressoUpload = ref(0);
    const filtroBusca = ref('');
    const isDragOver = ref(false);
    const fileInputRef = ref(null);

    const modalCadastroAberto = ref(false);
    const modalVisualizacaoAberto = ref(false);
    const modalExclusaoAberto = ref(false);

    const manualSelecionado = ref(null);
    const manualExcluir = ref(null);
    const urlVisualizacao = ref('');
    const arquivoSelecionado = ref(null);

    const formulario = ref({
      titulo: '',
      descricao: ''
    });

    const erros = ref({
      titulo: '',
      arquivo: ''
    });

    const getBaseUrl = () => {
      const matchWindow = window.location.pathname.match(/\/w\/\d+\//);
      if (matchWindow) {
        return matchWindow[0];
      }
      const matchWindowHref = window.location.href.match(/\/w\/\d+\//);
      if (matchWindowHref) {
        return matchWindowHref[0];
      }
      let meta = document.querySelector('meta[name="ECIDADE_REQUEST_PATH"]');
      let base = meta ? meta.getAttribute('content') : (window.ECIDADE_PATH || '');
      if (base && base.trim() !== '') {
        if (!base.endsWith('/')) base += '/';
        return base;
      }
      let path = window.location.pathname;
      let match = path.match(/^(\/[^\/]+)/);
      if (match && match[1] && match[1].includes('cidade')) {
        return match[1] + '/';
      }
      return '/';
    };

    const getUrl = (endpoint) => {
      let base = getBaseUrl();
      let clean = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
      if (!base.endsWith('/')) base += '/';
      return base + clean;
    };

    const getHttpClient = () => {
      return window.axios || axios;
    };

    const buscarManuais = async () => {
      carregando.value = true;
      try {
        const http = getHttpClient();
        const url = getUrl('web/educacao/manuais/listar');
        const response = await http.get(url);
        if (response.data && response.data.data) {
          manuais.value = response.data.data;
        } else if (Array.isArray(response.data)) {
          manuais.value = response.data;
        }
      } catch (error) {
        console.error('Erro ao buscar manuais:', error);
        toast.add({
          severity: 'error',
          summary: 'Erro',
          detail: 'Não foi possível carregar a lista de manuais.',
          life: 4000
        });
      } finally {
        carregando.value = false;
      }
    };

    const manuaisFiltrados = computed(() => {
      if (!filtroBusca.value || filtroBusca.value.trim() === '') {
        return manuais.value;
      }
      const termo = filtroBusca.value.toLowerCase().trim();
      return manuais.value.filter(m => 
        (m.titulo && m.titulo.toLowerCase().includes(termo)) ||
        (m.descricao && m.descricao.toLowerCase().includes(termo)) ||
        (m.nome_arquivo_original && m.nome_arquivo_original.toLowerCase().includes(termo))
      );
    });

    const acionarInputArquivo = () => {
      if (fileInputRef.value) {
        fileInputRef.value.click();
      }
    };

    const validarEConfigurarArquivo = (file) => {
      erros.value.arquivo = '';
      if (!file) return;

      const nome = file.name.toLowerCase();
      const isPdf = nome.endsWith('.pdf') || file.type === 'application/pdf';

      if (!isPdf) {
        erros.value.arquivo = 'Formato inválido! Somente arquivos no formato PDF (.pdf) são permitidos.';
        toast.add({
          severity: 'warn',
          summary: 'Arquivo Inválido',
          detail: 'O arquivo precisa ser exclusivamente no formato PDF (.pdf).',
          life: 4000
        });
        arquivoSelecionado.value = null;
        if (fileInputRef.value) fileInputRef.value.value = '';
        return;
      }

      if (file.size > 209715200) { // 200MB
        erros.value.arquivo = 'O arquivo selecionado excede o limite máximo permitido de 200MB.';
        toast.add({
          severity: 'warn',
          summary: 'Arquivo muito grande',
          detail: 'O limite máximo para o PDF é de 200MB.',
          life: 4000
        });
        arquivoSelecionado.value = null;
        if (fileInputRef.value) fileInputRef.value.value = '';
        return;
      }

      arquivoSelecionado.value = file;
    };

    const onArquivoSelecionado = (event) => {
      const files = event.target.files;
      if (files && files.length > 0) {
        validarEConfigurarArquivo(files[0]);
      }
    };

    const onDropArquivo = (event) => {
      isDragOver.value = false;
      const files = event.dataTransfer.files;
      if (files && files.length > 0) {
        validarEConfigurarArquivo(files[0]);
      }
    };

    const removerArquivo = () => {
      arquivoSelecionado.value = null;
      erros.value.arquivo = '';
      if (fileInputRef.value) {
        fileInputRef.value.value = '';
      }
    };

    const abrirModalCadastro = () => {
      formulario.value.titulo = '';
      formulario.value.descricao = '';
      arquivoSelecionado.value = null;
      erros.value.titulo = '';
      erros.value.arquivo = '';
      progressoUpload.value = 0;
      modalCadastroAberto.value = true;
    };

    const fecharModalCadastro = () => {
      if (!enviando.value) {
        modalCadastroAberto.value = false;
        removerArquivo();
      }
    };

    const salvarManual = async () => {
      erros.value.titulo = '';
      erros.value.arquivo = '';

      let valido = true;
      if (!formulario.value.titulo || formulario.value.titulo.trim() === '') {
        erros.value.titulo = 'O título do documento é obrigatório.';
        valido = false;
      }

      if (!arquivoSelecionado.value) {
        erros.value.arquivo = 'Por favor, selecione um arquivo PDF.';
        valido = false;
      }

      if (!valido) return;

      enviando.value = true;
      progressoUpload.value = 0;

      try {
        const formData = new FormData();
        formData.append('titulo', formulario.value.titulo.trim());
        if (formulario.value.descricao) {
          formData.append('descricao', formulario.value.descricao.trim());
        }
        formData.append('arquivo', arquivoSelecionado.value);

        const http = getHttpClient();
        const url = getUrl('web/educacao/manuais/salvar');
        const response = await http.post(url, formData, {
          onUploadProgress: (progressEvent) => {
            if (progressEvent.total > 0) {
              progressoUpload.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            }
          }
        });

        toast.add({
          severity: 'success',
          summary: 'Sucesso',
          detail: response.data.message || 'Manual cadastrado com sucesso!',
          life: 4000
        });

        modalCadastroAberto.value = false;
        removerArquivo();
        await buscarManuais();
      } catch (error) {
        console.error('Erro ao enviar manual:', error);
        let msg = 'Erro ao enviar manual.';
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            msg = error.response.data.message;
          } else if (error.response.data.errors) {
            const errs = error.response.data.errors;
            msg = Object.keys(errs).map(k => errs[k].join(' ')).join(' | ');
          }
        }
        toast.add({
          severity: 'error',
          summary: 'Erro',
          detail: msg,
          life: 6000
        });
      } finally {
        enviando.value = false;
        progressoUpload.value = 0;
      }
    };

    const visualizarManual = (manual) => {
      manualSelecionado.value = manual;
      urlVisualizacao.value = getUrl(`web/educacao/manuais/visualizar/${manual.id}`);
      modalVisualizacaoAberto.value = true;
    };

    const fecharModalVisualizacao = () => {
      modalVisualizacaoAberto.value = false;
      manualSelecionado.value = null;
      urlVisualizacao.value = '';
    };

    const baixarManual = (manual) => {
      if (!manual) return;
      const url = getUrl(`web/educacao/manuais/download/${manual.id}`);
      window.open(url, '_blank');
    };

    const confirmarExclusao = (manual) => {
      manualExcluir.value = manual;
      modalExclusaoAberto.value = true;
    };

    const executarExclusao = async () => {
      if (!manualExcluir.value) return;
      excluindo.value = true;
      try {
        const http = getHttpClient();
        const url = getUrl(`web/educacao/manuais/excluir/${manualExcluir.value.id}`);
        const response = await http.delete(url);
        toast.add({
          severity: 'success',
          summary: 'Excluído',
          detail: response.data.message || 'Manual excluído com sucesso.',
          life: 3000
        });
        modalExclusaoAberto.value = false;
        manualExcluir.value = null;
        await buscarManuais();
      } catch (error) {
        console.error('Erro ao excluir manual:', error);
        let msg = 'Não foi possível excluir o manual.';
        if (error.response && error.response.data && error.response.data.message) {
          msg = error.response.data.message;
        }
        toast.add({
          severity: 'error',
          summary: 'Erro',
          detail: msg,
          life: 4000
        });
      } finally {
        excluindo.value = false;
      }
    };

    const formatarBytes = (bytes, precision = 2) => {
      if (!bytes || bytes <= 0) return '0 B';
      const units = ['B', 'KB', 'MB', 'GB'];
      const pow = Math.floor(Math.log(bytes) / Math.log(1024));
      return (bytes / Math.pow(1024, pow)).toFixed(precision) + ' ' + units[pow];
    };

    onMounted(() => {
      buscarManuais();
    });

    return {
      manuais,
      manuaisFiltrados,
      carregando,
      enviando,
      excluindo,
      progressoUpload,
      filtroBusca,
      isDragOver,
      fileInputRef,
      modalCadastroAberto,
      modalVisualizacaoAberto,
      modalExclusaoAberto,
      manualSelecionado,
      manualExcluir,
      urlVisualizacao,
      arquivoSelecionado,
      formulario,
      erros,
      buscarManuais,
      acionarInputArquivo,
      onArquivoSelecionado,
      onDropArquivo,
      removerArquivo,
      abrirModalCadastro,
      fecharModalCadastro,
      salvarManual,
      visualizarManual,
      fecharModalVisualizacao,
      baixarManual,
      confirmarExclusao,
      executarExclusao,
      formatarBytes
    };
  }
});
</script>

<style>
.manuais-container {
  max-width: 1200px;
  margin: 0 auto;
}

.upload-dropzone {
  background-color: #f8fafc;
  cursor: pointer;
}

.upload-dropzone:hover {
  background-color: #f1f5f9;
}

/* Botões com Alto Contraste e Legibilidade */
.btn-action-baixar {
  background-color: #16a34a !important;
  border-color: #16a34a !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  padding: 0.55rem 1.25rem !important;
  border-radius: 6px !important;
}
.btn-action-baixar .p-button-icon,
.btn-action-baixar .p-button-label {
  color: #ffffff !important;
  font-weight: 700 !important;
}
.btn-action-baixar:hover {
  background-color: #15803d !important;
  border-color: #15803d !important;
}

.btn-action-fechar {
  background-color: #334155 !important;
  border-color: #334155 !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  padding: 0.55rem 1.25rem !important;
  border-radius: 6px !important;
}
.btn-action-fechar .p-button-icon,
.btn-action-fechar .p-button-label {
  color: #ffffff !important;
  font-weight: 700 !important;
}
.btn-action-fechar:hover {
  background-color: #1e293b !important;
  border-color: #1e293b !important;
}

.btn-action-salvar {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  padding: 0.55rem 1.25rem !important;
  border-radius: 6px !important;
}
.btn-action-salvar .p-button-icon,
.btn-action-salvar .p-button-label {
  color: #ffffff !important;
  font-weight: 700 !important;
}
.btn-action-salvar:hover {
  background-color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}

.btn-action-cancelar {
  background-color: #64748b !important;
  border-color: #64748b !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  padding: 0.55rem 1.25rem !important;
  border-radius: 6px !important;
}
.btn-action-cancelar .p-button-icon,
.btn-action-cancelar .p-button-label {
  color: #ffffff !important;
  font-weight: 700 !important;
}
.btn-action-cancelar:hover {
  background-color: #475569 !important;
  border-color: #475569 !important;
}

.btn-action-excluir {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  padding: 0.55rem 1.25rem !important;
  border-radius: 6px !important;
}
.btn-action-excluir .p-button-icon,
.btn-action-excluir .p-button-label {
  color: #ffffff !important;
  font-weight: 700 !important;
}
.btn-action-excluir:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}
</style>
