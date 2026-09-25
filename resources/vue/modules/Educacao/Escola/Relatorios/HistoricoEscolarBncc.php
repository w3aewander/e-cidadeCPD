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

use App\Domain\Educacao\Escola\Models\CursoEdu;
use App\Domain\Educacao\Secretaria\Enums\CamposExperienciaEnum;
use Carbon\Carbon;
use ECidade\Enum\Educacao\Escola\TipoEnsinoEnum;
use ECidade\Pdf\Pdf;

class HistoricoEscolarBncc extends Pdf
{
    const ALTURA_LINHA = 4.5;
    const LARGURA_BASE_CURRICULAR = 10;
    const LARGURA_ANOS_SERIES_EI = 120;
    const LARGURA_ANOS_SERIES_EF = 90;
    const ESPESSURA_LINHA = 0.2;
    private $dadosHistorico = [];
    private $configGrade;
    private $sCabecalho = '';
    private $oDadosCabecalhoEscola = null;
    private $sRodape = '';
    private $sObservacao = '';
    private $oParametros = null;
    private $sBrasao = '';
    private $oDadosPessoaisAluno;
    private $sTituloHistorico = 'HISTÓRICO ESCOLAR';
    private $isBaseDiversificada = false;
    /**
     * @var int|null
     */
    private $qtdEnsinos;
    private $temEnsinoInfantil = false;
    private $imprimeOutraBase = false;
    private $iFonteGradeNota;
    private $iFonteGradeEtapa;
    private $iFonteObservacao;
    private $sDataEmissao;
    private $sAssinaturaSecretario;
    private $sAssinaturaDiretor;
    /**
     * @var array
     */
    private $obsPorEtapa;
    private $exibeEtapaObs;
    /**
     * @var array
     */
    private $aObservacoesCurso = [];
    private $exibePorcentagemFreq;
    private $exibeMantenedora;
    /**
     * @var int
     */
    private $iTotalLinhas;
    private $exibeIdentidade;
    private $exibeResultadoFinalEtapaRede;
    private $exibeCamposExperienciaEI;
    private $getYInicialCamposEXP;
    private $colunasEmBranco = [];
    /**
     * @var array
     */
    private $ultimaEtapaCursada;
    private $exibeLeiDecretoCriacao;
    private $exibeLeiDecretoDenominacao;
    private $exibeResolucaoAutorizacao;
    private $exibeResolucaoReconhecimento;
    private $brasaoCentralizado;
    private $preencheQuadroObservacao;
    /**
     * @var boolean
     */
    private $alunoAprovadoComProgressao;
    /**
     * @var array
     */
    private $aObsProgressoes;

    /**
     * @return void
     */
    public function __construct($dados)
    {
        parent::__construct();
        $this->dadosHistorico = $dados;
    }

    public function setCabecalho($cabecalho)
    {
        $this->sCabecalho = $cabecalho;
    }

    public function setRodape($rodape)
    {
        $this->sRodape = $rodape;
    }

    public function setObservacao($observacao)
    {
        $this->sObservacao = $observacao;
    }

    public function setBrasao($brasao)
    {
        $this->sBrasao = $brasao;
    }

    public function setDadosCabecalhoEscola($dadosCabecalho)
    {
        $this->oDadosCabecalhoEscola = $dadosCabecalho;
    }

    public function setDadosPessoaisAluno($oDadosPessoaisAluno)
    {
        $this->oDadosPessoaisAluno = $oDadosPessoaisAluno;
    }

    public function setBaseDiversificada($isBaseDiversificada)
    {
        $this->isBaseDiversificada = $isBaseDiversificada;
    }

    public function setConfigGrade($oConfigGrade)
    {
        $this->configGrade = $oConfigGrade;
        $this->qtdEnsinos = count($oConfigGrade->basesPorEnsino);
        $this->temEnsinoInfantil = array_key_exists(1, $oConfigGrade->basesPorEnsino);
    }

    public function setFonteGradeNota($fonteGradeNota)
    {
        $this->iFonteGradeNota = $fonteGradeNota;
    }

    public function setFonteGradeEtapa($fonteGradeEtapa)
    {
        $this->iFonteGradeEtapa = $fonteGradeEtapa;
    }

    public function setFonteObservacao($fonteObservacao)
    {
        $this->iFonteObservacao = $fonteObservacao;
    }

    public function setDataEmissao($sDataEmissao)
    {
        $this->sDataEmissao = $sDataEmissao;
    }

    public function setAssinaturaSecretario($sSecretario)
    {
        if (empty($sSecretario)) {
            $sSecretario = " - ";
        }
        $aTexto = explode("-", $sSecretario);
        $this->sAssinaturaSecretario = $aTexto[1];
    }

    public function setAssinaturaDiretor($sDiretor)
    {
        if (empty($sDiretor)) {
            $sDiretor = " - ";
        }
        $aTexto = explode("-", $sDiretor);
        $this->sAssinaturaDiretor = $aTexto[1];
    }

    public function setObservacoesEtapas($observacoesOrganizadas)
    {
        $this->obsPorEtapa = $observacoesOrganizadas;
    }

    public function setExibeEtapaObs($getExibeEtapaObservacao)
    {
        $this->exibeEtapaObs = $getExibeEtapaObservacao;
    }

    public function setObservacoesCursos($aObservacoesCurso)
    {
        $this->aObservacoesCurso = $aObservacoesCurso;
    }

    public function setExibePorcentagemFreq($getExibePorcentagemFreq)
    {
        $this->exibePorcentagemFreq = $getExibePorcentagemFreq;
    }

    public function setExibeMantenedora($getExibeMantenedora)
    {
        $this->exibeMantenedora = $getExibeMantenedora;
    }

    public function setExibeIdentidade($exibeIdentidade)
    {
        $this->exibeIdentidade = $exibeIdentidade;
    }

    public function setExibeResultadoFinalEtapaRede($exibeResultadoFinalEtapaRede)
    {
        $this->exibeResultadoFinalEtapaRede = !($exibeResultadoFinalEtapaRede == 'f');
    }

    public function setExibeCamposExperienciaEI($exibeCamposExperienciaEI)
    {
        $this->exibeCamposExperienciaEI = $exibeCamposExperienciaEI;
    }

    public function setUltimaEtapaCursada($ultimaEtapaCursada)
    {
        $this->ultimaEtapaCursada = $ultimaEtapaCursada;
    }

