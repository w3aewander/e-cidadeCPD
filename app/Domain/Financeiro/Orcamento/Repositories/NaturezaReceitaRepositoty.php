<?php

namespace App\Domain\Financeiro\Orcamento\Repositories;

use App\Domain\Core\Base\Repository\BaseRepository;
use App\Domain\Financeiro\Orcamento\Models\NaturezaReceita;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NaturezaReceitaRepositoty extends BaseRepository
{
    protected $modelClass = NaturezaReceita::class;


    public function getByFilters(array $filtros, array $ordens)
    {
        return $this->newQuery()
            ->when(!empty($filtros['exercicio']), function (Builder $query) use ($filtros) {
                $query->where('o57_anousu', $filtros['exercicio']);
            })
            ->when(!empty($filtros['estrutural']), function (Builder $query) use ($filtros) {
                $query->where('o57_fonte', $filtros['estrutural']);
            })
            ->when(!empty($filtros['autocomplete']), function (Builder $query) use ($filtros) {
                $string = $filtros['autocomplete'];
                $query->whereRaw("o57_fonte || o57_descr ilike '%$string%'");
            })
            ->when(!empty($filtros['apenasComReceita']), function (Builder $query) {
                $query->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('orcamento.orcreceita')
                        ->whereRaw('o70_codfon = o57_codfon')
                        ->whereRaw('o70_anousu = o57_anousu');
                });
            })
            ->when(!empty($ordens), function ($query) use ($ordens) {
                foreach ($ordens as $order) {
                    $query->orderBy($order);
                }
            })
            ->paginate($filtros["rows"]);
    }
}
