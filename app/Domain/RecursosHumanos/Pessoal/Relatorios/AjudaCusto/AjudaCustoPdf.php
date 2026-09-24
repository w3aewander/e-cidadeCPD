<?php

namespace App\Domain\RecursosHumanos\Pessoal\Relatorios\AjudaCusto;

use ECidade\Pdf\Pdf;

class AjudaCustoPdf extends Pdf
{
    protected $data;

    public function __construct(array $data)
    {
        parent::__construct('L');
        $this->data = $data;
        $this->addTitulo('Relatório de Ajuda de Custo');
    }

    public function emitir()
    {
        $this->initEmissao();
        $this->setFillColor(200);
        $this->imprimir();
        $fileName = 'tmp/ajuda-custo.pdf';
        $this->output('F', $fileName);

        return [
            "name" => "Relatório de Ajuda de Custo PDF",
            "path" => $fileName,
            'pathExterno' => ECIDADE_REQUEST_PATH . $fileName
        ];
    }

    private function initEmissao()
    {
        $this->mostrarRodape();
        $this->mostrarTotalDePaginas();
        $this->setFont('Arial', 'B', 9);
        $this->exibeHeader();
    }

    public function imprimir()
    {
        $this->cabecalho();
        $this->setFont('Arial', '', 6);

        $matriculasUnicas = [];

        foreach ($this->data as $impressao) {
            if ($this->GetY() > 190) {
                $this->cabecalho();
                $this->setFont('Arial', '', 6);
            }

            // imprime linha
            $this->cell(16, 5, $impressao['matricula'], 1, 0, 'C');
            $this->cell(52, 5, $impressao['nome'], 1, 0, 'C');
            $this->cell(16, 5, $impressao['cpf_servidor'], 1, 0, 'C');
            $this->cell(48, 5, $impressao['nome_dependente'], 1, 0, 'C');
            $this->cell(16, 5, $impressao['cpf_dependente'], 1, 0, 'C');
            $this->cell(22, 5, $impressao['local'], 1, 0, 'C');
            $this->cell(47, 5, $impressao['especializacao'], 1, 0, 'C');
            $this->cell(55, 5, $impressao['unidade_ensino'], 1, 0, 'C');
            $this->cell(8, 5, $impressao['quantidade'], 1, 1, 'C');

            $matriculasUnicas[(string)$impressao['matricula']] = true;
        }

        $totalFuncionarios = count($matriculasUnicas);

        $this->ln(5);
        $this->setFont('Arial', 'B', 7);
        $this->cell(272, 5, 'Total de Funcionários', 1, 0, 'R');
        $this->cell(8, 5, $totalFuncionarios, 1, 1, 'C');
    }
    public function cabecalho()
    {
        $this->addPage();
        $this->setFont('Arial', 'B', 7);
        $this->cell(16, 5, 'Matrícula', 1, 0, 'C', 1);
        $this->cell(52, 5, 'Nome Servidor', 1, 0, 'C', 1);
        $this->cell(16, 5, 'CPF', 1, 0, 'C', 1);
        $this->cell(48, 5, 'Dependente', 1, 0, 'C', 1);
        $this->cell(16, 5, 'CPF Dep.', 1, 0, 'C', 1);
        $this->cell(22, 5, 'Local', 1, 0, 'C', 1);
        $this->cell(47, 5, 'Especialização', 1, 0, 'C', 1);
        $this->cell(55, 5, 'Unidade de Ensino', 1, 0, 'C', 1);
        $this->cell(8, 5, 'Qtd.', 1, 1, 'C', 1);
    }
}
