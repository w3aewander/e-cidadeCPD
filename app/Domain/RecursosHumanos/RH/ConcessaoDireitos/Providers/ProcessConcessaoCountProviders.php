<?php

namespace App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Providers;

use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Models\ConcessaoErros;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Models\ProcessConcessaoContagem;
use Illuminate\Support\Facades\DB;

class ProcessConcessaoCountProviders
{

    public static function salvarLogErro($matricula, $erro)
    {
        if ($erro) {
            $proce = new ConcessaoErros();
            $proce->rh509_matricula = $matricula;
            $proce->rh509_erro = $erro;
            $s = $proce->save();
            return $s;
        }
    }

    public static function getFila()
    {
        return ProcessConcessaoContagem::all();
    }

    public static function pararFila()
    {
        $query = "delete from jobs where payload like '%ProcessamentoConcessao%'";
        DB::select($query);
        $query = "delete from recursoshumanos.process_concessao_count";
        DB::select($query);
    }

    public static function setFila()
    {
        $quant = ProcessConcessaoContagem::select('rh510_quantidade')->max('rh510_quantidade');
        $query = "update recursoshumanos.process_concessao_count set rh510_quantidade = " . ($quant + 1) . ';';
        DB::select($query);
    }

    public static function verificarFila()
    {
        $fila = ProcessConcessaoContagem::all()->count();
        if ($fila != 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function create($total)
    {
        $query = "delete from recursoshumanos.process_concessao_count";
        DB::select($query);
        $proce = new ProcessConcessaoContagem;
        $proce->rh510_total = $total;
        $proce->rh510_quantidade = 0;
        $proce->save();
    }

    public static function finalizar()
    {
        $query = "delete from recursoshumanos.process_concessao_count";
        DB::select($query);
    }
}
