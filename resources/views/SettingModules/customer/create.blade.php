<div class="user-create">
        <div class="form-header">
            @if($customer->id)
            {{ __('Update Customer') }}
            @else
            {{ __('New Customer') }}
            @endif
        </div>
        <form class="ajx" method="POST" action="
            @if($customer->id)
                {{ route('customerUpdate',$customer->id) }}
            @else
                {{ route('customerStore') }}
            @endif
            ">
            @csrf
            <div class="form-group ">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $customer->name }}" required autocomplete="name" autofocus>

                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $customer->email }}" required autocomplete="email">
                @error('email')
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