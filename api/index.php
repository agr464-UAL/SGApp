
<?php

// Configuración de conexión
$host = getenv('MYSQL_HOST');
$user = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$database = 'SG';

$conexion = null;
$error = null;
$resultado = null;

// Comprobar variables de entorno
if ($host === false || $host === '' ||
    $user === false || $user === '' ||
    $password === false || $password === '') {

    $error = "Faltan variables de entorno en Vercel.";

} else {

    try {
        // Activar excepciones de MySQLi
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Conectar con Cloud SQL
        $conexion = new mysqli(
            $host,
            $user,
            $password,
            $database
        );

        // Codificación de caracteres
        $conexion->set_charset("utf8mb4");

        // Consultar catálogo
        $cadenaSQL = "SELECT * FROM s_customer";
        $resultado = $conexion->query($cadenaSQL);

    } catch (mysqli_sql_exception $e) {

        // Registrar el error sin mostrar credenciales
        error_log("Error MySQL: " . $e->getMessage());

        $error = "Error de MySQL (código " .
                 $e->getCode() . "): " .
                 $e->getMessage();

    } catch (Throwable $e) {

        error_log("Error PHP: " . $e->getMessage());
        $error = "Se ha producido un error en la aplicación.";
    }
}

// Escapar contenido HTML
function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customer Catalog</title>

    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
</head>

<body>

<div class="container mt-4">

    <div class="jumbotron">
        <h1 class="display-4">Customer Catalog</h1>
        <p class="lead">Customer Catalog Sample Application</p>
        <hr class="my-4">
        <p>PHP sample application connected to a MySQL database</p>
    </div>

    <?php if ($error !== null): ?>

        <div class="alert alert-danger">
            <h4>Error de conexión o consulta</h4>
            <p><?php echo escapar($error); ?></p>
        </div>

    <?php elseif ($resultado !== null): ?>

        <div class="alert alert-success">
            Conexión con MySQL establecida correctamente.
        </div>

        <table class="table table-striped table-responsive">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Credit Rating</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Country</th>
                    <th>Zip</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($fila = $resultado->fetch_object()): ?>

                    <tr>
                        <td><?php echo escapar($fila->name); ?></td>
                        <td><?php echo escapar($fila->credit_rating); ?></td>
                        <td><?php echo escapar($fila->address); ?></td>
                        <td><?php echo escapar($fila->city); ?></td>
                        <td><?php echo escapar($fila->state); ?></td>
                        <td><?php echo escapar($fila->country); ?></td>
                        <td><?php echo escapar($fila->zip_code); ?></td>
                    </tr>

                <?php endwhile; ?>

            </tbody>
        </table>

        <?php if ($resultado->num_rows === 0): ?>
            <div class="alert alert-warning">
                La tabla s_customer no contiene registros.
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>

<?php
if ($conexion !== null) {
    $conexion->close();
}
?>
