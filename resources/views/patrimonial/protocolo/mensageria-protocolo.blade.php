@extends("layouts.default")

@section("content")
    <mensageria_protocolo :processo="{{ json_encode($processo) }}"></mensageria_protocolo>
@endsection
