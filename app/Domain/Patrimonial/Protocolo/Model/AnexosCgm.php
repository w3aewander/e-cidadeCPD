<?php

namespace App\Domain\Patrimonial\Protocolo\Model;

use Illuminate\Database\Eloquent\Model;

class AnexosCgm extends Model
{
    public $timestamps = false;

    protected $table = 'protocolo.anexoscgm';

    protected $primaryKey = 'z34_sequencial';

    protected $fillable = [
        'z34_arquivo',
        'z34_idstorage',
        'z34_descricao',
        'z34_observacao',
        'z34_data',
        'z34_usuario',
        'z34_cgm',
        'z34_usuarioexclusao',
        'z34_dataexclusao'
    ];
}
