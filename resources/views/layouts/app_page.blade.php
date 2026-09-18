<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$title}}</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{asset('css/lib.min.css')}}?v=1.3">
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css"/>
    <link rel="stylesheet" type="text/css" href="{{asset('css/main.css')}}?v=1.6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @php
        $locale = app()->getLocale();
    @endphp
    <script>
        var LanguneThisJs = '@php  echo $locale;@endphp';
    </script>
    @if ($ip_datas['status'] == 'success')
        <script>
            window.country_ISO = '{{ strtolower($ip_datas['countryCode']) }}'
        </script>
    @endif
    @if(isset($route))
        <script>
            window.route = '{{ $route }}'
        </script>
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css" integrity="sha512-tze+o/xG0w3yxxE8xe32piisVvI/LfcEuf6LW7lFLUxhio2SfFQ9mQ0TqB0avvonmxUXT7/l8+fnWkR03u85fQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" integrity="sha512-q+4liFwdPC/bNdhUpZx6aXDx/h77yEQtn4I1slHydcbZK34nLaR3cAeYSJshoxIOq3mjEf7xJE8YWIUHMn+oCQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script id="widget-wfp-script" language="javascript" type="text/javascript" src="https://secure.wayforpay.com/server/pay-widget.js"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="trans" style="opacity: 1 !important;">
<noscript>
    <div class="error-js">Для повної функціональності цього сайту необхідно включити JavaScript. Ось <a href='//artjoker.ua/ru/blog/how-to-enable-javascript/' target='_blank' class='text-link' rel='nofollow'>інструкції</a>, як увімкнути JavaScript у вашому браузері.</div>
</noscript>
<div id="app"  class="wrapper">
    @yield('content')
</div>
@if (Route::currentRouteName() === 'atmosphera'||'rules-and-conditions'===Route::currentRouteName()||'rules-for-turning-penny-money'===Route::currentRouteName())
    <footer>
        <div class="wrap_footer">
            <ul class="">
                <li><a  href="{{route('atmosphera')}}">Бронювання</a></li>
                <li><a  href="{{route('rules-and-conditions')}}">Правила і умови</a></li>
                <li><a  href="{{route('rules-for-turning-penny-money')}}">Правила повернення грошових коштів</a></li>
            </ul>
        </div>
        <div class="fop">
            <p>
                ФОП: Сваренчук Анна Анатоліївна</br>
                ІПН: 3796905520
            </p>
        </div>
    </footer>
@endif
</body>
</html>