    public function setgetExibeLeiDecretoCriacao($exibeLeiDecretoCriacao)
    {
        $this->exibeLeiDecretoCriacao = $exibeLeiDecretoCriacao;
    }

    public function setExibeLeiDecretoDenominacao($exibeLeiDecretoDenominacao)
    {
        $this->exibeLeiDecretoDenominacao = $exibeLeiDecretoDenominacao;
    }

    public function setExibeResolucaoAutorizacao($exibeResolucaoAutorizacao)
    {
        $this->exibeResolucaoAutorizacao = $exibeResolucaoAutorizacao;
    }

    public function setExibeResolucaoReconhecimento($exibeResolucaoReconhecimento)
    {
        $this->exibeResolucaoReconhecimento = $exibeResolucaoReconhecimento;
    }

    public function setExibeBrasaoCentralizado($exibeBrasaoCentralizado)
    {
        $this->brasaoCentralizado = $exibeBrasaoCentralizado;
    }

    public function setPreencheQuadroObservacao($preencheQuadroObservacao)
    {
        $this->preencheQuadroObservacao = $preencheQuadroObservacao;
    }

    public function setAlunoAprovadoComProgressao($alunoAprovadoComProgressao)
    {
        $this->alunoAprovadoComProgressao = $alunoAprovadoComProgressao;
    }

    public function setObsProgressao(array $aObsProgressoes)
    {
        $this->aObsProgressoes = $aObsProgressoes;
    }

    private function initPdf()
    {
        $this->mostrarRodape();
        $this->aliasNbPages();
        $this->mostrarTotalDePaginas();
        $this->setMargins(8, 8, 8);
        $this->setAutoPageBreak(false, 10);
        $this->setFillColor(235);
        $this->setFont('Arial', '', 8);
        $this->exibeHeader(false);
        $this->setLineWidth(self::ESPESSURA_LINHA);
        $this->addPage();
    }

    protected function imprimir()
    {
        $fileName = 'tmp/historico_escolar_bncc' . time() . '.pdf';
        $this->output('F', $fileName);
        return [
            "name" => "Histórico Escolar BNCC",
            "path" => $fileName,
            "file" => ECIDADE_REQUEST_PATH . $fileName
        ];
    }

    public function emitirHistorico()
    {
        $this->initPdf();
        $this->imprimeCabecalho();
        $this->imprimeCabecalhoEscola();
        $this->imprimeDadosPessoaisAluno();
        $this->imprimeGradePrincipal();
        return $this->imprimir();
    }

    public function imprimeCabecalho()
    {
        $alturaBrasao = 5;
        $posicaoXLogo = 40;
        $posicaoYFinal = 5;
        if ($this->brasaoCentralizado) {
            $posicaoXLogo = ($this->getW() - 16) / 2;
            $posicaoYFinal = 23;
        } else {
            $linhasCabecalho = $this->nbLines($this->getW() - 16, $this->sCabecalho);
            $alturaBrasao    = $linhasCabecalho <= 3 ? 5 : 10;

            if ($this->getY() <= ($alturaBrasao + 16)) {
                $posicaoYFinal = $alturaBrasao + 16;
            }
        }

        if (file_exists($this->sBrasao)) {
            $this->image($this->sBrasao, $posicaoXLogo, $alturaBrasao, 16, 16);
            if ($this->brasaoCentralizado) {
                $this->setY($posicaoYFinal);
            }
        }
        $this->multiCell($this->getW() - 16, self::ALTURA_LINHA, $this->sCabecalho, 0, 'C');
    }

