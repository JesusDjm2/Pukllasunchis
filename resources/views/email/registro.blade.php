<!DOCTYPE html>
<html>

<head>
    <title>Registro Completado</title>
</head>

<body>
    <h1>Notificación de Registro Completado</h1>
    <p style="border-bottom: 1px dashed grey; margin-bottom:5px; padding-bottom:5px">
        <span style="font-weight: bold">Alumno:</span> <span>{{ $nombres }} {{ $apellidos }}</span>
    </p>
    <p style="border-bottom: 1px dashed grey; margin-bottom:5px; padding-bottom:5px">
        <span style="font-weight: bold">Programa - Ciclo:</span> <span>{{ $programa }} - {{ $ciclo }}</span>
    </p>
    <p style="border-bottom: 1px dashed grey; margin-bottom:5px; padding-bottom:5px">
        <span style="font-weight: bold">DNI:</span> <span>{{ $dni }}</span>
    </p>
    <p style="border-bottom: 1px dashed grey; margin-bottom:5px; padding-bottom:5px">
        <span style="font-weight: bold">Número de comprobante:</span> <span>{{ $num_comprobante }}<span>
    </p>
</body>

</html>
