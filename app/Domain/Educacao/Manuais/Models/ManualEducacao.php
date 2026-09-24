<?php

namespace App\Domain\Educacao\Manuais\Models;

use App\Domain\Configuracao\Usuario\Models\Usuario;
use Illuminate\Database\Eloquent\Model;

class ManualEducacao extends Model
{
    protected $table = 'configuracoes.manuais_educacao';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descricao',
        'caminho_arquivo',
        'nome_arquivo_original',
        'tamanho_bytes',
        'mime_type',
        'id_usuario',
        'criado_em',
        'ativo'
    ];

    protected $casts = [
        'id' => 'integer',
        'titulo' => 'string',
        'descricao' => 'string',
        'caminho_arquivo' => 'string',
        'nome_arquivo_original' => 'string',
        'tamanho_bytes' => 'integer',
        'mime_type' => 'string',
        'id_usuario' => 'integer',
        'criado_em' => 'datetime',
        'ativo' => 'boolean'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}

