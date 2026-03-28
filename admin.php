<?php
// admin.php - Panel exclusivo para tu mamá

$carpeta_destino = 'assets/img/';
$archivo_datos = 'assets/js/datos.json'; // Ahora usaremos un .json, es más fácil para PHP

// 1. ¿Tu mamá presionó el botón de subir nuevo catálogo?
if (isset($_POST['subir_catalogo'])) {
    
    // A. Borramos las fotos viejas del servidor
    $fotos_viejas = glob($carpeta_destino . '*');
    foreach($fotos_viejas as $foto) {
        if(is_file($foto)) {
            unlink($foto); 
        }
    }

    // B. Reiniciamos la lista de joyas vendidas (Catálogo nuevo = Todo disponible)
    file_put_contents($archivo_datos, json_encode([]));

    // C. Procesamos las fotos nuevas y las renombramos
    $contador = 1;
    foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
        // Extraemos el formato original (jpg, jpeg, png)
        $nombre_original = $_FILES['imagenes']['name'][$key];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        
        // Creamos el nuevo nombre (1.jpg, 2.jpg...)
        $nuevo_nombre = $contador . '.' . $extension;
        $ruta_final = $carpeta_destino . $nuevo_nombre;
        
        // Movemos la foto al servidor
        move_uploaded_file($tmp_name, $ruta_final);
        $contador++;
    }
    
    $mensaje = "¡Éxito! El nuevo catálogo se ha subido y las ventas se han reiniciado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    </head>
<body>
    <h1>Panel de Control de Mamá 👑</h1>
    
    <?php if(isset($mensaje)) echo "<p><strong>$mensaje</strong></p>"; ?>

    <h2>1. Subir Nuevo Catálogo</h2>
    <p>Sube todas las fotos nuevas. Esto borrará el catálogo anterior y pondrá todo como "Disponible".</p>
    
    <form action="admin.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="imagenes[]" multiple accept="image/*" required>
        <button type="submit" name="subir_catalogo">Subir Catálogo Completo</button>
    </form>

    <hr>

    <h2>2. Marcar Joyas Vendidas</h2>
    <p>Toca la foto de la joya que ya se vendió.</p>
    </body>
</html>