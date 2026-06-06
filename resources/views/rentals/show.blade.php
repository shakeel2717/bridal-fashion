@extends('layouts.app')
@section('title', 'Rental Detail')
@section('content')
    <livewire:rentals.rental-detail :rental="$rental" />
@endsection