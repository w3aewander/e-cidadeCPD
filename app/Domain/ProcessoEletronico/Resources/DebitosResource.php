<?php

namespace App\Domain\ProcessoEletronico\Resources;

use App\Domain\Tributario\Caixa\Models\Arrecad;
use App\Domain\Tributario\Caixa\Models\Recibounica;
use App\Domain\Tributario\Caixa\Models\Tabrec;
use Illuminate\Database\Eloquent\Collection;

class DebitosResource
{
    public static function toArrayReciboUnica(Recibounica $reciboUnica)
    {
        $descricaoParcela = 'Parcela Única';

        if ($reciboUnica->k00_percdes != 0) {
            $descricaoParcela = (
                'Parcela Única com ' .
                $reciboUnica->k00_percdes .
                '% desconto'
            );
        }

        return [
            'numero_parcela'    => 'U' . $reciboUnica->index,
            'numero_debito'     => $reciboUnica->k00_numpre,
            'descricao_parcela' => $descricaoParcela,
            'tipo_debito'       => $reciboUnica->k00_tipo,
            'data_vencimento'   => $reciboUnica->k00_dtvenc,
            'valor_juros'       => (double) $reciboUnica->k00_vlrjuros,
            'valor_multa'       => (double) $reciboUnica->k00_vlrmulta,
            'valor_historico'   => (double) $reciboUnica->k00_vlrhis,
            'valor_corrigido'   => (double) $reciboUnica->k00_vlrcor,
            'valor_desconto'    => (double) $reciboUnica->k00_vlrdesconto,
            'valor_total'       => (double) $reciboUnica->k00_vlrtotal,
        ];
    }

    public static function toArray(Collection $collection)
    {
        return $collection->map(function ($arrecad) {
            return [
                'numero_debito'    => $arrecad->k00_numpre,
                'numero_parcela'   => $arrecad->k00_numpar,
                'total_debitos'    => $arrecad->total_detalhe_debitos,
                'data_vencimento'  => $arrecad->k00_dtvenc,
                'tipo_debito'      => $arrecad->k00_tipo,
                'valor_juros'      => (double) $arrecad->k00_vlrjuros,
                'valor_multa'      => (double) $arrecad->k00_vlrmulta,
                'valor_historico'  => (double) $arrecad->k00_vlrhis,
                'valor_corrigido'  => (double) $arrecad->k00_vlrcor,
                'valor_desconto'   => (double) $arrecad->k00_vlrdesconto,
                'valor_total'      => (double) $arrecad->k00_vlrtotal,
                'detalhes_parcela' => $arrecad->arrecad->map(function (Arrecad $arrecad) {
                    return self::toObjectArrecad($arrecad);
                })
            ];
        });
    }

    public static function toObjectArrecad(Arrecad $arrecad)
    {
        return (object) array_merge(
            [
                'numero_debito'   => $arrecad->k00_numpre,
                'numero_parcela'  => $arrecad->k00_numpar,
                'tipo_debito'     => $arrecad->k00_tipo,
                'numero_receita'  => $arrecad->k00_numpre,
                'data_vencimento' => $arrecad->k00_dtvenc,
                'valor_juros'     => (double) $arrecad->k00_vlrjuros,
                'valor_multa'     => (double) $arrecad->k00_vlrmulta,
                'valor_historico' => (double) $arrecad->k00_vlrhis,
                'valor_corrigido' => (double) $arrecad->k00_vlrcor,
                'valor_desconto'  => (double) $arrecad->k00_vlrdesconto,
                'valor_total'     => (double) $arrecad->k00_vlrtotal
            ],
            (array) self::toObjectTabrec($arrecad->tabrec)
        );
    }

    public static function toObjectTabrec(Tabrec $tabrec)
    {
        return (object) [
            'descricao_parcela' => trim($tabrec->k02_descr)
        ];
    }
}
