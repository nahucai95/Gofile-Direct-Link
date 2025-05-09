<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $json = file_get_contents("https://gofile-direct-link.onrender.com/?url=" . urlencode($url));
    $data = json_decode($json, true);

    if ($data['status'] == 'ok') {
        echo "<div class='container'>";
        echo "<h1>Enlace de descarga</h1>";
        echo "<p><a href='" . $data['links'][0]['link'] . "'>" . $data['links'][0]['file'] . "</a></p>";
        echo "</div>";
    } else {
        echo "<p>Error al obtener el enlace de descarga.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obtener Enlace de Descarga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <h1>Obtener enlace de descarga</h1>
        <form action="index.php" method="get">
            <div class="mb-3">
                <label for="url" class="form-label">URL de Gofile</label>
                <input type="text" class="form-control" id="url" name="url" placeholder="Introduce el enlace de Gofile" required>
            </div>
            <button type="submit" class="btn btn-primary">Obtener enlace</button>
        </form>
    </div>
</body>
</html>
