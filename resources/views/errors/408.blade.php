@extends('components.layouts.errorDocument')

@section('code', '408')

@section('message')
    {{ $exception?->getMessage() ?: 'Lost in time...' }}
@endsection