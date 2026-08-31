@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div id="app">
        <correspondencia-editor-vue></correspondencia-editor-vue>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // TinyMCE configuration (global)
    tinymce.init({
        selector: 'textarea',
        language: 'es',
    });
</script>
@endpush
