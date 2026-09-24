import RequisicaoAPIPIXGeral from './Arrecadacao/RequisicaoAPIPIXGeral'
import GeracaoPDFEcartaGeral from './Cadastro/GeracaoPDFEcartaGeral'
import GeracaoPDFEcartaIndividual from './Cadastro/GeracaoPDFEcartaIndividual'
import CalculoGeralIPTU from './Cadastro/CalculoGeralIPTU'
import DecadDesconto from './Cadastro/DecadDesconto'
import DecadConsultaCgm from './Cadastro/DecadConsultaCgm'
import DecadConsultaEspelho from './Cadastro/DecadConsultaEspelho'
import ArquivoXMLCorreios from './Cadastro/ArquivoXMLCorreios'
import DataDeLancamento from './Arrecadacao/DataDeLancamento'
import NovosEstabelecimentos from "./Issqn/NovosEstabelecimentos"
import AtualizacaoDeCadastros from "./Issqn/AtualizacaoDeCadastros"
import ParametrosCemiterio from "./Cemiterio/ParametrosCemiterio"
import CancelaPacelamentoLista from "./Arrecadacao/CancelaPacelamentoLista"
import GrupoDeTaxas from "./Arrecadacao/GrupoDeTaxas"
import ConfigOrdenacaoCfg from "./Arrecadacao/ConfigOrdenacaoCfg"
import MassaFalida from "./Issqn/MassaFalida";
import DemostrativoCalculo from './Fiscalizacao/Vistoria/DemostrativoCalculo.vue'
import LancamentoHistorico from './DividaAtiva/LancamentoHistorico'
import ProcdividaativaSimples from './Issqn/procdividaativaSimples.vue'
import ImportadividaativaSimples from './Issqn/ImportadividaativaSimples.vue'
import ConsultaProssdividaativaSimples from './Issqn/ConsultaProssdividaativaSimples.vue'

import CadastroMacroZonas from './Cadastro/CadastroMacroZonas.vue'
import CadastroZonas from './Cadastro/CadastroZonas.vue'
import AlteracaoZonas from './Cadastro/AlteracaoZonas.vue'
import AlteracaoMacrozonas from './Cadastro/AlteracaoMacrozonas.vue'
import InclusaoSalaoParceiro from './Issqn/InclusaoSalaoParceiro'
import AlterarSalaoParceiro from './Issqn/AlterarSalaoParceiro.vue'
import InclusaoDeducaoSalao from './Issqn/InclusaoDeducaoSalao.vue'
import NotificacaoLancamentoIptu from './Cadastro/NotificacaoLancamentoIptu'
import InclusaoEnderecoEntrega from "./Issqn/EnderecoInscricao/InclusaoEnderecoEntrega.vue"
import AlteracaoEnderecoEntrega from "./Issqn/EnderecoInscricao/AlteracaoEnderecoEntrega.vue"
import ExclusaoEnderecoEntrega from "./Issqn/EnderecoInscricao/ExclusaoEnderecoEntrega.vue"

export default function (app) {
    app.component('requisicao_api_pix_geral', RequisicaoAPIPIXGeral)
    app.component('geracao_pdf_ecarta_geral', GeracaoPDFEcartaGeral)
    app.component('geracao_pdf_ecarta_individual', GeracaoPDFEcartaIndividual)
    app.component('calculo_geral_iptu', CalculoGeralIPTU)
    app.component('decad_desconto', DecadDesconto)
    app.component('decad_consulta_cgm', DecadConsultaCgm)
    app.component('decad_consulta_espelho', DecadConsultaEspelho)
    app.component('arquivo_xml_correios', ArquivoXMLCorreios)
    app.component('data_de_lancamento', DataDeLancamento)
    app.component('novos_estabelecimentos', NovosEstabelecimentos)
    app.component('importar_siples_divida_ativa', ImportadividaativaSimples)
    app.component('processar_simples_divida_ativa', ProcdividaativaSimples)
    app.component('consulta_simples_divida_ativa', ConsultaProssdividaativaSimples)
    app.component("atualizacao_de_cadastros_simples_nacional", AtualizacaoDeCadastros);
    app.component("parametros_cemiterio", ParametrosCemiterio);
    app.component('cancelparcelista', CancelaPacelamentoLista);
    app.component("grupo_de_taxas", GrupoDeTaxas);
    app.component("config_ordenacao_cgf", ConfigOrdenacaoCfg);
    app.component("massafalida", MassaFalida);
    app.component('demostrativo_calculo', DemostrativoCalculo);
    app.component("lancamento_historico", LancamentoHistorico);
    app.component("cadastro_macrozonas", CadastroMacroZonas);
    app.component("cadastro_zonas", CadastroZonas);
    app.component("alteracao_zonas", AlteracaoZonas);
    app.component("alteracao_macrozonas", AlteracaoMacrozonas);
    app.component("inclusao_salao_parceiro", InclusaoSalaoParceiro);
    app.component("alterar_salao_parceiro", AlterarSalaoParceiro);
    app.component("inclusao_deducao_salao", InclusaoDeducaoSalao);
    app.component('notificacao_lancamento_iptu', NotificacaoLancamentoIptu);
    app.component("inclusao-endereco-entrega", InclusaoEnderecoEntrega);
    app.component("alteracao-endereco-entrega", AlteracaoEnderecoEntrega);
    app.component("exclusao-endereco-entrega", ExclusaoEnderecoEntrega);
}
