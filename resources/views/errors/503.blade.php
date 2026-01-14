@extends('layouts.errorDocument')

@section('code', '503')

@section('message')
    {{ $exception?->getMessage() ?: 'Your spaceship has been delayed...' }}
@endsection