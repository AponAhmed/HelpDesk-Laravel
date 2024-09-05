<x-app-layout title="Settings : Users List">
    <div class="user-list data-list">

        <div class="data-table-wrap">
            <div class="data-table-header">
                <div class="data-title">
                    <h2>Users </h2>
                </div>
                <div class="data-controller">
                    <a class="popup btn-new" data-w='500' href="{{ URL('settings/user/new') }}"><svg
                            xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                            <title>Add</title>
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="32" d="M256 112v288M400 256H112" />
                        </svg> New</a>
                </div>
            </div>
            <div class="data-table-content">
                <div id="datalist"></div>
            </div>
        </div>
    </div>

    <x-slot name="script">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let cPage = 1;

                window.LoadData = function(customRoute, routeSufix) {
                    let route = "/settings/user/list-data";
                    if (customRoute) {
                        route = customRoute;
                    }
                    if (routeSufix) {
                        route += routeSufix;
                    }
                    let dataWrap = document.querySelector("#datalist");
                    let databulder = new DataBuilder(dataWrap, {
                        apiRoute: route,
                        currentPage: cPage,
                        model: 'User',
                        columns: {
                            name: {
                                visible: true,
                                title: "Name",
                                //width: "20%",
                                filter: function(data, row) {
                                    return `<a class='popup' data-w="500"  href="/settings/user/update/${row.id}">${data}</a>`;
                                }
                            },
                            roles: {
                                visible: true,
                                title: "User Role",
                            },
                            email: {
                                visible: true,
                                title: "Email"
                            },
                            status: {
                                visible: true,
                                title: "Status",
                                dataFilter: { //Data Filter is used to filter the data list based on the value(k) of the column(status).
                                    callback: 'filterData',
                                    sections: {
                                        1: "Active",
                                        0: "Inactive",
                                    }
                                },
                                width: "15%",
                                filter: function(status, row) {
                                    switch (status) {
                                        case "1":
                                            return `<a href="javascript:void(0)" onclick="filterData(event,'status','${status}')">Active</a>`;
                                        case "0":
                                            return `<a href="javascript:void(0)" onclick="filterData(event,'status','${status}')">Inactive</a>`;
                                        default:
                                            return "";
                                    }
                                }
                            }
                        },
                        actions: {
                            edit: {
                                url: "/settings/user/update/{id}",
                                title: 'Edit',
                                class: "popup",
                                attr: {
                                    "data-w": "500"
                                }
                            },
                            delete: {
                                url: "/settings/user/delete/{id}",
                                title: 'Delete',
                                class: "",
                                attr: {
                                    'onclick': "deleteData(event,this)",
                                }
                            }
                        },
                        rowClassFilter: function(row) {
                            switch (row.status) {
                                case "1":
                                    return "data-row-ok";
                                    break;
                                case "0":
                                    return "inactive";
                                    break;
                                default:
                                    return "data-row-item";
                                    break;
                            }
                        },
                        startProcess: function() {
                            window.DataPath = route;
                            inProcess();
                        },
                        endProcess: function() {
                            new popup();
                            completaProcess();
                            //icon.init();
                        }
                    });
                    cPage = databulder.currentPage;
                }
                //initial call
                LoadData();
            });
        </script>
    </x-slot>
</x-app-layout>
