@extends('components.layouts.errorDocument')

@section('code', '405')

@section('message')
    {{ $exception?->getMessage() ?: 'See what happens when you try funny stuff?' }}
@endsection