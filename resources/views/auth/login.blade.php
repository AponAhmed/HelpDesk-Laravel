<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="hdesk-wrap">
        <div class="main-wrap">
            <div class="login-card">
                <div class="login-card-left">
                    <div class="card-header">{{ __('HelpDesk') }}</div>
                    <p>SiATEX BD LTD</p>
                </div>
                <div class="login-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email" class="form-label">{{ __('UserName or eMail Address') }}</label>
                            <input id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label form-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Login') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="btn-link  form-label" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
