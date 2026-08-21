@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')

@section('icon')
    <x-dynamic-component component="lucide-shield-alert" class="w-8 h-8 text-danger" />
@endsection

@section('heading', 'Akses Dibatasi')

@section('message')
    {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses izin (permission) yang cukup untuk membuka atau mengelola fitur halaman ini.' }}
@endsection
