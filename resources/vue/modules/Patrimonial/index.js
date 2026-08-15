import AtendimentoJson from "./Ouvidoria/AtendimentoJson";
import IncluirOrgao from "./PNCP/IncluirOrgao";
import IncluirDocumentoLicitacao from "./PNCP/IncluirDocumentoLicitacao.vue";
import ExclusaoDocumento from "@modules/Patrimonial/PNCP/procedimentos/contrato/ExclusaoDocumento.vue";
import ManutencaoDeAtividade from "./Protocolo/DocumentosAndamento/ManutencaoDeAtividade";
import AlterarDotacao from "./Contratos/AlterarDotacao"
import IncluirObra from "./Licitacao/Licitacon/Obras/Incluir.vue";
import ParametrosLicitacon from "./Licitacao/Licitacon/Parametros.vue";
import ExclusaoDocumentoContratacao from "./PNCP/Procedimentos/ContratacaoEditalAviso/ExclusaoDocumentoContratacao.vue";
import ProtocoloDocumento from './Protocolo/ProtocoloDocumento';
import SolicitacaoAssinatura from './Protocolo/AndamentoProcesso/Components/SolicitarAssinaturas/SolicitacaoAssinatura.vue';
import MensageriaProtocolo from './Protocolo/AndamentoProcesso/Components/MensageriaProtocolo.vue';
import ConsultaAtendimento from './Protocolo/Atendimento/ConsultaAtendimento';
import AndamentoProcesso from './Protocolo/AndamentoProcesso/AndamentoProcesso';

import ImplantacaoSaldoPlanilha from "./Material/ImplantacaoSaldoPlanilha.vue";
import SolicitacoesAssinaturaDocumentos from "./Protocolo/SolicitacoesAssinatura/SolicitacoesAssinaturaDocumentos.vue";
import Acoes from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/Acoes.vue";
import MensageriaAcordosConfig from "@modules/Patrimonial/Contratos/MensageriaAcordosConfig.vue";

export default function (app) {
    app.component('atendimento_json',AtendimentoJson);
    app.component('incluir_orgao',IncluirOrgao);
    app.component('exclusao-documento',ExclusaoDocumento);
    app.component('manutenca_de_atividade',ManutencaoDeAtividade);
    app.component('alterar_dotacao',AlterarDotacao);
    app.component('protocolo_documento', ProtocoloDocumento);
    app.component('parametros_licitacon', ParametrosLicitacon);
    app.component('incluir_obra_licitacon', IncluirObra);
    app.component('mensageria_protocolo', MensageriaProtocolo);
    app.component('solicitacao-assinatura', SolicitacaoAssinatura)
    app.component('implantacao_saldo_planilha', ImplantacaoSaldoPlanilha);
    app.component('consulta-atendimento', ConsultaAtendimento);
    app.component('incluir-documento-licitacao', IncluirDocumentoLicitacao);
    app.component('exclusao-documento-contratacao', ExclusaoDocumentoContratacao);
    app.component('andamento-processo', AndamentoProcesso);
    app.component('acoes-andamento', Acoes);
    app.component("solicitacoes-assinatura-documentos", SolicitacoesAssinaturaDocumentos);
    app.component("mensageria-acordos-config", MensageriaAcordosConfig);
}
