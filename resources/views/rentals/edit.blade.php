@extends('layouts.app')
@section('title', 'Edit Rental')
@section('content')
    <livewire:rentals.rental-edit :rental="$rental" />
@endsection