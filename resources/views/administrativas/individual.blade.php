@extends('layouts.master')

@section('page-title', 'Expediente')
@section('title', 'Expediente')

@section('content')
<div id="app">
    <individual-vue :expediente-id="{{ request('id') }}"></individual-vue>
</div>
@endsection
