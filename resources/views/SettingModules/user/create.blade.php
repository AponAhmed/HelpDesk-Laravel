
<div class="user-create">
        <div class="form-header">
            @if($userData->id)
            {{ __('Update User') }}
            @else
            {{ __('New User') }}
            @endif
        </div>
        <form class="ajx" method="POST" action="
            @if($userData->id)
                {{ route('userUpdate',$userData->id) }}
            @else
                {{ route('userstore') }}
            @endif
            ">
            @csrf
            <div class="form-group ">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $userData->name }}" required autocomplete="name" autofocus>

                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $userData->email }}" required autocomplete="email">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

             @if($userData->id)
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" placeholder="Put it blank for Unchange" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
             @else
                <div class="form-group">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>
            @endif


            <div class="form-group">
                <label for="userRole" class="form-label">{{ __('User Role') }}</label>
                <select id="userRole" name="userRole">
                    @foreach ($roles as $role)
                        @if($role->name !=='Super Admin')
                        <option value="{{$role->id}}"
                            @if($userData->id && $userData->roleID == $role->id)
                                selected
                            @endif
                            >{{$role->name}}</option>
                        @endif
                    @endforeach
                </select>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    {{ __('Save') }}
                </button>
            </div>
        </form> 
   </div>