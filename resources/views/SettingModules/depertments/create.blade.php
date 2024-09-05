<x-app-layout title="Settings : Department Create">
    <x-slot name="script">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                $(function() {
                    $("#departmentForm").on('submit', function(e) {
                        e.preventDefault();
                        postData('', $(this).serialize(), function(res) {
                            //console.log(res);
                            window.location.href = "/settings/department";
                        });
                        //console.log('submit');
                    });
                });
            });
        </script>
    </x-slot>

    <div class="innderBody">
        <div class="data-table-header">
            <div class="data-title">
                @if ($department->id)
                    <h2>{{ __('Update Department') }}</h2>
                @else
                    <h2>{{ __('New Department') }}</h2>
                @endif

            </div>
        </div>
        <form id="departmentForm" class="ajx" method="POST"
            action="
            @if ($department->id) {{ route('departmentUpdateData', $department->id) }}
            @else
                {{ route('depertmentStore') }} @endif
            ">
            @csrf
            <div class="form-wrap">
                <div class="input-wrap">
                    <label class="form-label">{{ e('Depertment Name') }}</label>
                    <input type="text" name="name" required value="{{ $department->name }}" class="form-control">
                </div>
                <div class="input-wrap">
                    <label class="form-label">{{ e('Ticket Prifix') }}</label>
                    <input type="text" name="prefix" required value="{{ $department->prefix }}"
                        class="form-control">
                </div>
                <div class="input-wrap">
                    <label class="form-label">{{ e('Email Address') }}</label>
                    <input type="email" name="email" required value="{{ $department->email }}" class="form-control">
                </div>
                <div class="input-wrap">
                    <label class="form-label">{{ e('Signature') }}</label>
                    <textarea id="editor" name="signature" class="form-control">{{ $department->signature }}</textarea>
                </div>
                <br>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Save') }}
                    </button>
                    &nbsp;&nbsp;&nbsp;<a href="{{ route('department') }}">Back</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
