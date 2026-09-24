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

namespace App\Domain\Educacao\Secretaria\Relatorios\Alunos;

use ECidade\File\Csv\Dumper\Dumper;

class AlunosEstrangeirosCSV extends Dumper
{
    protected $dados = [];

    protected $cabecalho = [
        'nome' => 'NOME',
        'pais' => 'PAIS',
        'paisAbreviatura' => 'PAIS ABREV.',
        'sexo' => 'SEXO',
        'dataNascimento' => 'NASCIMENTO',
        'dataMatricula' => 'MATRICULA',
        'dataTransferencia' => 'TRANSFERENCIA',
        'etapa' => 'ETAPA',
        'percentualFrequencia' => 'FREQ'
    ];

    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    public function emitir()
    {
        $this->setCsvControl(";", '"');
        $filename = sprintf('tmp/alunos-estrangeiros-%s.csv', time());
        $this->dumpToFile($this->organizarDados(), $filename);
        return [
            "name" => "Relatório de Alunos Estrangeiros CSV",
            "path" => $filename,
            'pathExterno' => ECIDADE_REQUEST_PATH . $filename
        ];
    }

    private function organizarDados()
    {
        $dadosImprimir = [];
        $numeroAlunos = 0;
        foreach ($this->dados as $dados) {
            $numeroAlunos += count($dados->alunosEstrangeiros);
        }
        foreach ($this->dados as $dados) {
            $dadosImprimir[] = utf8_encode($dados->escola);
            $dadosImprimir[] = 'Numero de Alunos: '. $numeroAlunos;
            $dadosImprimir[] = "Ano Letivo: {$dados->ano}";
            $dadosImprimir[] = $this->cabecalho;
            if (count($dados->alunosEstrangeiros) > 0) {
                foreach ($dados->alunosEstrangeiros as $aluno) {
                    $linha = [
                        $aluno->nome,
                        $aluno->pais,
                        $aluno->paisAbreviatura,
                        $aluno->sexo,
                        $aluno->dataNascimento,
                        $aluno->dataMatricula,
                        $aluno->dataTransferencia,
                        utf8_encode($aluno->etapa),
                        $aluno->percentualFrequencia . '%',
                    ];

                    $dadosImprimir[] = $linha;
                }
            } else {
                $dadosImprimir[] = 'NENHUM ALUNO ESTRANGEIRO NESTA ESCOLA';
            }
            $dadosImprimir[] = '';
        }
        return $dadosImprimir;
    }
}
