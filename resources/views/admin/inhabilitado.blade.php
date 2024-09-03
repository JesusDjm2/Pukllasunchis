<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Inhabilitado</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/logoiesp.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/Icono-Puklla.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }

        .background {
            background-image: url('../img/fondos/preocupada-en-la-laptop.webp');
            background-size: cover;
            background-position: center;
            height: 100vh;
            width: 100%;
            position: relative;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.5);
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .content {
            text-align: center;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>

<body>
    <div class="background">
        <div class="overlay">
            <div class="content">
                <div class="alert alert-danger" role="alert">
                    Tu perfil está <strong> Inhabilitado.</strong> Por favor, comunícate con las oficinas de
                    administración de la Escuela.
                </div>
                <form action="/logout" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn btn-primary">Regresar a la página principal</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
