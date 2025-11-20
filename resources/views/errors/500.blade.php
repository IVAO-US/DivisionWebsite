@extends('layouts.errorDocument')

@section('code', '500')

@section('message')
    {{ $exception?->getMessage() ?: 'The whole universe has collapsed!' }}
@endsection