<x-app-layout title="Settings : General Settings">
    <x-slot name="script">
        <script type="module">
            document.addEventListener("DOMContentLoaded", function() {
                $("#settingsForm").on('submit', function(e) {
                    e.preventDefault();
                    let route = $(this).attr('action');
                    let settings = $(this).serialize();
                    let target = $(e.target);
                    target.find("button[type='submit']").html('<div class="load load03"><div></div></div>');
                    axios.post(route, {
                        settings
                    }).then((response) => {
                        target.find("button[type='submit']").html("Update");
                        //console.log(response);
                        if (response.data == 1) {
                            ntf('Settings Updated', 'success');
                        }
                    }).catch((error) => {
                        ntf(error, 'error');
                    });
                });

                window.setRole2Release = function(e, element) {
                    e.preventDefault();
                    let id = $(element).attr('data-id');
                    let type = $(element).attr('data-type');
                    let wrap = $(".roleWrapper");
                    let c = $(".release-role-item").length;
                    let txt = $(element).html();
                    wrap.append(`<strong class="release-role-item">
                        <span class='releaseRoleType'>${type}</span>: ${txt}
                        <input type="hidden" name="optionGlobal[releaseStep][${c}][type]" value="${type}">
                        <input type="hidden" name="optionGlobal[releaseStep][${c}][id]" value="${id}">
                        <span class='removeRoleType' onclick="removeReleaseRole(this)">&times;</span>
                    </strong>`)
                    console.log(element);
                }

                window.removeIp = function(_this) {
                    Notiflix.Confirm.show(
                        "IP Confirm",
                        "Are you sure to Remove : " + $(_this).closest(".ipItem").find('input').val(),
                        "Yes",
                        "No",
                        function() {
                            $(_this).closest('.ipItem').remove();
                        }
                    );
                }

                $(".addNewIP").on('click', function() {
                    let inputEl = $('.ipInput');
                    let str = `<span class="ipItem">
                        <input name="optionGlobal[restricted_ip][]" type="hidden" value="${inputEl.val()}">
                        ${inputEl.val()}
                        <span onclick="removeIp(this)" class="removeIp">×</span>
                    </span>`
                    inputEl.val('');
                    $(".IPList").append(str);
                });

                window.removeReleaseRole = function(_this) {
                    $(_this).closest(".release-role-item").remove();
                }
            });
        </script>
    </x-slot>

    <div>
        <div class="user-list data-list">
            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-title">
                        <h2>General Settings</h2>
                    </div>
                    <div class="data-controller">
                        <!--<a class="popup btn-new" data-w='500'  href="">New</a><br>-->
                    </div>
                </div>
                <form method="post" id="settingsForm" action="{{ route('generalSettingsStore') }}">
                    <div class="optionGeneralArea">
                        <div class="settings-group">
                            <label class="settings-group-head">User Options</label>
                            <div class="option-wrap">
                                <label>Time Zone (hr)</label>
                                <input type="text" class="text-input" name="option[time_zone]"
                                    value="{{ $settings->get_option('time_zone') }}" placeholder="Ex: +6">
                            </div>
                        </div>

                        @if (_UR('Super Admin'))
                            <div class="settings-group">
                                <label class="settings-group-head"><input value="1" type="checkbox"
                                        name="optionGlobal[release_control]"
                                        {{ $settings->get_option('release_control', true) == '1' ? 'checked' : '' }}>
                                    Release Control</label>
                                <div class="option-wrap">
                                    <div class="release-control-role">
                                        <div class="roleWrapper">
                                            @foreach ($settings->releaseRoles() as $k => $releaseRole)
                                                @php
                                                    $k++;
                                                @endphp
                                                <strong class="release-role-item">
                                                    <span class='releaseRoleType'>{{ $releaseRole['type'] }}</span>:
                                                    {{ $releaseRole['name'] }}
                                                    <input type="hidden"
                                                        name="optionGlobal[releaseStep][{{ $k }}][type]"
                                                        value="{{ $releaseRole['type'] }}">
                                                    <input type="hidden"
                                                        name="optionGlobal[releaseStep][{{ $k }}][id]"
                                                        value="{{ $releaseRole['id'] }}">
                                                    <span class='removeRoleType'
                                                        onclick="removeReleaseRole(this)">&times;</span>
                                                </strong>
                                            @endForeach
                                        </div> {{-- Role Wraper --}}
                                        <div class="customSelect">
                                            <div class="customSelectTog releaseRoleCustomSelect"
                                                onclick="customSelect(event,this)">+</div>
                                            <div class="optionItems" style="display:none">
                                                <label class="optionGroupLabel">User Roles</label>
                                                @foreach ($roles as $role)
                                                    <div class="optionitem-wrap">
                                                        <a class="custom-select-option"
                                                            onclick="setRole2Release(event,this)" data-type="role"
                                                            href="javascript:void(0)"
                                                            data-id="{{ $role->id }}">{{ $role->name }}</a>
                                                    </div>
                                                @endForeach
                                                <label class="optionGroupLabel">Users</label>
                                                @foreach ($users as $user)
                                                    <a class="custom-select-option"
                                                        onclick="setRole2Release(event,this)" href="javascript:void(0)"
                                                        data-type="user"
                                                        data-id="{{ $user->id }}">{{ $user->name }}</a>
                                                @endForeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="settings-group">
                                <input type="hidden" name="optionGlobal[ip_restricted]" value="0">
                                <label class="settings-group-head"><input name="optionGlobal[ip_restricted]"
                                        value="1"
                                        {{ $settings->get_option('ip_restricted', true) == '1' ? 'checked' : '' }}
                                        type="checkbox">&nbsp;IP Allowed</label>
                                <div class="restricted_ip_area">
                                    <div class="IPList">
                                        @foreach ($settings->allowed_ip() as $ip)
                                            <span class="ipItem">
                                                <input name="optionGlobal[restricted_ip][]" type="hidden"
                                                    value="{{ $ip }}">
                                                {{ $ip }}
                                                <span onclick="removeIp(this)" class="removeIp">×</span>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="ipAddArea">
                                        <input type="text" class="ipInput">
                                        <button class="addNewIP" type="button">Add</button>
                                    </div>

                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="data-table-footer settings">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
