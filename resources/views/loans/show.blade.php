@extends('layouts.app')
@section('title', $lender->name . ' — Loan Statement')
@section('content')
    <livewire:loans.loan-detail :lender="$lender" />
@endsection
