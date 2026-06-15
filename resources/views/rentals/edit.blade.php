@extends('layouts.app')
@section('title', 'Edit Rental')
@section('content')
    <livewire:rentals.rental-create :rental="$rental" />
@endsection
