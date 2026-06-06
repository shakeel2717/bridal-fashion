@extends('layouts.app')
@section('title', 'Sale Detail')
@section('content')
    <livewire:sales.sale-detail :sale="$sale" />
@endsection