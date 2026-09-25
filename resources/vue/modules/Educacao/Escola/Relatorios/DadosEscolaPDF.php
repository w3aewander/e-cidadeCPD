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

class DadosEscolaPDF extends Pdf
{
    protected $dadosEscola;
    const ALTURA_LINHA = 5;

    public function __construct($dadosEscola)
    {
        parent::__construct();
        $this->dadosEscola = $dadosEscola;
    }

    public function emitir()
    {
        $this->initPdf();
        $this->addPage();
        $this->montaTitulo();
        $this->montaIdentificacaoLocalizacao();
        $this->montaGestaoContato();
        $this->montaHorariosEscola();
        $this->montaCursosEscola();
        $this->montaAtosLegais();

        return $this->imprimir();
    }

    private function initPdf()
    {
        $this->mostrarRodape(true);
        $this->mostrarTotalDePaginas(true);
        $this->setMargins(8, 8, 8);
        $this->setAutoPageBreak(true, 25);
        $this->aliasNbPages();
        $this->setFillColor(235);
        $this->setFont('Arial', 'B', 9);
        $this->exibeHeader(true, 1);
        $this->addTitulo('DADOS DA ESCOLA', 1);
        $this->setExibeBrasao(true);
    }

    private function imprimir()
    {
        $fileName = 'tmp/ficha_cadastral_escola'.time().'.pdf';
        $this->output('F', $fileName);

        return [
            "name" => "Ficha Cadastral Escola",
            "path" => $fileName,
            'pathExterno' => ECIDADE_REQUEST_PATH.$fileName
        ];
    }

    private function montaTitulo()
    {
        $this->setY($this->getY() + 5);
        $this->setFont('Arial', 'B', 16);
        $this->setTextColor(2, 50, 115);
        $this->cell(200, self::ALTURA_LINHA, 'Dados da Escola', 0, 1, 'C', 0);
        $this->setFont('Arial', '', 8);
        $this->setTextColor(100, 116, 139);
        $this->cell(200, self::ALTURA_LINHA, 'Ficha Cadastral da Unidade de Ensino', 0, 1, 'C', 0);
    }

