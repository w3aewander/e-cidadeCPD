<?php

namespace App\Domain\Patrimonial\PNCP\Relatorios;

use ECidade\File\Csv\Dumper\Dumper;

class ItensPlanoCsv extends Dumper
{
    /**
     * @var array
     */
    private $dados;
    private $dadosImprimir;
    private $quantidade = 0;
    private $valorUnitario = 0;
    private $valorTotal = 0;
    private $valorOrcamento = 0;

    public function __construct()
    {
        $this->dadosImprimir = [];
    }

    public function addDados(array $dados)
    {
        $this->dados = $dados;
    }

    /**
     * @return string[]
     */
    public function emitir()
    {

        $this->montaCabecalho();
        foreach ($this->dados as $item) {
            $this->montaLinha($item);
            $this->somador($item->quantidade, $item->valorUnitario, $item->valorTotal, $item->valorOrcamentoExercicio);
        }

        $this->totalizador($this->quantidade, $this->valorUnitario, $this->valorTotal, $this->valorOrcamento);

        return $this->imprimir();
    }

    /**
     * @param $qtd
     * @param $vlrUni
     * @param $vlrTotal
     * @param $vlrOrcamento
     * @return void
     */
    private function somador($qtd, $vlrUni, $vlrTotal, $vlrOrcamento)
    {
        $this->quantidade += $qtd;
        $this->valorUnitario += $vlrUni;
        $this->valorTotal += $vlrTotal;
        $this->valorOrcamento += $vlrOrcamento;
    }

    /**
     * @param $item
     * @param $cor
     * @return void
     */
    private function montaLinha($item)
    {
        $this->dadosImprimir[] = [
            $item->numeroItem,
            $item->descricao,
            $item->categoriaItemDescricao,
            $item->unidadeFornecimento,
            $item->quantidade,
            number_format($item->valorUnitario, 2, ',', '.'),
            number_format($item->valorTotal, 2, ',', '.'),
            number_format($item->valorOrcamentoExercicio, 2, ',', '.'),
            $item->unidadeRequisitante
        ];
    }

    /**
     * @param $quantidade
     * @param $valorUnitario
     * @param $valorTotal
     * @param $valorOrcamento
     * @return void
     */
    private function totalizador($quantidade, $valorUnitario, $valorTotal, $valorOrcamento)
    {

        $this->dadosImprimir[] = [
            'Totalizador:',
            '',
            '',
            '',
            $quantidade,
            number_format($valorUnitario, 2, ',', '.'),
            number_format($valorTotal, 2, ',', '.'),
            number_format($valorOrcamento, 2, ',', '.'),
            ''
        ];
    }

    /**
     * @return void
     */
    private function montaCabecalho()
    {
        $this->dadosImprimir[] = [
            'Item',
            'Descrição Item',
            'Categoria Item',
            'Unidade Fornecimento',
            'Quantidade',
            'Valor Unitário',
            'Valor Total',
            'Valor Orçamento',
            'Unidade Requisitante'
        ];
    }

    /**
     * @return string[]
     */
    private function imprimir()
    {
        $fileName = 'tmp/itens_do_plano' . time() . '.csv';
        $this->dumpToFile($this->dadosImprimir, $fileName);

        return [
            "name" => "Csv dos Itens do Plano de Contratação",
            "path" => $fileName,
            'pathExterno' => ECIDADE_REQUEST_PATH . $fileName
        ];
    }
}
