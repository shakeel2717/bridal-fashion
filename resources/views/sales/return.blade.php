@extends('layouts.app')
@section('title', 'Sale Return')
@section('content')
    <livewire:sales.sale-return-create :sale="$sale" />
@endsection
