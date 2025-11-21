<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue",
                Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol",
                "Noto Color Emoji";
            /* Fondo personalizado */
            background-image: url('{{ asset('img/Pages/Fondo-mision.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .error-container {
            background-color: rgba(0, 0, 0, 0.55);
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .error-message {
            font-size: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-code" style="color:#ffeb26">@yield('code')</div>
        <div class="error-message">@yield('message')</div>
    </div>
</body>

</html>
