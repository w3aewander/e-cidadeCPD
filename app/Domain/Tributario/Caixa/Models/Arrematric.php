<?php

namespace App\Domain\Tributario\Caixa\Models;

use App\Domain\Tributario\Caixa\Models\Arrecad;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Model Arrematric
 *
 * @property int k00_numpre
 * @property int k00_matric
 * @property double|null k00_perc
 */
class Arrematric extends Model
{
    protected $table = 'caixa.arrematric';

    protected $fillable = [
        'k00_numpre',
        'k00_matric',
        'k00_perc'
    ];

    public function arrecad()
    {
        return $this->hasMany(Arrecad::class, 'k00_numpre', 'k00_numpre');
    }
}
