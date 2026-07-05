@extends('layouts.app')
@section('title', $billBook->name . ' — Detail')
@section('content')
    <livewire:bill-books.bill-book-detail :billBook="$billBook" />
@endsection