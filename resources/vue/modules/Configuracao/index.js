import ConfigArquivoXMLCorreios from './Procedimentos/ArquivoXMLCorreios';
import GeradorRelatorios from "./Relatorios/Gerador";
import TelaDinamica from "./Relatorios/Gerador/Components/DialogTelaDinamica";

export default function (app) {
    app.component('config_arquivo_xml_correios', ConfigArquivoXMLCorreios);
    app.component('gerador_relatorios', GeradorRelatorios);
    app.component('gerador_tela_dinamica', TelaDinamica);
}
