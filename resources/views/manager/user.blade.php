@extends('manager.header')


@section('activity')
    #user {
        color: #06C;
    }
@endsection
@section('body')
    <div id="app">
        <show-user></show-user>
    </div>
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
@endsection
