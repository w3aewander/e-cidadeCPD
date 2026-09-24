import StatusPaciente from "./Ambulatorial/Consultas/StatusPaciente.vue";
import UnidadeEncaminhadora from "./Ambulatorial/Cadastros/UnidadeEncaminhadora.vue";
import UnificacaoCgs from "./Ambulatorial/Procedimentos/UnificacaoCgs.vue";
import ConsultaCgsUnificado from "./Ambulatorial/Consultas/ConsultaCgsUnificado.vue";

export default function (app) {
    app.component('status_paciente', StatusPaciente);
    app.component('unidade_encaminhadora', UnidadeEncaminhadora);
    app.component('unificacao_cgs', UnificacaoCgs);
    app.component('consulta_cgs_unificado', ConsultaCgsUnificado);
}