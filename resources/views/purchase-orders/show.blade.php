@extends('layouts.app')
@section('title', 'Purchase Order')
@section('content')
    <livewire:purchase-orders.purchase-order-detail :po="$po" />
@endsection