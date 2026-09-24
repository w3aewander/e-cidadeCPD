<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

namespace App\Domain\Educacao\Escola\Relatorios;

use ECidade\Pdf\Pdf;

class GuiaTransposicaoPDF extends Pdf
{
    /**
     * @var object
     */
    private $dados;

    /**
     * @var string
     */
    private $nomeAluno;

    /**
     * @var object
     */
    protected $tamanhos;

    public function __construct($dados)
    {
        parent::__construct('P');
        $this->dados = $dados;
        $this->tamanhos = (object)[
            'alturaLinha' => 5,
            'margemHeader' => 50
        ];

        $nomeAluno = $this->dados->nomeAluno;
        if ($this->dados->nomeAlunoSocial !== "") {
            $nomeAluno .= " / " . $this->dados->nomeAlunoSocial;
        }
        $this->nomeAluno = $nomeAluno;
    }

    private function initPdf()
    {
        $this->mostrarRodape();
        $this->setMargins(20, 8, 20);
        $this->setAutoPageBreak(false, 10);
        $this->aliasNbPages();
        $this->setFillColor(235);
        $this->setFont('Arial', 'B', 12);
        $this->exibeHeader(true, 1);
        $this->setExibeBrasao(true);
        $this->mostrarEmissor(true);
    }

    protected function imprimir()
    {
        $encode = mb_detect_encoding($this->dados->nomeAluno, "UTF-8, ISO-8859-1, ISO-8859-15", true);
        $firstName = explode(" ", trim($this->dados->nomeAluno));
        $firstName = \DBString::removerAcentuacao(mb_strtolower($firstName[0], $encode));
        $fileName  = 'tmp/guia_transposicao_' . $firstName . '_' .time() . '.pdf';
        $this->output('F', $fileName);
        return [
            "name" => "Guia de Transposição",
            "path" => $fileName,
            "file" => ECIDADE_REQUEST_PATH . $fileName
        ];
    }

    public function emitirPdf()
    {
        $this->initPdf();

        $this->addTitulo("GUIA DE TRANSPOSIÇÃO", 'titulo');
        $this->addTitulo($this->nomeAluno, 'aluno');
        $this->addPage();

        $this->Rect(
            5,
            $this->getY(),
            ($this->getPageWidth() - 10),
            ($this->getPageHeight() - $this->tamanhos->margemHeader)
        );

        $this->SetY($this->GetY() + 20);
        $this->cell(0, $this->tamanhos->alturaLinha * 2, "GUIA DE TRANSPOSIÇÃO", 0, 1, 'C');

        $this->imprimirCorpo();
        $this->assinaturas();

        return $this->imprimir();
    }

    public function imprimirCorpo()
    {
        $this->Ln();
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, 'Atesto que o(a) estudante ');
        $this->changeBold(true);

        $this->Write($this->tamanhos->alturaLinha, $this->nomeAluno);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ', matriculado(a) na ');

        $this->changeBold(true);
        $textoMatriculaAnterior = "Etapa: " . $this->dados->etapaOrigem . " - " . $this->dados->faseOrigem;
        $this->Write($this->tamanhos->alturaLinha, $textoMatriculaAnterior);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ', no ano letivo de ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, $this->dados->anoOrigem);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ', na unidade escolar de origem ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, $this->dados->escolaOrigem);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ', foi ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, 'designado(a), via transposição, ');
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, 'para a unidade de destino ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, $this->dados->escolaDestino);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ', a fim de cursar a ');

        $this->changeBold(true);
        $textoMatriculaFutura = "Etapa: " . $this->dados->etapaDestino . " - " . $this->dados->faseDestino;
        $this->Write($this->tamanhos->alturaLinha, $textoMatriculaFutura);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ', no ano letivo de ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, $this->dados->anoDestino . '.');

        $this->Ln($this->tamanhos->alturaLinha * 3);
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, 'Obs.: Após o término do ano letivo, o responsável legal deverá ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, 'obrigatoriamente');
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ' retirar o ');
        $this->changeBold(true);
        $this->Write($this->tamanhos->alturaLinha, 'Protocolo de Transferência com o Resultado Final');
        $this->changeBold(false);
        $this->Write($this->tamanhos->alturaLinha, ' na unidade de origem e apresentá-lo na unidade de destino.');
        $this->Write($this->tamanhos->alturaLinha, ' Este procedimento é necessário para a confirmação da vaga ');
        $this->Write($this->tamanhos->alturaLinha, 'pretendida e início da frequência escolar, evitando possíveis ');
        $this->Write($this->tamanhos->alturaLinha, 'inconsistências nos registros.');
    }

    public function assinaturas()
    {
        $this->SetY($this->GetY() + ($this->tamanhos->alturaLinha * 4));
        $infoAssinatura  = $this->dados->municipioDestino . ", ";
        $infoAssinatura .= str_repeat("_", 10) . " de ";
        $infoAssinatura .= str_repeat("_", 20) . " de " . $this->dados->anoAtual;

        $this->cell(0, $this->tamanhos->alturaLinha * 2, $infoAssinatura, 0, 1, 'C');

        $this->SetY($this->GetY() + ($this->tamanhos->alturaLinha * 3));
        $this->cell(0, $this->tamanhos->alturaLinha, str_repeat("_", 50), 0, 1, 'C');
        $this->cell(0, $this->tamanhos->alturaLinha, "ASSINATURA DO RESPONSÁVEL", 0, 1, 'C');

        $this->SetY($this->GetY() + ($this->tamanhos->alturaLinha * 2));
        $this->cell(0, $this->tamanhos->alturaLinha, str_repeat("_", 50), 0, 1, 'C');
        $this->cell(0, $this->tamanhos->alturaLinha, "ASSINATURA E CARIMBO DA DIREÇÃO", 0, 1, 'C');
    }

    public function changeBold($which = true)
    {
        if ($which == true) {
            $this->SetFont('Arial', 'B', 10);
        } else {
            $this->SetFont('Arial', '', 10);
        }
    }
}
