@extends('components.layouts.errorDocument')

@section('code', '403')

@section('message')
    {{ $exception?->getMessage() ?: 'No spacewalk for you!' }}
@endsection