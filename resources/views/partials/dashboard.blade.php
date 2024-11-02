@extends('layouts.master')

@section('content')
    @if (auth()->user()->isAddmin())
        @include('partials.dashboard-admin')
    @else
        @include('partials.dashboard-user')
    @endif
@endsection