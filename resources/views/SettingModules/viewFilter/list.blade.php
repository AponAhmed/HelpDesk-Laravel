<x-app-layout title="Settings : View Filter Setup">
    <div class="user-list data-list">
        <div class="data-table-wrap">
            <div class="data-table-header">
                <div class="data-title">
                    <h2>View Filter </h2>
                </div>
                <div class="data-controller">
                    <a class="popup btn-new" data-w='500' href="{{ URL('settings/view-filter/new') }}"><svg
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
                    let route = "/settings/view-filter/list-data";
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
                        model: 'ViewFilter',
                        columns: {
                            department_name: {
                                visible: true,
                                title: "Department",
                            },
                            role: {
                                visible: true,
                                title: "Filter Role"
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
                            },
                            created_by: {
                                visible: true,
                                title: "Created By"
                            },
                            created_at: {
                                visible: true,
                                title: "Created At"
                            }
                        },
                        actions: {
                            edit: {
                                url: "/settings/view-filter/update/{id}",
                                title: 'Edit',
                                class: "popup",
                                attr: {
                                    "data-w": "500"
                                }
                            },
                            delete: {
                                url: "/settings/view-filter/delete/{id}",
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
