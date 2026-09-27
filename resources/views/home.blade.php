@extends('layouts.app_page')
@section('content')
    <main class="main main-background" id="main">
        <app
            route="{{$route}}"
            :is-local='@json(app()->environment("local"))'
        ></app>
    </main>
@endsection
