@extends('layout.app')

@section('title', 'Settings : Users List')

@section('mainBody')
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
                    </svg> New</a><br>
            </div>
        </div>
        <table class="data-table js-dt" data-route="user/list-data">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>User Role</th>
                    <th>Created at</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="pagination"></div>
</div>
@endsection

@section('script')
<script>
    //Data table JS
    LoadData();

    window.LoadData = function(p) {
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
                    console.log(dataRow);
                    rows += `<tr>
                <td>${data[i].name}</td>
                <td>${data[i].email}</td>
                <td>${dataRow.roles}</td>
                <td>${data[i].created_at}{{ method_field('DELETE') }}</td>
                <td><a class="popup" data-w='500' href="{{ URL('settings/user/update/${dataRow.id}') }}">Edit</a> | <a onclick="deleteData(event,this)" href="{{ URL('settings/user/delete/${data[i].id}') }}" data-csrf="{{ csrf_token() }}">Delete</a></td>
            </tr>`;
                }
                $(dataWrap).find('tbody').html(rows);
                new popup;
                //Data placed
                //pagination
                p = new pagination(responseData);
                $(".pagination").html(p.linksHtm());

            })
            .catch(function(error) {
                console.log(error);
            });
    }
</script>
@endsection
<!--<div class="mail-view">View</div>-->