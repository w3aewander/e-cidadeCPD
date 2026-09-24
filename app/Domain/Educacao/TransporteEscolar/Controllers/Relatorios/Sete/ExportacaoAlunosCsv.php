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

namespace App\Domain\Educacao\TransporteEscolar\Controllers\Relatorios\Sete;

use ECidade\File\Csv\Dumper\Dumper;

class ExportacaoAlunosCsv extends Dumper
{
    private $alunos = [];

    public function __construct(array $alunos)
    {
        $this->alunos = $alunos;
    }

    public function emitir()
    {
        $filename = sprintf('tmp/alunos-%s.csv', time());
        $this->dumpToFile($this->organizarDados(), $filename);
        return [
            'name' => 'Layout exportação de alunos para o SETE ',
            'path' => $filename,
            'url' => ECIDADE_REQUEST_PATH . $filename
        ];
    }

    private function organizarDados()
    {
        $dadosImprimir = [$this->cabecalho()];

        foreach ($this->alunos as $aluno) {
            $dadosImprimir[] = [
                $aluno['ed47_v_nome'],
                (new \DateTime($aluno['ed47_d_nasc']))->format('d/m/Y'),
                $this->getSexo($aluno['ed47_v_sexo']),
                $this->getCor($aluno['ed47_c_raca']),
                $this->getZona($aluno['ed47_c_zona']),
                $this->getNivel($aluno['ed10_tipo']),
                $this->getTurno($aluno['ed57_i_turno']),
                $aluno['ed47_v_cpf'],
                $aluno['ed47_c_nomeresp'],
                $this->getGrauParentesco($aluno['ed47_parentescoresponsavel']),
                $aluno['ed47_v_ender'],
                $aluno['tre04_latitude'],
                $aluno['tre04_longitude']
            ];
        }

        return $dadosImprimir;
    }

    private function getSexo($sexo)
    {
        switch (trim($sexo)) {
            case 'M':
                return 'Masculino';
            case 'F':
                return 'Feminino';
            default:
                return 'Não informado';
        }
    }



    private function getCor($cor)
    {
        switch (trim($cor)) {
            case 'AMARELA':
                return 'Amarelo';
            case 'BRANCA':
                return 'Branco';
            case 'INDÍGENA':
                return 'Indígena';
            case 'PARDA':
                return 'Pardo';
            case 'PRETA':
                return 'Preto';
            default:
                return $cor;
        }
    }

    private function getTurno($turno)
    {
        switch ($turno) {
            case in_array($turno, [1,7]):
                return 'Manhã';
            case in_array($turno, [2,8]):
                return 'Tarde';
            case in_array($turno, [3,12]):
                return 'Noturno';
            case 5:
                return 'Integral';
            default:
                return 'Inconsistente';
        }
    }
 
    private function getNivel($nivel)
    {
        switch ($nivel) {
            case 1:
                return 'Infantil';
            case 2:
                return 'Fundamental';
            case 3:
                return 'Médio';
            case 4:
                return 'Outro';
        }
    }

    private function getGrauParentesco($grauParentesco)
    {
        switch ($grauParentesco) {
            case 0:
                return 'Pai, Mãe, Padrasto ou Madrasta';
            case 1:
                return 'Avô ou Avó';
            case 2:
                return 'Irmão ou Irmã';
            case 4:
                return 'Outro Parente';
        }
    }

    private function getZona($zona)
    {
        return ucfirst(strtolower($zona));
    }

    private function cabecalho()
    {
        return [
            'NOME',
            'DATA_NASCIMENTO',
            'SEXO',
            'COR',
            'LOCALIZACAO',
            'NIVEL_ENSINO',
            'TURNO_ENSINO',
            'CPF',
            'NOME_RESPONSAVEL',
            'GRAU_PARENTESCO',
            'ENDERECO',
            'LATITUDE',
            'LONGITUDE'
        ];
    }
}
