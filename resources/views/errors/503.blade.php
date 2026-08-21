@extends('errors.layout')

@section('title', 'Sistem Sedang Pemeliharaan')
@section('code', '503')

@section('icon')
    <x-dynamic-component component="lucide-wrench" class="w-8 h-8 text-primary" />
@endsection

@section('heading', 'Dalam Proses Pemeliharaan')

@section('message')
    Sistem saat ini sedang dalam proses pembaruan atau pemeliharaan rutin berkala. Layanan akan segera aktif kembali beberapa saat lagi.
@endsection
