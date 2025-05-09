<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Extraer enlace de GoFile</title>
</head>
<body>
    <h2>Insertar enlace de GoFile</h2>
    <form method="post">
        <input type="text" name="gofile_url" placeholder="https://gofile.io/d/..." size="60" required>
        <button type="submit">Obtener enlace</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["gofile_url"])) {
        $url = escapeshellarg($_POST["gofile_url"]);
        $output = shell_exec("python3 run.py $url 2>&1");

        echo "<h3>Resultado:</h3><pre>$output</pre>";
    }
    ?>
</body>
</html>
