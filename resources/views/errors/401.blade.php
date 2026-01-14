@extends('layouts.errorDocument')

@section('code', '401')

@section('message')
    {{ $exception?->getMessage() ?: 'Space is out of reach!' }}
@endsection