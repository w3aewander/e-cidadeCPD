@extends("layouts.default")

@section("head")
    <style>
        .p-fileupload-file img {
            display: none !important;
        }

        .p-fileupload-content {
            height: 195px !important;
            overflow: auto !important;
        }

        .trumbowyg-box {
            height: 250px !important;

        }

        .trumbowyg-button-pane {
            z-index: auto !important;
        }
    </style>
@endsection
@section("content")
    <protocolo_documento/>
@endsection
