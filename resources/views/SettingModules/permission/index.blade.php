<x-app-layout title="Settings : Permission Center">
    <x-slot name="script">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new popup;

                window.LoadData = function() {
                    window.location.reload();
                }

                $("#permissionCenter").on('submit', function(e) {
                    e.preventDefault();
                    //Notiflix.Loading.pulse();
                    if ($(".custom-select-option.selected").length > 0) {
                        let PType = $(".custom-select-option.selected").attr("data-type");
                        let MId = $(".custom-select-option.selected").attr("data-id");
                        let route = $(this).attr('action');
                        let permissionData = $(this).serialize();
                        axios.post(route, {
                            permissionData,
                            model: PType,
                            id: MId
                        }).then((response) => {
                            //Notiflix.Loading.remove();
                            //console.log(response);
                            if (response.data == 1) {
                                ntf('Permission Updated', 'success');
                            }
                        }).catch((error) => {
                            ntf(error, 'error');
                        });
                    } else {
                        ntf('Select a User or  User Role First', 'error');
                    }

                });
            });
        </script>
    </x-slot>
    <div class="data-list">
        <form id="permissionCenter" method="post" action="{{ route('permissionPut') }}">
            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-title">
                        <h2>Permission Center</h2>
                    </div>
                    <div class="data-controller">
                        <div class="customSelect">
                            <div class="customSelectTog" onclick="customSelect(event,this)">
                                Select User or User label
                            </div>
                            <div class="optionItems" style="display:none">
                                <label class="optionGroupLabel">User Roles <a class="popup"
                                        href="permission/create-role">New</a></label>
                                @foreach ($roles as $role)
                                    <div class="optionitem-wrap">
                                        <a class="custom-select-option" onclick="getPermission(event,this)"
                                            data-type="role" href="javascript:void(0)"
                                            data-id="{{ $role->id }}">{{ $role->name }}</a>
                                        <a class="roleDelete" onclick="deleteData(event,this)"
                                            href="permission/role/delete/{{ $role->id }}"
                                            data-csrf="{{ csrf_token() }}">×</a>
                                    </div>
                                @endForeach
                                <label class="optionGroupLabel">Users</label>
                                @foreach ($users as $user)
                                    <a class="custom-select-option" onclick="getPermission(event,this)"
                                        href="javascript:void(0)" data-type="user"
                                        data-id="{{ $user->id }}">{{ $user->name }}</a>
                                @endForeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="permission-center">
                    <div class="row">
                        <div class="col w4">
                            <div class="permissionGroup">
                                <div class="group-label">
                                    <label>Mail Box Access</label>
                                </div>
                                <div class="group-access">
                                    @foreach ($modulesInfo->labels as $k => $Mailbox)
                                        <div class="accessItem">
                                            <div class="accessItemPermission">
                                                <input id="box_{{ $k }}" class="toggle"
                                                    name="permission[box][{{ $k }}]" type="checkbox"
                                                    value="1">
                                            </div>
                                            <div class="accessItemName">
                                                {{ $Mailbox['label'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col w8">
                            <div class="permissionGroup">
                                <div class="group-label">
                                    <label>Settings Access</label>
                                </div>
                                <div class="group-access">
                                    @foreach ($modulesInfo->settingModules() as $k => $settingModule)
                                        @if ($k != 'permission')
                                            <div class="accessItem">
                                                <div class="accessItemName">
                                                    {{ $settingModule['label'] }}
                                                </div>

                                                <div class="accessItemPermission">
                                                    <input id="settings_{{ $k }}_view"
                                                        name="permission[settings][{{ $k }}][view]"
                                                        class="toggle" type="checkbox" value="1">
                                                    <div class="p_title">View</div>
                                                </div>

                                                <div class="accessItemPermission">
                                                    <input id="settings_{{ $k }}_write"
                                                        name="permission[settings][{{ $k }}][write]"
                                                        class="toggle" type="checkbox" value="1">
                                                    <div class="p_title">Write</div>
                                                </div>
                                                <div class="accessItemPermission">
                                                    <input id="settings_{{ $k }}_delete"
                                                        name="permission[settings][{{ $k }}][delete]"
                                                        class="toggle" type="checkbox" value="1">
                                                    <div class="p_title">Delete</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="permissionGroup">
                                <div class="group-label">
                                    <label>Mail Action Access</label>
                                </div>
                                <div class="group-access accessInline">
                                    <div class="accessItem">
                                        <div class="accessItemPermission">
                                            <input id="mailAction_reply" name="permission[mailAction][reply]"
                                                class="toggle" type="checkbox" value="1">
                                        </div>
                                        <div class="accessItemName">Reply</div>
                                    </div>
                                    <div class="accessItem">
                                        <div class="accessItemPermission">
                                            <input id="mailAction_edit" name="permission[mailAction][edit]"
                                                class="toggle" type="checkbox" value="1">
                                        </div>
                                        <div class="accessItemName">Edit</div>
                                    </div>
                                    <div class="accessItem">
                                        <div class="accessItemPermission">
                                            <input id="mailAction_forward" name="permission[mailAction][forward]"
                                                class="toggle" type="checkbox" value="1">
                                        </div>
                                        <div class="accessItemName">Foreword</div>
                                    </div>
                                    <div class="accessItem">
                                        <div class="accessItemPermission">
                                            <input id="mailAction_trash" name="permission[mailAction][trash]"
                                                class="toggle" type="checkbox" value="1">
                                        </div>
                                        <div class="accessItemName">Trash</div>
                                    </div>
                                    <div class="accessItem">
                                        <div class="accessItemPermission">
                                            <input id="mailAction_delete" name="permission[mailAction][delete]"
                                                class="toggle" type="checkbox" value="1">
                                        </div>
                                        <div class="accessItemName">Delete</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="data-table-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
