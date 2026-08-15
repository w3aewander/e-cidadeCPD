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

namespace App\Domain\Educacao\TransporteEscolar\Repositories\Sete;

use App\Domain\Educacao\Escola\Models\Aluno;
use Illuminate\Support\Facades\DB;

class ExportacaoAlunosRepository
{
    
    const UTILIZA_TRANSPORTE_PUBLICO = '1';
    const TRANSPORTE_MUNICIPAL = '2';

    public function getDadosAlunos($dados)
    {
        $campos = [
            DB::raw("DISTINCT ed47_i_codigo"),
            'ed47_v_nome',
            'ed47_d_nasc',
            'ed47_v_sexo',
            'ed47_c_raca',
            'ed57_i_turno',
            'ed10_tipo',
            'ed47_c_nomeresp',
            'ed47_parentescoresponsavel',
            'ed47_c_zona',
            'ed47_v_cpf',
            'ed47_celularresponsavel',
            'tre04_latitude',
            'tre04_longitude',
            'ed47_v_cep',
            'ed47_v_ender',
            'ed355_porteira',
            'ed355_mataburro',
            'ed355_colchete',
            'ed355_atoleiro',
            'ed355_ponterustica',
            DB::raw('
                CASE WHEN ed214_i_necessidade = 106 THEN true
                ELSE false END as deficiencia_caminhar
            '),
            DB::raw('
                CASE WHEN ed214_i_necessidade in(103,104,105) THEN true
                ELSE false END as deficiencia_ouvir
            '),
            DB::raw('
                CASE WHEN ed214_i_necessidade in (101,102,105,114) THEN true
                ELSE false END as deficiencia_enxergar
            '),
            DB::raw('
                CASE WHEN ed214_i_necessidade = 107 THEN true
                ELSE false END as deficiencia_mental
            ')
        ];

        $query = Aluno::query()
        ->select($campos)
        ->join('matricula', 'ed60_i_aluno', 'aluno.ed47_i_codigo')
        ->join('turma', 'ed57_i_codigo', 'matricula.ed60_i_turma')
        ->join('calendario', 'ed52_i_codigo', 'turma.ed57_i_calendario')
        ->join('base', 'ed31_i_codigo', 'turma.ed57_i_base')
        ->join('cursoedu', 'ed29_i_codigo', 'base.ed31_i_curso')
        ->join('ensino', 'ensino.ed10_i_codigo', 'cursoedu.ed29_i_ensino')
        ->leftJoin('alunotransportedificuldades', 'ed355_aluno', 'aluno.ed47_i_codigo')
        ->leftJoin('alunonecessidade', 'ed214_i_aluno', 'aluno.ed47_i_codigo')
        ->leftJoin(
            'linhatransportepontoparadaaluno',
            'tre12_aluno',
            'aluno.ed47_i_codigo'
        )
        ->leftJoin(
            'linhatransportepontoparada',
            'tre11_sequencial',
            'linhatransportepontoparadaaluno.tre12_linhatransportepontoparada'
        )
        ->leftJoin(
            'pontoparada',
            'tre04_sequencial',
            'linhatransportepontoparada.tre11_pontoparada'
        )
        ->where('ed52_i_ano', $dados['competencia'])
        ->where('ed60_c_situacao', 'MATRICULADO')
        ->where('ed60_c_ativa', 'S')
        ->where('ed47_i_transpublico', $this::UTILIZA_TRANSPORTE_PUBLICO)
        ->where('ed47_c_transporte', $this::TRANSPORTE_MUNICIPAL)
                
        ->where(function ($query) {
            $query->WhereNotNull('ed47_d_nasc')
            ->WhereNotNull('ed47_v_sexo')
            ->WhereNotNull('ed47_c_raca')
            ->WhereNotNull('ed47_c_zona')
            ->WhereNotNull('ed10_tipo')
            ->WhereNotNull('ed57_i_turno');
        })
        ->get();
            
        return $query;
    }

    public function getDadosAlunosInconsistentes($dados, $verificarInconsistencia = false)
    {
        $campos= [
            'ed47_i_codigo',
            'ed47_v_nome',
            'ed47_d_nasc',
            'ed47_v_sexo',
            'ed47_c_raca',
            'ed47_c_zona',
            'ed10_tipo',
            'ed57_i_turno'
        ];

        if ($verificarInconsistencia) {
            $campos= [
                DB::raw('count(ed47_i_codigo) as numero_inconsistencias'),
            ];
        }

        $query = Aluno::query()
        ->select($campos)
        ->join('matricula', 'ed60_i_aluno', 'aluno.ed47_i_codigo')
        ->leftJoin('turma', 'ed57_i_codigo', 'matricula.ed60_i_turma')
        ->leftJoin('calendario', 'ed52_i_codigo', 'turma.ed57_i_calendario')
        ->leftJoin('base', 'ed31_i_codigo', 'turma.ed57_i_base')
        ->leftJoin('cursoedu', 'ed29_i_codigo', 'base.ed31_i_curso')
        ->leftJoin('ensino', 'ensino.ed10_i_codigo', 'cursoedu.ed29_i_ensino')
        ->where('ed60_c_situacao', 'MATRICULADO')
        ->where('ed60_c_ativa', 'S')
        ->where('ed52_i_ano', $dados['competencia'])
        ->where('ed47_i_transpublico', $this::UTILIZA_TRANSPORTE_PUBLICO)
        ->where('ed47_c_transporte', $this::TRANSPORTE_MUNICIPAL)
        ->where(function ($query) {
            $query->whereNull('aluno.ed47_d_nasc')
            ->orWhereNull('aluno.ed47_v_sexo')
            ->orWhereNull('aluno.ed47_c_raca')
            ->orWhereNull('aluno.ed47_c_zona')
            ->orWhereNull('ensino.ed10_tipo')
            ->orWhereNull('turma.ed57_i_turno');
        })
        ->distinct();

        if (!$verificarInconsistencia) {
            $query->orderBy('ed47_v_nome');
        }
        
        return $query->get();
    }
}
