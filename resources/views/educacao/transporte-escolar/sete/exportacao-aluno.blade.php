@extends('layouts.default')

@section('content')
    <exportacao_aluno exercicio="{{db_getsession('DB_anousu')}}"></exportacao_aluno>
@endsection
