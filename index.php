<?php
// Procesar la solicitud al recibir la URL
$downloadLink = null;
if (isset($_GET['url'])) {
    $url = $_GET['url'];

    // Realiza la solicitud al endpoint de Render para obtener el enlace de descarga
    $apiUrl = "https://gofile-direct-link.onrender.com/?url=" . urlencode($url);
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    // Verificar si el enlace se obtuvo correctamente
    if (isset($data['links'][0]['link'])) {
        $downloadLink = $data['links'][0]['link'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obtener enlace de descarga</title>
    <!-- Agregar Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Obtener enlace de descarga</h1>

        <form action="index.php" method="get" class="mb-4">
            <div class="form-group">
                <label for="url">Enlace Gofile:</label>
                <input type="text" class="form-control" id="url" name="url" placeholder="Ingresa el enlace de Gofile" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Obtener enlace</button>
        </form>

        <?php if ($downloadLink): ?>
            <div class="alert alert-success">
                <strong>Enlace de descarga:</strong> <a href="<?php echo $downloadLink; ?>" target="_blank"><?php echo $downloadLink; ?></a>
            </div>
        <?php elseif (isset($_GET['url'])): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> No se pudo obtener el enlace de descarga. Verifica el enlace de Gofile.
            </div>
        <?php endif; ?>

    </div>

    <!-- Agregar Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

