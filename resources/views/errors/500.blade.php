@extends('errors.layout')

@section('title', 'Terjadi Kesalahan Server')
@section('code', '500')

@section('icon')
    <x-dynamic-component component="lucide-server-crash" class="w-8 h-8 text-danger" />
@endsection

@section('heading', 'Gangguan Layanan Server')

@section('message')
    Terjadi kendala pemrosesan data internal pada server. Tim teknis telah menerima log pencatatan kendala ini.
@endsection
