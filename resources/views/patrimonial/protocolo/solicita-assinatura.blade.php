@extends("layouts.default")

@section("head")
    <style>
    </style>
@endsection
@section("content")
    <solicitacao-assinatura codigo-processo="{{$codigoProcesso}}" codigo-despacho="{{$codigoDespacho}}"/>
@endsection
