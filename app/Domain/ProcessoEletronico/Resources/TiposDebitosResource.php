<?php

namespace App\Domain\ProcessoEletronico\Resources;

use App\Domain\Tributario\Arrecadacao\Models\Arretipo;
use App\Domain\Tributario\Caixa\Models\Cadtipo;
use App\Domain\Tributario\Caixa\Models\Recibounica;
use Illuminate\Database\Eloquent\Collection;

class TiposDebitosResource
{
    public static function toArray(Collection $collection)
    {
        $result   = [];
        $arretipo = $collection->map(function ($arretipo) {
            if ($arretipo->debitos->count() > 0) {
                return (object) array_merge(
                    (array) self::toObjectTipoDebito($arretipo),
                    (array) self::toObjectCadTipo($arretipo->cadtipo)
                );
            }
        });

        foreach ($arretipo as $value) {
            if (!is_null($value)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    public static function toObjectTipoDebito(Arretipo $arretipo)
    {
        $debitos    = DebitosResource::toArray($arretipo->debitos);
        $valorTotal = 0;

        foreach ($debitos as $debito) {
            $valorTotal += $debito['valor_total'];
        }

        return (object) [
            'codigo_tipo_debito'       => $arretipo->k00_tipo,
            'descricao_tipo_debito'    => $arretipo->k00_descr,
            'permitir_emitir_debito'   => (is_null($arretipo->k00_emrec)) ? false : ($arretipo->k00_emrec),
            'permissao_debito'         => (
                (is_null($arretipo->k00_recibodbpref)) ? false : ($arretipo->k00_recibodbpref)
            ),
            'permissao_debito_vencido' => (
                (is_null($arretipo->k00_liberacarnepref) ? false : ($arretipo->k00_liberacarnepref))
            ),
            'valor_total_debito'       => $valorTotal,
            'parcelas_unicas'          => $arretipo->cotaUnica->map(function (Recibounica $cotaUnica) {
                return DebitosResource::toArrayReciboUnica($cotaUnica);
            }),
            'parcelas'                 => $debitos
        ];
    }

    public static function toObjectCadTipo(Cadtipo $cadtipo)
    {
        return (Object) [
            'codigo_grupo_tipo_debito'    => $cadtipo->k03_tipo,
            'descricao_grupo_tipo_debito' => $cadtipo->k03_descr
        ];
    }
}
