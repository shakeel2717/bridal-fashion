@extends('layouts.app')
@section('title', 'Purchase Return')
@section('content')
    <livewire:purchase-orders.purchase-return-create :po="$purchaseOrder" />
@endsection
