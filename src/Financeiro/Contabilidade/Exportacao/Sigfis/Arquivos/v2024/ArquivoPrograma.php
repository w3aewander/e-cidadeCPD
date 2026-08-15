<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use stdClass;
use Illuminate\Database\Capsule\Manager as DB;

class ArquivoPrograma extends ArquivoBase
{
    protected $sNomeArquivo = 'Programa';

    public function gerarDados()
    {
        $aProgramas = DB::table('orcprograma AS op')
            ->select([
                'op.o54_programa as programa',
                'op.o54_descr as descr',
                'op.o54_finali as obejtivo'
            ])
            ->join('orcdotacao AS od', function ($join) {
                $join->on('od.o58_anousu', 'op.o54_anousu');
                $join->on('od.o58_programa', 'op.o54_programa');
            })

            // conditions
            ->where('op.o54_anousu', $this->iAnoUsu)
            ->where('od.o58_instit', db_getsession('DB_instit'))
            ->distinct()
            ->get();

        if ($aProgramas->isEmpty()) {
            throw new BusinessException('Programas vazios');
        }

        $RemessaPrograma = new stdClass();
        $RemessaPrograma->Programas = [];

        foreach ($aProgramas as $item) {
            $Programa = new stdClass();
            $Programa->Identificador = implode('', [$this->iAnoUsu, $item->programa]);
            $Programa->CodigoPrograma = $item->programa;
            $Programa->Descricao = Helper::convertAndLimit($item->descr, 200);
            $Programa->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $Programa->Ano = $this->iAnoUsu;
            $Programa->Objetivo = (!empty($item->obejtivo))
                ? Helper::convertAndLimit($item->obejtivo, 2000)
                : $Programa->Descricao;

            $RemessaPrograma->Programas[] = (object) ['Programa' => $Programa];
        }

        $this->aDados = $RemessaPrograma;
    }
}
