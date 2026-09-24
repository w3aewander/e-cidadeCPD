<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class RelatorioAlunosRacaCorXls extends EstatisticaAlunosRacaCor {

    private $lPercentual = true;
    private $spreadsheet;
    private $sheet;
    private $linhaAtual;

    public function __construct(Calendario $oCalendario, array $aEtapa, Escola $oEscola, Spreadsheet $spreadsheetExistente = null) {
        // Valida se $aEtapa é um array e não está vazio
        if (!is_array($aEtapa) || empty($aEtapa)) {
            throw new InvalidArgumentException('O parâmetro $aEtapa deve ser um array não vazio.');
        }

        // Chama o construtor da classe pai (EstatisticaAlunosRacaCor)
        parent::__construct($oCalendario, $aEtapa, $oEscola);

        // Obtém os dados estatísticos
        $this->getEstatisticaAlunosMatriculados();
        $this->getPercentual();

        // Inicializa a planilha
        if ($spreadsheetExistente instanceof Spreadsheet) {
            // Usa a planilha existente
            $this->spreadsheet = $spreadsheetExistente;
            $this->sheet = $this->spreadsheet->getActiveSheet();
        } else {
            // Cria uma nova planilha
            $this->spreadsheet = new Spreadsheet();
            $this->sheet = $this->spreadsheet->getActiveSheet();
        }

        $this->linhaAtual = 1; // Valor padrão, será sobrescrito se setLinhaInicial for chamado
    }

    /**
     * Define a linha inicial para o relatório.
     *
     * @param int $linhaInicial A linha inicial.
     */
    public function setLinhaInicial($linhaInicial) {
        $this->linhaAtual = $linhaInicial;
    }

    /**
     * Retorna a linha atual após adicionar os dados.
     *
     * @return int
     */
    public function getLinhaAtual() {
        return $this->linhaAtual;
    }

    /**
     * Adiciona o cabeçalho do relatório de raça/cor.
     */
    private function adicionarCabecalho() {
        $cabecalho = [
            'Etapa', 'Branca', 'Preta', 'Parda', 'Amarela', 'Indígena', 'Não Declarada'
        ];

        $cabecalho = array_map(function($v) {
            return mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
          }, $cabecalho);

        // Aplica formatação ao cabeçalho
        $this->sheet->fromArray($cabecalho, null, 'A' . $this->linhaAtual);
        $this->sheet->getStyle('A' . $this->linhaAtual . ':G' . $this->linhaAtual)
            ->getFont()->setBold(true); // Negrito no cabeçalho
        $this->linhaAtual++;
    }

    /**
     * Gera o relatório de raça/cor em XLS.
     */
    public function gerarXls() {
        // Adiciona o cabeçalho
        $this->adicionarCabecalho();

        // Adiciona os dados
        foreach ($this->aEnsino as $oEnsino) {
            $this->sheet->setCellValue('A' . $this->linhaAtual, mb_convert_encoding($oEnsino->sNome,'UTF-8', 'ISO-8859-1'));
            $this->linhaAtual++;

            foreach ($oEnsino->aEtapa as $oEtapa) {
                $this->sheet->setCellValue('A' . $this->linhaAtual, 'Etapa: ' . mb_convert_encoding($oEtapa->sNome,'UTF-8', 'ISO-8859-1'));
                $this->linhaAtual++;

                foreach ($oEtapa->aTurmas as $oTurma) {
                    $linha = [
                        $oTurma->sTurma . ' - ' . mb_convert_encoding($oTurma->sTurno,'UTF-8', 'ISO-8859-1'),
                        $oTurma->raca_branca,
                        $oTurma->raca_preta,
                        $oTurma->raca_parda,
                        $oTurma->raca_amarela,
                        $oTurma->raca_indigena,
                        $oTurma->raca_naodeclarada
                    ];

                    $this->sheet->fromArray($linha, null, 'A' . $this->linhaAtual);
                    $this->linhaAtual++;
                }

                // Totais da Etapa
                $totalEtapa = [
                    'Total da Etapa: ' . mb_convert_encoding($oEtapa->sNome,'UTF-8', 'ISO-8859-1'),
                    $oEtapa->iTotalRaca_branca,
                    $oEtapa->iTotalRaca_preta,
                    $oEtapa->iTotalRaca_parda,
                    $oEtapa->iTotalRaca_amarela,
                    $oEtapa->iTotalRaca_indigena,
                    $oEtapa->iTotalRaca_naodeclarada
                ];

                $this->sheet->fromArray($totalEtapa, null, 'A' . $this->linhaAtual);
                $this->linhaAtual++;

                if ($this->lPercentual) {
                    $percentuaisEtapa = [
                        'Percentuais:',
                        $oEtapa->percentual_branca . '%',
                        $oEtapa->percentual_preta . '%',
                        $oEtapa->percentual_parda . '%',
                        $oEtapa->percentual_amarela . '%',
                        $oEtapa->percentual_indigena . '%',
                        $oEtapa->percentual_naodeclarada . '%'
                    ];

                    $this->sheet->fromArray($percentuaisEtapa, null, 'A' . $this->linhaAtual);
                    $this->linhaAtual++;
                }
            }

            // Totais do Ensino
            $totalEnsino = [
                'Total ' .mb_convert_encoding($oEnsino->sNome,'UTF-8', 'ISO-8859-1'),
                $oEnsino->iTotalRaca_branca,
                $oEnsino->iTotalRaca_preta,
                $oEnsino->iTotalRaca_parda,
                $oEnsino->iTotalRaca_amarela,
                $oEnsino->iTotalRaca_indigena,
                $oEnsino->iTotalRaca_naodeclarada
            ];

            $this->sheet->fromArray($totalEnsino, null, 'A' . $this->linhaAtual);
            $this->linhaAtual++;

            if ($this->lPercentual) {
                $percentuaisEnsino = [
                    'Percentuais:',
                    $oEnsino->percentual_branca . '%',
                    $oEnsino->percentual_preta . '%',
                    $oEnsino->percentual_parda . '%',
                    $oEnsino->percentual_amarela . '%',
                    $oEnsino->percentual_indigena . '%',
                    $oEnsino->percentual_naodeclarada . '%'
                ];

                $this->sheet->fromArray($percentuaisEnsino, null, 'A' . $this->linhaAtual);
                $this->linhaAtual++;
            }
        }

        // Total Geral
        $oTotalGeral = $this->getTotalGeral();
        $totalGeral = [
            'TOTAL GERAL',
            $oTotalGeral->iTotalRaca_branca,
            $oTotalGeral->iTotalRaca_preta,
            $oTotalGeral->iTotalRaca_parda,
            $oTotalGeral->iTotalRaca_amarela,
            $oTotalGeral->iTotalRaca_indigena,
            $oTotalGeral->iTotalRaca_naodeclarada
        ];

        $this->sheet->fromArray($totalGeral, null, 'A' . $this->linhaAtual);
        $this->linhaAtual++;

        if ($this->lPercentual) {
            $percentuaisGeral = [
                'Percentuais:',
                $oTotalGeral->percentual_branca . '%',
                $oTotalGeral->percentual_preta . '%',
                $oTotalGeral->percentual_parda . '%',
                $oTotalGeral->percentual_amarela . '%',
                $oTotalGeral->percentual_indigena . '%',
                $oTotalGeral->percentual_naodeclarada . '%'
            ];

            $this->sheet->fromArray($percentuaisGeral, null, 'A' . $this->linhaAtual);
        }
    }
}