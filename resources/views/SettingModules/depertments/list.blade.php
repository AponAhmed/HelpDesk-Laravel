
<x-app-layout title="Settings : Department List">
    <x-slot name="script">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let cPage = 1;
    
                window.LoadData=function (customRoute, routeSufix) {
                    let route = "/settings/department/list-data";
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
                        model: 'Department',
                        columns: {
                            name: {
                                visible: true,
                                title: "Name",
                                //width: "20%",
                                filter: function(data, row) {
                                    return `<a data-w="500"  href="/settings/department/update/${row.id}">${data}</a>`;
                                }
                            },
                            prefix: {
                                visible: true,
                                title: "Prefix"
                            },
                            email: {
                                visible: true,
                                title: "Email",
                                filter: function(data, row) {
                                    let OAuth =
                                        `<button  class="btn btn-primary" onclick='AuthLogin(${row.id})'>Login With OAuth</button>`;
                                    if (row.OAuth === true) {
                                        OAuth = `<span class="btn btn-success">Loged In</span>`;
                                    } else if (row.OAuth == 'expired') {
                                        OAuth =
                                            `<button class="btn btn-danger" onclick='AuthLogin(${row.id},true)'>Re Login</button>`;
                                    }
                                    return `${data} ${OAuth}`;
                                }
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
                                url: "/settings/department/update/{id}",
                                title: 'Edit',
                            },
                            delete: {
                                url: "/settings/department/delete/{id}",
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
    
                function LoadData__(p) {
                    let dataWrap = document.querySelector(".js-dt");
                    var route = dataWrap.getAttribute("data-route");
                    if (p) {
                        route = p;
                    }
                    axios
                        .get(route)
                        .then(function(response) {
                            let responseData = response.data;
                            let data = responseData.data;
                            //data row build
                            var rows = "";
                            for (i = 0; i < data.length; i++) {
                                let dataRow = data[i];
                                let OAuth =
                                    `<button  class="btn btn-primary" onclick='AuthLogin(${data[i].id})'>Login With OAuth</button>`;
                                if (data[i].OAuth === true) {
                                    OAuth = `<span class="btn btn-success">Loged In</span>`;
                                } else if (data[i].OAuth == 'expired') {
                                    OAuth =
                                        `<button class="btn btn-danger" onclick='AuthLogin(${data[i].id},true)'>Re Login</button>`;
                                }
                                rows += `<tr>
                    <td>${data[i].name}</td>
                    <td>${data[i].prefix}</td>
                    <td>${data[i].email}</td>
                    <td>${data[i].created_at}</td>
                    <td>
                        ${OAuth}
                        &nbsp;&nbsp;<a  data-w='500' href="{{ URL('settings/department/update/${data[i].id}') }}">Edit</a> | <a onclick="deleteData(event,this)" href="{{ URL('settings/department/delete/${data[i].id}') }}" data-csrf="{{ csrf_token() }}">Delete</a></td>
                </tr>`;
                            }
                            $(dataWrap).find('tbody').html(rows);
                            //pagination
                            p = new pagination(responseData);
                            $(".pagination").html(p.linksHtm());
                        })
                        .catch(function(error) {
                            console.log(error);
                            //ntf(error.response.data,'error');
                        });
                }
            });
        </script>
    </x-slot>

    <div class="user-list data-list">
        <div class="data-table-wrap">
            <div class="data-table-header">
                <div class="data-title">
                    <h2>Depertment </h2>
                </div>
                <div class="data-controller">
                    <a class="btn-new" data-w='500' href="{{ URL('settings/department/new') }}"><svg
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
</x-app-layout>
