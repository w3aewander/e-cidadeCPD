<?php

/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao     2 da
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

namespace App\Domain\Educacao\Secretaria\Services;

use Illuminate\Support\Facades\DB;

class EmissaoAtasService
{
    public function getAlunosClassificacao($calendario, $turma)
    {
        return DB::table('escola.trocaserie')
        ->distinct()
        ->select(
            'ed47_i_codigo',
            'ed47_v_nome'
        )
        ->join('escola.aluno', 'ed47_i_codigo', '=', 'ed101_i_aluno')
        ->join('escola.turma as turmaorig', 'turmaorig.ed57_i_codigo', '=', 'ed101_i_turmaorig')
        ->join('escola.turma as turmadest', 'turmadest.ed57_i_codigo', '=', 'ed101_i_turmadest')
        ->where('turmaorig.ed57_i_calendario', '=', $calendario)
        ->where('turmaorig.ed57_i_codigo', '=', $turma)
        ->where('trocaserie.ed101_c_tipo', '=', 'C')
        ->orderBy('ed47_v_nome')
        ->get()->map(function ($aluno) {
            return (object) [
                'nome' => trim($aluno->ed47_v_nome),
                'codigo' => $aluno->ed47_i_codigo
            ];
        });

        return (object) $alunosClassificacao;
    }

    public function getAlunosAvanco($calendario, $turma)
    {
        return DB::table('escola.trocaserie')
        ->distinct()
        ->select(
            'ed47_i_codigo',
            'ed47_v_nome'
        )
        ->join('escola.aluno', 'ed47_i_codigo', '=', 'ed101_i_aluno')
        ->join('escola.turma as turmaorig', 'turmaorig.ed57_i_codigo', '=', 'ed101_i_turmaorig')
        ->join('escola.turma as turmadest', 'turmadest.ed57_i_codigo', '=', 'ed101_i_turmadest')
        ->where('turmaorig.ed57_i_calendario', '=', $calendario)
        ->where('turmaorig.ed57_i_codigo', '=', $turma)
        ->where('trocaserie.ed101_c_tipo', '=', 'A')
        ->orderBy('ed47_v_nome')
        ->get()->map(function ($aluno) {
            return (object) [
                'nome' => trim($aluno->ed47_v_nome),
                'codigo' => $aluno->ed47_i_codigo
            ];
        });

        return (object) $alunosAvanco;
    }

    public function getAlunosReclassificacao($escola, $calendario, $turma)
    {
        return DB::table('escola.trocaserie')
        ->distinct()
        ->select(
            'ed47_i_codigo',
            'ed47_v_nome'
        )
        ->join('escola.aluno', 'ed47_i_codigo', '=', 'ed101_i_aluno')
        ->join('escola.turma as turmaorig', 'turmaorig.ed57_i_codigo', '=', 'ed101_i_turmaorig')
        ->join('escola.escola as escolaorig', 'escolaorig.ed18_i_codigo', '=', 'turmaorig.ed57_i_escola')
        ->join('escola.turno as turnoorig', 'turnoorig.ed15_i_codigo', '=', 'turmaorig.ed57_i_turno')
        ->join('escola.sala as salaorig', 'salaorig.ed16_i_codigo', '=', 'turmaorig.ed57_i_sala')
        ->join(
            'escola.calendario as calendarioorig',
            'calendarioorig.ed52_i_codigo',
            '=',
            'turmaorig.ed57_i_calendario'
        )
        ->join('escola.base as baseorig', 'baseorig.ed31_i_codigo', '=', 'turmaorig.ed57_i_base')
        ->join('escola.turma as turmadest', 'turmadest.ed57_i_codigo', '=', 'trocaserie.ed101_i_turmadest')
        ->join('escola.escola as escoladest', 'escoladest.ed18_i_codigo', '=', 'turmadest.ed57_i_escola')
        ->join('escola.turno as turnodest', 'turnodest.ed15_i_codigo', '=', 'turmadest.ed57_i_turno')
        ->join('escola.sala as saladest', 'saladest.ed16_i_codigo', '=', 'turmadest.ed57_i_sala')
        ->join(
            'escola.calendario as calendariodest',
            'calendariodest.ed52_i_codigo',
            '=',
            'turmadest.ed57_i_calendario'
        )
        ->join('escola.base as basedest', 'basedest.ed31_i_codigo', '=', 'turmadest.ed57_i_base')
        ->where('escolaorig.ed18_i_codigo', '=', $escola)
        ->where('calendarioorig.ed52_i_codigo', '=', $calendario)
        ->where('turmaorig.ed57_i_codigo', '=', $turma)
        ->where('ed101_c_tipo', '=', 'R')
        ->orderBy('ed47_v_nome')
        ->get()->map(function ($aluno) {
            return (object) [
                'nome' => trim($aluno->ed47_v_nome),
                'codigo' => $aluno->ed47_i_codigo
            ];
        });
    }
}
