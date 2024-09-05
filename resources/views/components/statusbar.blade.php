<div class="status-bar">
    <button class="sidebarCollapseTrig"><span></span><span></span><span></span></button>
    <div class="search-wrap">
        @php
            echo App\Http\Controllers\iconController::getIcon('search');
        @endphp
        <input type="text" class="searchInput" onkeyup="searchData(event)" placeholder="Search Here">
        <span class="searchCancel" onclick="searchCancel(event)">&times;</span>
    </div>
    <div class="status-wrap">
        @if (request()->is('settings/*'))
            <div class="icon-wrap back-home"><a href="{{ URL('/') }}">
                    @php
                        echo App\Http\Controllers\iconController::getIcon('mail');
                    @endphp
                </a></div>
        @endif
        <div class="loader icon-wrap" onclick="sysnData()">
            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                <title>Sync</title>
                <path
                    d="M434.67 285.59v-29.8c0-98.73-80.24-178.79-179.2-178.79a179 179 0 00-140.14 67.36m-38.53 82v29.8C76.8 355 157 435 256 435a180.45 180.45 0 00140-66.92"
                    fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="32" />
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="32" d="M32 256l44-44 46 44M480 256l-44 44-46-44" />
            </svg>
        </div>
        <div class="settings dropdown">
            <span class="dropdown-tolggler icon-wrap">
                @php
                    echo App\Http\Controllers\iconController::getIcon('cog');
                @endphp
            </span>
            <div class="dropdown-items">
                @foreach (App\Http\Controllers\SidebarController::settingModules() as $slug => $module)
                    @if (access(['settings', $slug, 'view']))
                        <a href='{{ url("settings/$slug") }}'>
                            @php
                                echo App\Http\Controllers\iconController::getIcon($module['icon']);
                            @endphp
                            {{ $module['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        <div class='user-menu dropdown'>
            <span class="dropdown-tolggler icon-wrap">
                @php
                    echo App\Http\Controllers\iconController::getIcon('user');
                @endphp
            </span>
            <div class="dropdown-items">

                @if (Auth())
                    <a class="active" href="">{{ Auth()->user()->name }}</a>
                @endIf
                <a href="">
                    @php
                        echo App\Http\Controllers\iconController::getIcon('password');
                    @endphp
                    Change Password
                </a>
                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                    @php
                        echo App\Http\Controllers\iconController::getIcon('logout');
                    @endphp
                    {{ __('Logout') }}
                </a>
            </div>
        </div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>
