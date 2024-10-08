<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? $attributes->get('title', 'Default Title') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @php
        $aiSettings = \App\Http\Controllers\AiService::getAiSettings();
    @endphp
    <script>
        const APP_URL = "{{ config('app.url') }}";
        const ATTACH = "/{{ config('attachment.attachment_path') }}/";
        const IN_ATTACH = "/{{ config('attachment.inline_attachment_path') }}/";
        const USER_ID = {{ Auth::user()->id }};
        const USER_MAIL_CHANNEL = "{{ Auth::user()->getChannelID() }}";
        const AI_OPTIONS = {
            ai_provider: "{{ $aiSettings['provider'] }}",
            ai_temperature: "{{ $aiSettings['creativity'] }}",
            ai_tone: "{{ $aiSettings['tone'] }}"
        };
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
