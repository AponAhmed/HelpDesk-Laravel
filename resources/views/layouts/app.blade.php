<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? $attributes->get('title', 'Default Title') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <script>
        const APP_URL = "{{ config('app.url') }}";
        const ATTACH = "/storage/{{ config('attachment.attachment_path') }}/";
        const IN_ATTACH = "/storage/{{ config('attachment.inline_attachment_path') }}/";
        const USER_ID = {{ Auth::user()->id }}
    </script>
</head>
<!--detailsOpend-->

<body class="@if (request()->is('list/*'))  @endif">
    <div class="hdesk-wrap">
        <div class="sidebar">
            {{ App\Http\Controllers\SidebarController::index() }}
        </div>
        <script>
            //Set Sidebar Collapse from Cookies
            let sidebarColps = localStorage.getItem("sidebarCollapse"); // getCookie("sidebarCollapse");
            if (sidebarColps === "true") {
                document.querySelector(".sidebar").classList.add("clps");
            } else {
                document.querySelector(".sidebar").classList.remove("clps");
            }
        </script>
        <div class="main-wrap resizable">
            @include('components.statusbar')
            <div class="body-wrap">
                <div class="page-loader">
                    <div class="load load10">
                        <div></div>
                        <div></div>
                    </div>
                </div>
                {{ $slot }}
            </div>
        </div>
        @if (!request()->is('settings/*'))
            <div class="list-triger"><span onclick="$('body').toggleClass('listnone')" class="tooltip"
                    data-position="top" title="List Toggle"></span></div>
            <div class="viewWrap"></div>
        @endif
    </div>
    {{ $script ?? '' }}
</body>

</html>
