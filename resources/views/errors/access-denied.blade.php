<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>403 Not Authorized !</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

</head>

<body>
    <div class="container mt-5 pt-5">
        <div class="alert alert-danger text-center" style="
    display: flex;
    align-items: center;
">
            <h2 class="display-3"
                style="
    padding-right: 15px;
    margin-right: 20px;
    border-right: 1px solid rgb(255 255 255);
    margin-bottom: 0;
">
                403</h2>
            <p class="display-5" style="
    margin: 0;
">
                @if (Session::get('message'))
                    {{ Session::get('message') }}
                @else
                    Access Denied, You haven't Permission to access this Section
                @endif

            </p>
        </div>
    </div>
</body>

</html>
