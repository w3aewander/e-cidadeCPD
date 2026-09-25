@extends("layouts.default")

@section("content")
    <manuais_educacao 
        :is-admin="{{ $isAdmin ? 'true' : 'false' }}"
        :usuario-id="{{ (int)$usuarioId }}"
        usuario-nome="{{ addslashes($usuarioNome) }}"
    ></manuais_educacao>
@endsection

