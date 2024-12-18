@extends('layouts.admin') 
@section('contents')
    <div class="row">
        <div class="col-md-12 welcome_part">
            <p align="center"><span>Welcome </span> {{ Auth::User()->name }}</p>     
            <a href="{{ route('export.statistics') }}" class="btn btn-primary">Export User Statistics to Excel</a>
    
            @include('admin.dashboard.view') 
        </div>
    </div>
@endsection
