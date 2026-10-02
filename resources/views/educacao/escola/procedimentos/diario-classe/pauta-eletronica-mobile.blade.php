@extends("layouts.default")

@section("content")
    <pauta_eletronica_mobile 
        user="{{ db_getsession('DB_id_usuario', false) }}"
        escola="{{ db_getsession('DB_coddepto', false) }}"
        server-url="{{ $serverUrl }}"
    ></pauta_eletronica_mobile>
@endsection
