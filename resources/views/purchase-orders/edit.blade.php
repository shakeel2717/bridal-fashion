@extends('layouts.app')
@section('title', 'Edit Purchase Order')
@section('content')
    <livewire:purchase-orders.purchase-order-create :purchaseOrder="$purchaseOrder" />
@endsection
