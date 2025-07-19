@extends('components.layouts.errorDocument')

@section('code', '419')

@section('message')
    {{ $exception?->getMessage() ?: 'You lost track of time...' }}
@endsection