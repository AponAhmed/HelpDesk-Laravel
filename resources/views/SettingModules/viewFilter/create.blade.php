
<div class="user-create">
        <div class="form-header">
            @if($ViewFilter->id)
            {{ __('Update View Filter') }}
            @else
            {{ __('New View Filter') }}
            @endif
        </div>
        <form class="ajx" method="POST" action="
            @if($ViewFilter->id)
                {{ route('ViewFilterUpdate',$ViewFilter->id) }}
            @else
                {{ route('ViewFilterStore') }}
            @endif
            ">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">{{ __('Filter Keys') }}</label>
                <textarea id="keys" class="form-control" name="keys" required rows="8">{{$ViewFilter->keys}}</textarea>
                 @error('keys')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="userRole" class="form-label">{{ __('Department') }}</label>
                        <select class="custom-select" name="department" required>
                            @foreach ($departments as $department)
                                <option value="{{$department->id}}"
                                    @if($ViewFilter->id)
                                    @if(isset($ViewFilter->department) && $ViewFilter->department == $department->id)
                                        selected
                                    @endif 
                                    @endif 
                                    >{{$department->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="userRole" class="form-label">{{ __('Filter Role') }}</label>
                        <select class="custom-select" required name="role">
                            @foreach ($filterRoles as $k=> $fRole)
                                <option value="{{$k}}"
                                @if($ViewFilter->id)
                                    @if(isset($ViewFilter->role) && $ViewFilter->role == $k)
                                        selected
                                    @endif 
                                @endif 
                                >{{$fRole}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    {{ __('Save') }}
                </button>
            </div>
        </form> 
   </div>