    private function imprimeCabecalhoEscola()
    {
        $this->setY($this->getY() + 3);
        // ESCOLA
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, self::ALTURA_LINHA, ' ESCOLA: ', 'LTR', 0, 'L');
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() - 180);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosCabecalhoEscola->escola, '', 1, 'L');

        // MANTENEDORA
        if ($this->exibeMantenedora) {
            $this->setFont('Arial', 'B', 8);
            $this->cell(195, self::ALTURA_LINHA, ' ENTIDADE MANTENEDORA: ', 'LR', 0, 'L');
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() - 155);
            $this->cell(195, self::ALTURA_LINHA, $this->oDadosCabecalhoEscola->mantenedora, '', 1, 'L');
        }

        // CRIAÇÃO
        if ($this->exibeLeiDecretoCriacao) {
            $this->setFont('Arial', 'B', 8);
            $this->cell(195, self::ALTURA_LINHA, ' LEI/DECRETO DE CRIAÇÃO: Lei Nº ', 'LR', 0, 'L');
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() - 146);
            $this->cell(195, self::ALTURA_LINHA, $this->oDadosCabecalhoEscola->atoCriacao, '', 1, 'L');
        }

        // DENOMINAÇÃO
        if ($this->exibeLeiDecretoDenominacao) {
            $this->setFont('Arial', 'B', 8);
            $this->cell(195, self::ALTURA_LINHA, ' LEI/DECRETO DE DENOMINAÇÃO: Lei Nº ', 'LR', 0, 'L');
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() - 138);
            $this->cell(195, self::ALTURA_LINHA, $this->oDadosCabecalhoEscola->atoDenominacao, '', 1, 'L');
        }

        // AUTORIZAÇÃO
        if ($this->exibeResolucaoAutorizacao) {
            $this->setFont('Arial', 'B', 8);
            $this->cell(195, self::ALTURA_LINHA, ' RESOLUÇÃO DE AUTORIZAÇÃO: ', 'LR', 0, 'L');
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() - 148);
            $this->cell(195, self::ALTURA_LINHA, $this->oDadosCabecalhoEscola->atoAutorizacao, '', 1, 'L');
        }

        // RECONHECIMENTO
        if ($this->exibeResolucaoReconhecimento) {
            $this->setFont('Arial', 'B', 8);
            $this->cell(195, self::ALTURA_LINHA, ' RESOLUÇÃO DE RECONHECIMENTO: ', 'LBR', 0, 'L');
            $this->setFont('Arial', '', 8);
            $this->setX($this->getX() - 142);
            $this->cell(195, self::ALTURA_LINHA, $this->oDadosCabecalhoEscola->atoReconhecimento, '', 1, 'L');
        }

        // Imprime fechamento do quadro caso não seja impresso a Resolução de Reconhecimento
        if (!$this->exibeResolucaoReconhecimento) {
            $this->setY($this->getY() - 4);
            $this->cell(195, self::ALTURA_LINHA, '', 'LBR', 1, 'L');
        }
    }

    private function imprimeDadosPessoaisAluno()
    {
        $this->setY($this->getY() + 2);
        // NOME ALUNO
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, self::ALTURA_LINHA, ' NOME DO(A) ALUNO(A): ', 'LTR', 0, 'L');
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() - 160);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->nome, '', 1, 'L');

        // DADOS RG, CPF, CODINEP
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, self::ALTURA_LINHA, ' COD. INEP: ', 'LR', 0, 'L');
        $this->setX($this->getX() - 130);
        $this->cell(130, self::ALTURA_LINHA, ' CPF: ', '', 0, 'L');
        if ($this->exibeIdentidade) {
            $this->setX($this->getX() - 65);
            $this->cell(65, self::ALTURA_LINHA, ' RG: ', '', 0, 'L');
        }
        $this->setFont('Arial', '', 8);
        $this->setX(25);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->codInep, '', 0, 'L');
        $this->setX(81);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->cpf, '', $this->exibeIdentidade ? 0 : 1, 'L');
        if ($this->exibeIdentidade) {
            $this->setX(145);
            $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->identidade, '', 1, 'L');
        }

        // DADOS NASCIMENTO
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, self::ALTURA_LINHA, ' DATA NASCIMENTO: ', 'LR', 0, 'L');
        $this->setX($this->getX() - 130);
        $this->cell(130, self::ALTURA_LINHA, ' LOCAL: ', '', 0, 'L');
        $this->setX($this->getX() - 65);
        $this->cell(65, self::ALTURA_LINHA, ' ESTADO: ', '', 0, 'L');
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() - 165);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->dataNascimento, '', 0, 'L');
        $this->setX($this->getX() - 148);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->municipioNascimento, '', 0, 'L');
        $this->setX($this->getX() - 128);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->estadoNascimento, '', 1, 'L');

        // NOME DO PAI
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, self::ALTURA_LINHA, ' FILIAÇÃO 1: ', 'LR', 0, 'L');
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() - 173);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->nomePai, '', 1, 'L');

        // NOME DA MAE
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, self::ALTURA_LINHA, ' FILIAÇÃO 2: ', 'LBR', 0, 'L');
        $this->setFont('Arial', '', 8);
        $this->setX($this->getX() - 172);
        $this->cell(195, self::ALTURA_LINHA, $this->oDadosPessoaisAluno->nomeMae, '', 1, 'L');
    }

    private function imprimeGradePrincipal()
    {
        foreach ($this->configGrade->basesPorEnsino as $keyEnsino => $ensino) {
            if ($keyEnsino == TipoEnsinoEnum::ENSINO_INFANTIL) {
                $this->imprimeOutraBase = true;
                $this->imprimeTituloHistorico('INFANTIL');
                $this->imprimeCabecalhoTabela(
                    $ensino,
                    TipoEnsinoEnum::ENSINO_INFANTIL,
                    self::LARGURA_ANOS_SERIES_EI
                );
                $this->imprimeBaseComum($ensino, self::LARGURA_ANOS_SERIES_EI, TipoEnsinoEnum::ENSINO_INFANTIL);
                $this->imprimeBaseDiversificada($ensino, self::LARGURA_ANOS_SERIES_EI, TipoEnsinoEnum::ENSINO_INFANTIL);
                $this->imprimeResultadoFinal($ensino, self::LARGURA_ANOS_SERIES_EI, TipoEnsinoEnum::ENSINO_INFANTIL);
                $this->imprimeRodape(TipoEnsinoEnum::ENSINO_INFANTIL);
            } elseif ($keyEnsino == TipoEnsinoEnum::ENSINO_FUNDAMENTAL) {
                if ($this->imprimeOutraBase) {
                    $this->setFont('Arial', '', $this->iFonteGradeNota);
                    $this->addPage();
                    $this->imprimeCabecalho();
                    $this->imprimeCabecalhoEscola();
                    $this->imprimeDadosPessoaisAluno();
                }
                if (!empty($this->ultimaEtapaCursada)) {
                    $this->imprimeTituloHistorico($this->ultimaEtapaCursada->ensino->getNome());
                } else {
                    $this->setFont('Arial', 'B', 7);
                }
                $this->imprimeCabecalhoTabela(
                    $ensino,
                    TipoEnsinoEnum::ENSINO_FUNDAMENTAL,
                    self::LARGURA_ANOS_SERIES_EF
                );
                $this->imprimeBaseComum($ensino, self::LARGURA_ANOS_SERIES_EF, TipoEnsinoEnum::ENSINO_FUNDAMENTAL);
                $this->imprimeBaseDiversificada(
                    $ensino,
                    self::LARGURA_ANOS_SERIES_EF,
                    TipoEnsinoEnum::ENSINO_FUNDAMENTAL
                );
                $this->imprimeResultadoFinal(
                    $ensino,
                    self::LARGURA_ANOS_SERIES_EF,
                    TipoEnsinoEnum::ENSINO_FUNDAMENTAL
                );
                $this->imprimeRodape(TipoEnsinoEnum::ENSINO_FUNDAMENTAL);
            }
        }
    }

    private function imprimeTituloHistorico($nomeHistorico)
    {
        $this->setY($this->getY() + 2);
        $this->setFont('Arial', 'B', 8);
        $this->cell(195, 5, $this->sTituloHistorico . ' ' . $nomeHistorico, 1, 1, 'C');
        $this->setFont('Arial', 'B', 7);
    }

    private function imprimeCabecalhoTabela($ensino, $tipoEnsino, $larguraAnosSeries)
    {
        $this->setY($this->getY() + 2);
        $this->cell($larguraAnosSeries, self::ALTURA_LINHA, 'ANOS / SÉRIES / ETAPAS', 1, 0, 'C');
        foreach ($this->dadosHistorico as $etapa) {
            if ($etapa->ensino->getTipoEnsino()->getValue() == $tipoEnsino) {
                $tamanhoFonte = $this->calculaTamanhoFonte(
                    ($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas'],
                    $this->iFonteGradeNota,
                    $etapa->sEtapa . '0000'
                );

                $this->setFontSize($tamanhoFonte);
                $this->cell(
                    ($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas'],
                    self::ALTURA_LINHA,
                    $etapa->sEtapa,
                    1,
                    0,
                    'C'
                );
            }
        }
        $this->ln();
        $this->setFontSize($this->iFonteGradeNota);
        $this->cell($larguraAnosSeries, self::ALTURA_LINHA, 'ANOS LETIVOS', 1, 0, 'C');
        foreach ($this->dadosHistorico as $etapa) {
            if ($etapa->ensino->getTipoEnsino()->getValue() == $tipoEnsino) {
                $this->cell(
                    ($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas'],
                    self::ALTURA_LINHA,
                    $etapa->iAno,
                    1,
                    0,
                    'C'
                );
            }
        }
        $this->ln();

        $this->multiCell(
            $larguraAnosSeries,
            self::ALTURA_LINHA,
            "RESULTADOS FINAIS (R.F.)\nCARGAS HORÁRIAS (C.H.)",
            1,
            'C'
        );
        $this->imprimeColunasAdicionais($ensino, $tipoEnsino, $larguraAnosSeries);
    }

    private function imprimeColunasAdicionais($ensino, $tipoEnsino, $larguraAnosSeries)
    {
        $this->setXY($larguraAnosSeries + 8, $this->getY() - self::ALTURA_LINHA * 2);
        foreach ($this->dadosHistorico as $etapa) {
            if ($etapa->ensino->getTipoEnsino()->getValue() == $tipoEnsino) {
                if (($etapa->ensino->getCodigoTipoEnsino() == 3 && $etapa->ordem > 4) || $etapa->ordem < 6) {
                    $this->cell(
                        ($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas'],
                        self::ALTURA_LINHA * 4,
                        'R.F.',
                        1,
                        0,
                        'C'
                    );
                } else {
                    $this->setFont('Arial', 'B', 6);
                    $this->cell(
                        (($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas']) / 2,
                        self::ALTURA_LINHA * 4,
                        'R.F.',
                        1,
                        0,
                        'C'
                    );
                    $this->cell(
                        (($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas']) / 2,
                        self::ALTURA_LINHA * 4,
                        'C.H.',
                        1,
                        0,
                        'C'
                    );
                }
            }
        }
        $this->ln();
        $this->getYInicialCamposEXP = $this->getY();
        $this->setFont('Arial', 'B', 7);
    }

    private function imprimeBaseComum($ensino, $larguraAnosSeries, $tipoEnsino)
    {
        if ($ensino['qtdDisciplinasBaseComum'] > 0) {
            $getYBnccPadrao = $this->getY();
            $alturaInicial = $this->getY();
            $this->setXY(18, $this->getY() - self::ALTURA_LINHA * 2);
            $this->image(
                'imagens/educacao/historico/diplomado.png',
                9,
                $this->getY() + 1,
                8,
                8
            );
            $this->multiCell(
                ($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.4,
                self::ALTURA_LINHA,
                "ÁREAS DE\nCONHECIMENTO",
                1,
                'C'
            );
            $this->setXY(
                (18 + ($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.4),
                $this->getY() - self::ALTURA_LINHA * 2
            );
            $this->multiCell(
                (($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.6),
                self::ALTURA_LINHA,
                "COMPONENTES\nCURRICULARES",
                1,
                'C'
            );

            foreach ($ensino['BASE COMUM'] as $keyAreaConhecimento => $disciplinas) {
                $this->imprimeDisciplinas(
                    $keyAreaConhecimento,
                    $disciplinas,
                    $ensino,
                    $alturaInicial,
                    $larguraAnosSeries,
                    $ensino['qtdDisciplinasBaseComum'],
                    $tipoEnsino
                );
                $alturaInicial = $this->getY();
            }
            $this->imprimeQuadroBase($getYBnccPadrao, "BASE NACIONAL\nCOMUM CURRICULAR");
        }
    }

    private function imprimeBaseDiversificada($ensino, $larguraAnosSeries, $tipoEnsino)
    {
        if ($ensino['qtdDisciplinasBaseDiversificada'] > 0) {
            $getYDiversificada = $this->getY();
            $alturaInicial = $this->getY();
            foreach ($ensino['BASE DIVERSIFICADA'] as $keyAreaConhecimento => $disciplinas) {
                $this->imprimeDisciplinas(
                    $keyAreaConhecimento,
                    $disciplinas,
                    $ensino,
                    $alturaInicial,
                    $larguraAnosSeries,
                    $ensino['qtdDisciplinasBaseDiversificada'],
                    $tipoEnsino
                );
                $alturaInicial = $this->getY();
            }
            $this->imprimeQuadroBase($getYDiversificada, "BASE\nDIVERSIFICADA");
        }
    }

    private function imprimeDisciplinas(
        $keyAreaConhecimento,
        $disciplinas,
        $ensino,
        $yInicial,
        $larguraAnosSeries,
        $qtdDisciplinas,
        $tipoEnsino
    ) {
        $tamanhoFonte = $this->calculaTamanhoFonte(
            ($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.30,
            $this->iFonteGradeNota,
            mb_strtoupper($keyAreaConhecimento, 'ISO-8859-1')
        );
        $this->setFont('Arial', 'B', $tamanhoFonte);
        $this->setXY(18, $yInicial);
        $alturaAreaConhecimento =
            $qtdDisciplinas >= 3 ?
                count($disciplinas) * self::ALTURA_LINHA :
                self::ALTURA_LINHA * 3;
        $this->cell(
            ($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.4,
            $alturaAreaConhecimento,
            mb_strtoupper($keyAreaConhecimento, 'ISO-8859-1'),
            1,
            1,
            'C'
        );
        $this->setY($yInicial);
        foreach ($disciplinas as $disciplinaConfig) {
            $this->imprimeDadosDisciplina($disciplinaConfig, $ensino, $qtdDisciplinas, $larguraAnosSeries, $tipoEnsino);
        }
    }

    private function imprimeDadosDisciplina(
        $disciplinaConfig,
        $ensino,
        $qtdDisciplinas,
        $larguraAnosSeries,
        $tipoEnsino
    ) {
        $tamanhoFonte = $this->calculaTamanhoFonte(
            ($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.55,
            $this->iFonteGradeNota,
            mb_strtoupper($disciplinaConfig . '000', 'ISO-8859-1')
        );
        $this->setFont('Arial', '', $tamanhoFonte);
        $this->setX((18 + ($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.4));
        $alturaDisciplina =
            $qtdDisciplinas >= 3 ?
            self::ALTURA_LINHA :
            self::ALTURA_LINHA * 3 / $qtdDisciplinas;
        $this->cell(
            (($larguraAnosSeries - self::LARGURA_BASE_CURRICULAR) * 0.6),
            $alturaDisciplina,
            mb_strtoupper($disciplinaConfig, 'ISO-8859-1'),
            1,
            0,
            'C'
        );

        foreach ($this->dadosHistorico as $etapa) {
            $this->setFont('Arial', '', $this->iFonteGradeNota);
            if ($etapa->ensino->getTipoEnsino()->getValue() == $tipoEnsino) {
                $this->imprimeResultadosDisciplinas($etapa, $disciplinaConfig, $ensino, $alturaDisciplina, $tipoEnsino);
            }
        }
        $this->ln();
    }

    private function imprimeResultadosDisciplinas($etapa, $disciplinaConfig, $ensino, $alturaDisciplina, $tipoEnsino)
    {
        $larguraEtapa = ($tipoEnsino == TipoEnsinoEnum::ENSINO_INFANTIL)
            ? self::LARGURA_ANOS_SERIES_EI
            : self::LARGURA_ANOS_SERIES_EF;

        $larguraCelula = ($this->getW() - $larguraEtapa - 15) / $ensino['qtdEtapas'];
        $larguraCelulaDividida = $larguraCelula / 2;
        $naoCursouDiscNaEtapa = true;

        if (property_exists($etapa, 'aDisciplinas')) {
            foreach ($etapa->aDisciplinas as $areaConhecimento) {
                foreach ($areaConhecimento as $disciplina) {
                    if ($disciplina->sDisciplina === $disciplinaConfig) {
                        $naoCursouDiscNaEtapa = false;

                        if ($tipoEnsino === TipoEnsinoEnum::ENSINO_INFANTIL) {
                            /**
                             * Condições de entrada no IF:
                             * Se o filtro for:
                             *  - NÃO EXIBE CAMPOS DE EXPERIÊNCIA;
                             *  - EXIBE SOMENTE REDE E A ETAPA FOR CURSADA FORA DA REDE;
                             *  - EXIBE SOMENTE FORA E A ETAPA FOR CURSADA NA REDE;
                             *  - SE O RESULTADO DA ETAPA FOR M, D, C (Matriculado, Desistente ou Cursando).
                             */
                            if ($this->exibeCamposExperienciaEI == CamposExperienciaEnum::NAO_EXIBE ||
                                ($this->exibeCamposExperienciaEI == CamposExperienciaEnum::EXIBE_REDE
                                    && $etapa->etapaForaRede) ||
                                ($this->exibeCamposExperienciaEI == CamposExperienciaEnum::EXIBE_FORA
                                    && !$etapa->etapaForaRede) ||
                                in_array($etapa->resultadoEtapaAno, ['M', 'T', 'C'])
                            ) {
                                $this->salvaCoordenadasCelula($etapa, $larguraCelula);
                                $this->imprimeCelulaEmBranco($larguraCelula, $alturaDisciplina);
                            } else {
                                $this->imprimeCelula($larguraCelula, $alturaDisciplina, $disciplina->mAvaliacao, 7);
                            }
                        } else {
                            if (in_array($etapa->resultadoEtapaAno, ['M', 'T', 'C'])) {
                                $this->salvaCoordenadasCelula($etapa, $larguraCelula);
                                $this->imprimeCelulaEmBranco($larguraCelula, $alturaDisciplina);
                            } elseif (($etapa->ensino->getCodigoTipoEnsino() == 3 && $etapa->ordem > 4) ||
                                $etapa->ordem >= 6) {
                                /* Imprime o resultado */
                                $this->imprimeCelula(
                                    $larguraCelulaDividida,
                                    $alturaDisciplina,
                                    $disciplina->mAvaliacao,
                                    6
                                );
                                /* Imprime a carga horária */
                                $this->imprimeCelula(
                                    $larguraCelulaDividida,
                                    $alturaDisciplina,
                                    $disciplina->iCargaHoraria,
                                    6
                                );
                            } else {
                                $this->imprimeCelula($larguraCelula, $alturaDisciplina, $disciplina->mAvaliacao, 7);
                            }
                        }
                    }
                }
            }
        }

        if ($naoCursouDiscNaEtapa) {
            if ($tipoEnsino === TipoEnsinoEnum::ENSINO_INFANTIL) {
                if ($this->exibeCamposExperienciaEI == CamposExperienciaEnum::NAO_EXIBE ||
                    ($this->exibeCamposExperienciaEI == CamposExperienciaEnum::EXIBE_REDE && $etapa->etapaForaRede) ||
                    ($this->exibeCamposExperienciaEI == CamposExperienciaEnum::EXIBE_FORA && !$etapa->etapaForaRede) ||
                    in_array($etapa->resultadoEtapaAno, ['M', 'T', 'C'])
                ) {
                    $this->salvaCoordenadasCelula($etapa, $larguraCelula);
                    $this->imprimeCelulaEmBranco($larguraCelula, $alturaDisciplina);
                } else {
                    $this->imprimeCelula($larguraCelula, $alturaDisciplina, '-', 7);
                }
            } else {
                if (in_array($etapa->resultadoEtapaAno, ['M', 'T', 'C'])) {
                    $this->salvaCoordenadasCelula($etapa, $larguraCelula);
                    $this->imprimeCelulaEmBranco($larguraCelula, $alturaDisciplina);
                } elseif ($etapa->ordem >= 6) {
                    $this->imprimeCelula($larguraCelulaDividida, $alturaDisciplina, '-', 7);
                    $this->imprimeCelula($larguraCelulaDividida, $alturaDisciplina, '-', 7);
                } else {
                    $this->imprimeCelula($larguraCelula, $alturaDisciplina, '-', 7);
                }
            }
        }
    }

    private function imprimeCelulaEmBranco($largura, $altura)
    {
        $this->cell($largura, $altura, '', 'LR', 0, 'C');
    }

    private function salvaCoordenadasCelula($etapa, $larguraCelula)
    {
        // Salvo coordenadas da coluna em branco
        if (!array_key_exists($etapa->iEtapa, $this->colunasEmBranco)) {
            $this->colunasEmBranco[$etapa->iEtapa] = [];
        };
        // Atribuindo coordenadas e valores que serão impressos
        if (in_array($etapa->resultadoEtapaAno, ['M', 'T', 'C'])) {
            switch ($etapa->resultadoEtapaAno) {
                case 'M':
                    $this->colunasEmBranco[$etapa->iEtapa]['sImprimir'] = 'MATRICULADO';
                    break;
                case 'T':
                    $this->colunasEmBranco[$etapa->iEtapa]['sImprimir'] = 'DESISTENTE';
                    break;
                default:
                    $this->colunasEmBranco[$etapa->iEtapa]['sImprimir'] = 'CURSANDO';
            }
        } else {
            switch (trim($etapa->resultadoEtapaAno)) {
                case 'A':
                    $this->colunasEmBranco[$etapa->iEtapa]['sImprimir'] = 'APTO';
                    break;
                case '-':
                    $this->colunasEmBranco[$etapa->iEtapa]['sImprimir'] = '-';
                    break;
                default:
                    $this->colunasEmBranco[$etapa->iEtapa]['sImprimir'] = 'INAPTO';
            }
        }
        // Coordernada X
        if (!array_key_exists('coordX', $this->colunasEmBranco[$etapa->iEtapa])) {
            $this->colunasEmBranco[$etapa->iEtapa]['coordX'] = '';
        };
        $this->colunasEmBranco[$etapa->iEtapa]['coordX'] = $this->getX();
        // Coordenada Y
        $this->colunasEmBranco[$etapa->iEtapa]['coordY'][] = $this->getY();
        // Largura Célula
        $this->colunasEmBranco[$etapa->iEtapa]['larguraCell'] = $larguraCelula;
        // Tipo Ensino
        $this->colunasEmBranco[$etapa->iEtapa]['tipoEnsino'] = $etapa->ensino->getTipoEnsino()->getValue();
    }

    private function imprimeCelula($largura, $altura, $conteudo, $tamanhoFonte)
    {
        $this->setFont('Arial', '', $tamanhoFonte);
        $this->cell(
            $largura,
            $altura,
            $conteudo ? ($conteudo === 'Parecer' ? 'PD' : $conteudo) : '-',
            1,
            0,
            'C'
        );
    }

    private function imprimeQuadroBase($alturaInicial, $nomeBase)
    {
        $calcTamanhoFonte = $nomeBase == "BASE\nDIVERSIFICADA" ? 'DIVERSIFICADA   ' : 'COMUM CURRICULAR   ';
        $alturaQuadroBase = $this->getY() - $alturaInicial;
        $tamanhoFonte = $this->calculaTamanhoFonte(
            $alturaQuadroBase * 0.9,
            $this->iFonteGradeNota,
            mb_strtoupper($calcTamanhoFonte, 'ISO-8859-1')
        );

        $this->setFont(
            'Arial',
            'B',
            $tamanhoFonte
        );
        $this->setY($alturaInicial);
        $this->vcell(self::LARGURA_BASE_CURRICULAR, $alturaQuadroBase, $nomeBase, 1, 1, 'C');
    }

    private function imprimeResultadoFinal($ensino, $larguraAnosSeries, $tipoEnsino)
    {
        if (count($this->colunasEmBranco) > 0) {
            $this->imprimeColunaEtapaVertical($this->getY(), $tipoEnsino);
            $this->setY($this->getY() + 2);
        }
        $this->setFont('Arial', 'B', $this->iFonteGradeNota);
        $larguraCelula = ($this->getW() - $larguraAnosSeries - 15) / $ensino['qtdEtapas'];
        $countEtapa = 0;
        $this->cell($larguraAnosSeries, self::ALTURA_LINHA, 'RESULTADO FINAL', 1, 1, 'L');
        $this->cell($larguraAnosSeries, self::ALTURA_LINHA, 'CARGA HORÁRIA ANUAL', 1, 1, 'L');
        if ($this->exibePorcentagemFreq) {
            $this->cell($larguraAnosSeries, self::ALTURA_LINHA, 'PORCENTAGEM DE FREQUÊNCIA (%)', 1, 1, 'L');
        }
        // Calcula o total de linhas que devem ser consideradas para setar no setY
        $this->iTotalLinhas = $this->exibePorcentagemFreq ? 4 : 3;

        $this->cell($larguraAnosSeries, self::ALTURA_LINHA, 'DIAS LETIVOS ANUAIS', 1, 1, 'L');
        $this->setXY($larguraAnosSeries + 8, $this->getY() - self::ALTURA_LINHA * $this->iTotalLinhas);
        $this->setFont('Arial', '', $this->iFonteGradeEtapa);
        foreach ($this->dadosHistorico as $etapa) {
            if ($etapa->ensino->getTipoEnsino()->getValue() == $tipoEnsino) {
                $this->cell(
                    $larguraCelula,
                    self::ALTURA_LINHA,
                    $etapa->sResultado &&
                        ($etapa->etapaForaRede ||
                        $this->exibeResultadoFinalEtapaRede) ?
                        $etapa->sResultado :
                        '-',
                    1,
                    1,
                    'C'
                );
                $this->setX(8 + $larguraAnosSeries + $larguraCelula * $countEtapa);
                $this->cell(
                    $larguraCelula,
                    self::ALTURA_LINHA,
                    $etapa->iCargaHoraria ? $etapa->iCargaHoraria : '-',
                    1,
                    1,
                    'C'
                );
                $this->setX(8 + $larguraAnosSeries + $larguraCelula * $countEtapa);
                if ($this->exibePorcentagemFreq) {
                    $this->cell(
                        $larguraCelula,
                        self::ALTURA_LINHA,
                        $etapa->nPercentualFalta ? $etapa->nPercentualFalta : '-',
                        1,
                        1,
                        'C'
                    );
                    $this->setX(8 + $larguraAnosSeries + $larguraCelula * $countEtapa);
                }
                $this->cell(
                    $larguraCelula,
                    self::ALTURA_LINHA,
                    $etapa->iDiasLetivos ? $etapa->iDiasLetivos : '-',
                    1,
                    1,
                    'C'
                );
                $countEtapa++;
                $this->setXY(
                    8 + $larguraAnosSeries + $larguraCelula * $countEtapa,
                    $this->getY() - self::ALTURA_LINHA * $this->iTotalLinhas
                );
            }
        }
    }

    private function imprimeRodape($tipoEnsino)
    {
        $this->setFont('Arial', '', $this->iFonteObservacao);
        $this->setXY(8, $this->getY() + self::ALTURA_LINHA * $this->iTotalLinhas + 2);
        $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $this->sRodape, 1, 'J');

        $this->imprimeResumoEtapas($tipoEnsino);
    }

    private function imprimeResumoEtapas($tipoEnsino)
    {
        $this->verificaQuebraDePagina();
        $this->setFont('Arial', 'B', $this->iFonteGradeEtapa);
        $this->setY($this->getY() + 2);
        $this->cell(($this->getW() - 15) * 0.2, self::ALTURA_LINHA, 'ANO/SÉRIE', 1, 0, 'C');
        $this->cell(($this->getW() - 15) * 0.4, self::ALTURA_LINHA, 'ESTABELECIMENTO DE ENSINO', 1, 0, 'C');
        $this->cell(($this->getW() - 15) * 0.3, self::ALTURA_LINHA, 'LOCALIDADE', 1, 0, 'C');
        $this->cell(($this->getW() - 15) * 0.1, self::ALTURA_LINHA, 'ESTADO', 1, 1, 'C');
        foreach ($this->dadosHistorico as $etapa) {
            $this->verificaQuebraDePagina();
            if ($etapa->ensino->getTipoEnsino()->getValue() == $tipoEnsino) {
                $tamanhoFonte =
                    $this->calculaTamanhoFonte(($this->getW() - 15) * 0.2, $this->iFonteGradeEtapa, $etapa->sEtapa);
                $this->setFont('Arial', '', $tamanhoFonte);
                $this->cell(($this->getW() - 15) * 0.2, self::ALTURA_LINHA, $etapa->sEtapa ?: '-', 1, 0, 'C');
                $tamanhoFonte =
                    $this->calculaTamanhoFonte(($this->getW() - 15) * 0.4, $this->iFonteGradeEtapa, $etapa->sEscola);
                $this->setFont('Arial', '', $tamanhoFonte);
                $this->cell(
                    ($this->getW() - 15) * 0.4,
                    self::ALTURA_LINHA,
                    $etapa->sEscola ?:
                        '-',
                    1,
                    0,
                    'C'
                );
                $tamanhoFonte =
                    $this->calculaTamanhoFonte(
                        ($this->getW() - 15) * 0.3,
                        $this->iFonteGradeEtapa,
                        $etapa->resultadoEtapaAno
                    );
                $this->setFont('Arial', '', $tamanhoFonte);
                $this->cell(
                    ($this->getW() - 15) * 0.3,
                    self::ALTURA_LINHA,
                    $etapa->sMunicipio ?:
                        '-',
                    1,
                    0,
                    'C'
                );
                $tamanhoFonte =
                    $this->calculaTamanhoFonte(($this->getW() - 15) * 0.1, $this->iFonteGradeEtapa, $etapa->sUF);
                $this->setFont('Arial', '', $tamanhoFonte);
                $this->cell(
                    ($this->getW() - 15) * 0.1,
                    self::ALTURA_LINHA,
                    $etapa->sUF ?:
                        '-',
                    1,
                    1,
                    'C'
                );
            }
        }
        $this->imprimeObservacao($tipoEnsino);
    }

    private function imprimeObservacao($tipoEnsino)
    {
        /**
         * Aqui é responsável por imprimir a observação cadastrada
         * no modelo do relatório
        */
        $this->setY($this->getY() + 2);
        $aObservacao = $this->transformaObsEmArray($this->sObservacao);
        $bordas = 'LTR';
        foreach ($aObservacao as $obs) {
            if ($this->verificaAlturaRestanteObs()) {
                $this->setFont('Arial', '', $this->iFonteObservacao);
                $bordas = 'LTR';
                $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $obs, $bordas, 'J');
            } else {
                $this->setFont('Arial', '', $this->iFonteObservacao);
                $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $obs, $bordas, 'J');
            }
            $bordas = 'LR';
        }

        /**
         * Aqui é responsável por imprimir a observação do curso
         */
        if (is_array($this->aObservacoesCurso) && count($this->aObservacoesCurso) > 0) {
            foreach ($this->aObservacoesCurso as $keyCurso => $obsCurso) {
                $isCursoPertenceEtapa = CursoEdu::where('ed29_i_codigo', $keyCurso)
                    ->whereHas('ensino', function ($query) use ($tipoEnsino) {
                        $query->where('ed10_tipo', $tipoEnsino);
                    })
                    ->exists();

                if ($isCursoPertenceEtapa) {
                    $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 'J');
                    $aObsCurso = $this->transformaObsEmArray($obsCurso);
                    foreach ($aObsCurso as $obs) {
                        if ($this->verificaAlturaRestanteObs()) {
                            $this->setFont('Arial', '', $this->iFonteObservacao);
                            $bordas = 'LTR';
                            $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $obs, $bordas, 'J');
                        } else {
                            $this->setFont('Arial', '', $this->iFonteObservacao);
                            $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $obs, $bordas, 'J');
                        }
                        $bordas = 'LR';
                    }
                }
            }
            $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 'J');
        }

        if (is_array($this->obsPorEtapa) && count($this->obsPorEtapa) > 0) {
            foreach ($this->obsPorEtapa as $keyEnsino => $observacoes) {
                if ($keyEnsino == $tipoEnsino) {
                    foreach ($observacoes as $keyEtapa => $obsEtapa) {
                        $this->setFont('Arial', 'B', $this->iFonteObservacao);
                        if ($this->exibeEtapaObs) {
                            $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $keyEtapa, $bordas, 'J');
                        }
                        $aObsEtapa = $this->transformaObsEmArray($obsEtapa);
                        foreach ($aObsEtapa as $obs) {
                            if ($this->verificaAlturaRestanteObs()) {
                                $this->setFont('Arial', '', $this->iFonteObservacao);
                                $bordas = 'LTR';
                                $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $obs, $bordas, 'J');
                            } else {
                                $this->setFont('Arial', '', $this->iFonteObservacao);
                                $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, $obs, $bordas, 'J');
                            }
                            $bordas = 'LR';
                        }
                        $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 'J');
                    }
                }
            }
        }

        if ($this->alunoAprovadoComProgressao && $tipoEnsino == TipoEnsinoEnum::ENSINO_FUNDAMENTAL) {
            $this->imprimeObservacaoDaProgressao();
        }

        // Aqui eu aumento a área do quadro de observação para que ele ocupe
        // a folha e deixe somente o espaço da data e assinatura
        if ($this->getAvailHeight() > $this->getH() * 0.15 && $this->preencheQuadroObservacao) {
            while ($this->getY() < $this->getH() * 0.85) {
                $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 'J');
            }
        }

        $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 4, '', 'LBR', 'J');

        $this->imprimeDataExpedicao();
    }

    private function imprimeDataExpedicao()
    {
        $this->verificaQuebraDePagina();
        $this->setY($this->getY() + 2);
        $this->setFont('Arial', 'B', 7);
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA, 'Expedido em: ', 'LTB', 0, 'R');
        $this->setFont('Arial', '', 7);
        $this->cell(
            ($this->getW() - 15) / 2,
            self::ALTURA_LINHA,
            $this->sDataEmissao ?: Carbon::now()->format('d/m/Y'),
            'RTB',
            1,
            'L'
        );
        $this->imprimeAssinaturas();
    }

    private function imprimeAssinaturas()
    {
        $this->verificaQuebraDePagina();
        $this->setY($this->getY() + 2);
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA, '', 'LT', 0, 'C');
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA, '', 'RT', 1, 'C');
        $this->setFont('Arial', 'B', 7);
        $this->cell(
            ($this->getW() - 15) / 2,
            2,
            '_______________________________________________',
            'L',
            0,
            'C'
        );
        $this->cell(
            ($this->getW() - 15) / 2,
            2,
            '_______________________________________________',
            'R',
            1,
            'C'
        );
        $this->setFont('Arial', '', 7);
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA - 1, $this->sAssinaturaSecretario, 'L', 0, 'C');
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA - 1, $this->sAssinaturaDiretor, 'R', 1, 'C');
        $this->setFont('Arial', 'B', 7);
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA - 1, 'Secretário(a)', 'LB', 0, 'C');
        $this->cell(($this->getW() - 15) / 2, self::ALTURA_LINHA - 1, 'Diretor(a)', 'RB', 1, 'C');
        $this->setFont('Arial', '', 7);
    }

    private function verificaAlturaRestanteObs()
    {
        if ($this->getAvailHeight() <= 20) {
            $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 4, '', 'LBR', 'J');
            $this->addPage();
            $this->setY($this->getY() + 2);
            return true;
        }
        return false;
    }

    private function transformaObsEmArray($observacao)
    {
        return explode("\n", $observacao);
    }

    private function imprimeColunaEtapaVertical($coordenadaYFinal, $tipoEnsino)
    {
        $this->setFont('Arial', 'B', $this->iFonteGradeNota);
        foreach ($this->colunasEmBranco as $etapa) {
            if ($etapa['tipoEnsino'] == $tipoEnsino) {
                $this->setY(min($etapa['coordY']) + 2);
                $aImprimir = str_split($etapa['sImprimir']);
                $qtdCaracteres = count($aImprimir);
                $areaYImpressao = ($coordenadaYFinal) - min($etapa['coordY']) - 4;
                $alturaLinha = $areaYImpressao / $qtdCaracteres;
                foreach ($aImprimir as $letra) {
                    $this->setX($etapa['coordX']);
                    $this->cell($etapa['larguraCell'], $alturaLinha, $letra, 0, 1, 'C');
                }
            }
        }
    }

    private function verificaQuebraDePagina()
    {
        if ($this->getAvailHeight() <= 10) {
            $this->addPage();
        }
    }

    private function calculaTamanhoFonte($largura, $tamanhoFonte, $conteudo)
    {
        $tamCalculado = $this->getFonteSizeByWidthCell($largura, $tamanhoFonte, $conteudo);
        return min($tamCalculado, $tamanhoFonte);
    }

    private function imprimeObservacaoDaProgressao()
    {
        $this->setFont('Arial', 'B', $this->iFonteObservacao);
        $this->cell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 1, 'L');
        $this->cell($this->getW() - 15, self::ALTURA_LINHA - 1, 'Observações adicionais:', 'LR', 1, 'L');
        $alinhamentoInicial = $this->getX();
        foreach ($this->aObsProgressoes as $key => $observacoes) {
            if ($key == 'iniciadas' && count($observacoes) > 0) {
                $alturaInicial = $this->getY();
                $this->cell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 1, 'L');
                $this->setY($alturaInicial);
                $this->setX($alinhamentoInicial + 2);
                $this->cell(
                    $this->getW() - 15,
                    self::ALTURA_LINHA - 1,
                    '- Aprovações com progressões parciais:',
                    '',
                    1,
                    'L'
                );
                $this->setFont('Arial', '', $this->iFonteObservacao);
                foreach ($observacoes as $obsIniciada) {
                    $alturaInicial = $this->getY();
                    $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, "", 'LR', 'L');
                    $this->setY($alturaInicial);
                    $this->setX($alinhamentoInicial + 5);
                    $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, "· {$obsIniciada}", '', 1, 'L');
                }
            } elseif ($key == 'concluidas' && count($observacoes) > 0) {
                $this->setFont('Arial', 'B', $this->iFonteObservacao);
                $alturaInicial = $this->getY();
                $this->cell($this->getW() - 15, self::ALTURA_LINHA - 1, '', 'LR', 1, 'L');
                $this->setY($alturaInicial);
                $this->setX($alinhamentoInicial + 2);
                $this->cell($this->getW() - 15, self::ALTURA_LINHA - 1, '- Progressões concluídas:', '', 1, 'L');
                $this->setFont('Arial', '', $this->iFonteObservacao);
                foreach ($observacoes as $obsConcluidas) {
                    $alturaInicial = $this->getY();
                    $this->cell($this->getW() - 15, self::ALTURA_LINHA - 1, "", 'LR', 1, 'L');
                    $this->setY($alturaInicial);
                    $this->setX($alinhamentoInicial + 5);
                    $this->multiCell($this->getW() - 15, self::ALTURA_LINHA - 1, "· {$obsConcluidas}", '', 'L');
                }
            }
        }
    }
}
