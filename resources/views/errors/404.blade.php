@extends('layouts.errorDocument')

@section('code', '404')

@section('message')
    {{ $exception?->getMessage() ?: 'Lost in space...' }}
@endsection