    private function montaIdentificacaoLocalizacao()
    {
        $this->setY($this->getY() + 5);
        $this->imprimeCabecalhoCard('Identificação e Localização', 58, false, false);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'NOME DA ESCOLA', 0, 1, 'L', 0);
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $this->dadosEscola['nome'], 0, 1, 'L', 0);
        $this->ln();
        $alturaInicial = $this->getY();
        $this->setFont('Arial', 'B', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'CÓDIGO INEP', 0, 1, 'L', 0);
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $this->dadosEscola['inep'], 0, 1, 'L', 0);
        $this->setY($alturaInicial);
        $this->setX(($this->getW() - 16) / 2);
        $this->setFont('Arial', 'B', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'ESFERA ADMINISTRATIVA', 0, 1, 'L', 0);
        $this->setX(($this->getW() - 16) / 2);
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $this->dadosEscola['esfera_administrativa'], 0, 1, 'L', 0);
        $this->ln();
        $this->setFont('Arial', 'B', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'ENDEREÇO COMPLETO', 0, 1, 'L', 0);
        $this->setFont('Arial', '', 8);
        $enderecoCompleto =
            $this->dadosEscola['endereco']['rua'] .
            ', Nº ' . $this->dadosEscola['endereco']['numero'] .
            ', ' . $this->dadosEscola['endereco']['bairro'];
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $enderecoCompleto, 0, 1, 'L', 0);
        $this->setY($this->getY() + 14);
    }

    private function imprimeCard($alturaCardInicial, $alturaCardFinal, $topo, $meiaPagina)
    {
        if ($meiaPagina) {
            $larguraCard = ($this->getW() / 2) - 10;
        } else {
            $larguraCard = $this->getW() - 16;
        }
        if ($topo) {
            $this->setDrawColor(2, 50, 115);
            $this->setFillColor(2, 50, 115);
            $this->rectBorderRadius($this->getX(), $alturaCardInicial, $larguraCard, 5, 2, 2, 2, 2, 'F');
        } else {
            $this->setDrawColor(248, 249, 250);
            $this->setFillColor(248, 249, 250);
            $this->rectBorderRadius($this->getX(), $alturaCardInicial, $larguraCard, $alturaCardFinal, 2, 2, 2, 2, 'F');
        }
    }

    private function imprimeLinha($meiaPagina, $segundaMetade)
    {
        $comprimentoLinha = $meiaPagina ? ($this->getW() / 2) - 16 : $this->getW() - 20;
        $inicioLinha = $segundaMetade ? 110 : $this->getX() + 2;
        $this->setDrawColor(226, 232, 240);
        $this->line($inicioLinha, $this->getY() + 2, $inicioLinha + $comprimentoLinha, $this->getY() + 2);
    }

    private function imprimeCabecalhoCard($titulo, $alturaCardFinal, $meiaPagina, $segundaMetade)
    {
        $this->imprimeCard($this->getY() - 4, $this->getY() + 5, true, $meiaPagina);
        $this->imprimeCard($this->getY() - 3, $alturaCardFinal, false, $meiaPagina);
        $this->setFont('Arial', 'B', 12);
        $this->setTextColor(2, 50, 115);
        $this->setX($this->getX() + 2);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $titulo, 0, 1, 'L', 0);
        $this->imprimeLinha($meiaPagina, $segundaMetade);
        $this->ln();
        $this->setTextColor(30, 41, 59);
        $this->setFont('Arial', 'B', 8);
    }

    private function montaGestaoContato()
    {
        $this->imprimeCabecalhoCard('Gestão e Contato', 44, false, false);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'GESTOR(A) RESPONSÁVEL', 0, 1, 'L', 0);
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $this->dadosEscola['responsavel'], 0, 1, 'L', 0);
        $this->ln();
        $alturaInicial = $this->getY();
        $this->setFont('Arial', 'B', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'E-MAIL INSTITUCIONAL', 0, 1, 'L', 0);
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $this->dadosEscola['email'], 0, 1, 'L', 0);
        $this->setY($alturaInicial);
        $this->setX(($this->getW() - 16) / 2);
        $this->setFont('Arial', 'B', 8);
        $this->setX($this->getX() + 5);
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'TELEFONES', 0, 1, 'L', 0);
        $this->setX(($this->getW() - 16) / 2);
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() + 5);
        $telefones = '';
        $cont = 0;
        foreach ($this->dadosEscola['telefones'] as $telefone) {
            if ($cont > 0) {
                $telefones .= ' / ';
            }
            $telefones .= '(' . $telefone['ddd'] . ') ' . $telefone['numero'];
            $cont++;
        }
        $this->cell($this->getW() - 16, self::ALTURA_LINHA, $telefones, 0, 1, 'L', 0);
        $this->setY($this->getY() + 14);
    }

    private function montaHorariosEscola()
    {
        $alturaInicial = $this->getY();
        $alturaCard = (count($this->dadosEscola['cursos']) * 5) + 20;
        $this->imprimeCabecalhoCard('Turnos e Horários', $alturaCard, true, false);
        foreach ($this->dadosEscola['turnos'] as $turno) {
            $this->setFont('Arial', 'B', 8);
            $this->setX($this->getX() + 5);
            $this->cell(($this->getW() / 5) - 12, self::ALTURA_LINHA, $turno['nome'], 0, 0, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() + 25);
            $horarios = $turno['inicio'] . ' às ' . $turno['fim'];
            $this->cell(($this->getW() / 5) - 12, self::ALTURA_LINHA, $horarios, 0, 1, 'L', 0);
        }
        $this->setY($alturaInicial);
        $this->setX(($this->getW() / 2) + 2);
    }

    private function montaCursosEscola()
    {
        $alturaCard = (count($this->dadosEscola['cursos']) * 5) + 20;
        $this->imprimeCabecalhoCard('Cursos da Escola', $alturaCard, true, true);
        foreach ($this->dadosEscola['cursos'] as $curso) {
            $this->setX(($this->getW() / 2) - 20);
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() + 25);
            $this->cell(($this->getW() / 5) - 12, self::ALTURA_LINHA, $curso['descricao'], 0, 1, 'L', 0);
        }
        $this->ln();
    }

    private function montaAtosLegais()
    {
        foreach ($this->dadosEscola['atos_legais'] as $ato) {
            if ($this->getY() >= $this->getH() - 45) {
                $this->addPage();
            }
            $this->setY($this->getY() + 10);
            $this->imprimeCabecalhoCard('Atos Legais e Regulamentação', 80, false, false);
            $this->imprimeCardDecreto();
            $this->setFont('Arial', 'B', 8);
            $this->setXY($this->getX() + 7, $this->getY() + 2);
            $this->setTextColor(2, 50, 115);
            $this->cell(($this->getW() / 5) - 12, self::ALTURA_LINHA, 'DECRETO Nº ' . $ato['numero'], 0, 0, 'L', 0);
            $this->imprimeAnoDecreto($ato['ano']);
            $this->setTextColor(30, 41, 59);
            $this->setFont('Arial', 'B', 8);
            $this->setXY($this->getX() + 8, $this->getY() + 5);
            // ÓRGÃO e FINALIDADE
            $alturaInicial = $this->getY();
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'ÓRGÃO EMISSOR', 0, 1, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() + 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, $ato['orgao'], 0, 1, 'L', 0);
            $this->setY($alturaInicial);
            $this->setX($this->getW() / 2.2);
            $this->setFont('Arial', 'B', 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'FINALIDADE', 0, 1, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX(($this->getW()) / 2.2);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, $ato['finalidade'], 0, 1, 'L', 0);
            $this->setY($alturaInicial);
            $this->setX(($this->getW() / 3) + 90);
            $this->setFont('Arial', 'B', 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'EXIBE NO HISTÓRICO', 0, 1, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX(($this->getW() / 3) + 90);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, $ato['aparece_historico'], 0, 1, 'L', 0);
            // DATAS
            $this->setXY($this->getX() + 8, $this->getY() + 2);
            $alturaInicial = $this->getY();
            $this->setFont('Arial', 'B', 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'VIGÊNCIA', 0, 1, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() + 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, $ato['data_vigora'], 0, 1, 'L', 0);
            $this->setY($alturaInicial);
            $this->setX(($this->getW() - 16) / 3);
            $this->setFont('Arial', 'B', 8);
            $this->setX($this->getX() + 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'APROVAÇÃO', 0, 1, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX(($this->getW() - 16) / 3);
            $this->setX($this->getX() + 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, $ato['data_aprovado'], 0, 1, 'L', 0);
            $this->setY($alturaInicial);
            $this->setX(($this->getW() / 3) + 60);
            $this->setFont('Arial', 'B', 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'PUBLICAÇÃO', 0, 1, 'L', 0);
            $this->setFont('Arial', '', 8);
            $this->setX(($this->getW() / 3) + 60);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, $ato['data_publicado'], 0, 1, 'L', 0);
            // TEXTO
            $this->setXY($this->getX() + 8, $this->getY() + 2);
            $this->setFont('Arial', 'B', 8);
            $this->cell($this->getW() - 16, self::ALTURA_LINHA, 'TEXTO ATO / OBSERVAÇÃO', 0, 1, 'L', 0);
            $this->setFont('Courier', '', 7);
            $this->setX($this->getX() + 8);
            $this->setFillColor(226, 232, 240);
            $this->multiCell($this->getW() - 33, self::ALTURA_LINHA, $ato['texto'], 0, 'L', 1);
        }
    }

    private function imprimeCardDecreto()
    {
        $this->setDrawColor(228, 229, 230);
        $this->setFillColor(240, 240, 240);
        $this->rectBorderRadius($this->getX() + 4, $this->getY(), $this->getW() - 25, 60, 2, 2, 2, 2, 'F');
    }

    private function imprimeAnoDecreto($ano)
    {
        $this->setFillColor(2, 50, 115);
        $this->rectBorderRadius($this->getW() - 34, $this->getY(), 15, 5, 1, 1, 1, 1, 'F');
        $this->setX($this->getW() - 43);
        $this->cell(12, self::ALTURA_LINHA, 'Ano', 0, 0, 'L', 0);
        $this->setX($this->getW() - 31);
        $this->setTextColor(255, 255, 255);
        $this->setFont('Arial', 'B', 8);
        $this->cell(($this->getW() / 5) - 12, self::ALTURA_LINHA, $ano, 0, 1, 'L', 0);
        $this->setDrawColor(220, 226, 234);
        $this->line($this->getX() + 8, $this->getY() + 2, 194, $this->getY() + 2);
    }
}
