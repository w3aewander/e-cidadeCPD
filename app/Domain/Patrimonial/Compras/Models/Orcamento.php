<?php

namespace App\Domain\Patrimonial\Compras\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $pc20_codorc,
 * @property $pc20_dtate,
 * @property $pc20_hrate,
 * @property $pc20_obs,
 * @property $pc20_prazoentrega,
 * @property $pc20_validadeorcamento,
 * @property $pc20_cotacaoprevia
 */
class Orcamento extends Model
{
    public $timestamps = false;
    protected $table = 'compras.pcorcam';
}
