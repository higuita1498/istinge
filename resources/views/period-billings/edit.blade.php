@extends('layouts.app')

@section('content')

@if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 5000);
        </script>
    @endif
    @if (Session::has('info'))
        <div class="alert alert-info">
            {{ Session::get('info') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 10000);
        </script>
    @endif


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
         
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
