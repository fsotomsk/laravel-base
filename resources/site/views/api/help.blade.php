<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body class="disabled-is-preloader">
<div class="page-wrapper">
    <section class="container padding-top-3x">
        @include('api.help-table', ['class' => $class, 'routes' => $routes])
        <hr noshade="noshade">
        @foreach ( $routes_all as $class => $routes )
            @include('api.help-table', ['class' => $class, 'routes' => $routes])
        @endforeach
    </section>
</div>
</body>
</html>