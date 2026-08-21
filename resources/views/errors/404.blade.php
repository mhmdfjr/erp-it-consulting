@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')

@section('icon')
    <x-dynamic-component component="lucide-file-question" class="w-8 h-8" />
@endsection

@section('heading', 'Halaman Tidak Ditemukan')

@section('message')
    Tautan atau halaman yang Anda tuju tidak tersedia, telah dipindahkan, atau alamat URL yang dimasukkan salah.
@endsection
