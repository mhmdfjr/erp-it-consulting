@extends('errors.layout')

@section('title', 'Sesi Telah Berakhir')
@section('code', '419')

@section('icon')
    <x-dynamic-component component="lucide-clock" class="w-8 h-8 text-warning" />
@endsection

@section('heading', 'Sesi Kadaluarsa')

@section('message')
    Sesi autentikasi atau token keamanan formulir Anda telah berakhir karena tidak ada aktivitas. Silakan muat ulang halaman atau login kembali.
@endsection
