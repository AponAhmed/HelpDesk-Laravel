<div class="user-create">
        <div class="form-header">
            {{ __('New Role') }}
        </div>
        <form class="ajx" method="POST" action="{{ route('createRoleStore') }}">
            @csrf
            <div class="form-group ">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="" required autocomplete="name" autofocus>

                @error('name')
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