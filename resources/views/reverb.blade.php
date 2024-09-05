<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])    
</head>

<body>
    <script>
        setTimeout(() => {
            window.Echo.channel('testChannel')
                .listen('TestEvent', (event) => {
                    console.log(event.message); // Ensure you are accessing the correct key
                });
        }, 1000);
    </script>
</body>

</html>
