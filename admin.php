<?php
// Configuración
$carpeta_img = 'assets/img/';
$archivo_datos = 'assets/js/datos.json';

// Si el archivo JSON no existe por alguna razón, lo creamos
if (!file_exists($archivo_datos)) {
    file_put_contents($archivo_datos, '[]');
}

// 1. LÓGICA: Cuando tu mamá sube un nuevo catálogo
if (isset($_POST['subir_catalogo'])) {
    // A) Borramos las fotos viejas
    $fotos_viejas = glob($carpeta_img . '*');
    foreach($fotos_viejas as $f) { if(is_file($f)) unlink($f); }

    // B) Reiniciamos las ventas a cero
    file_put_contents($archivo_datos, '[]');

    // C) Procesamos las fotos nuevas, les ponemos 1.jpg, 2.jpg...
    $contador = 1;
    if(isset($_FILES['fotos'])) {
        $total = count($_FILES['fotos']['name']);
        for($i = 0; $i < $total; $i++) {
            $tmp = $_FILES['fotos']['tmp_name'][$i];
            if ($tmp != ""){
                $ext = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
                // Si es un formato válido
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'heic'])) {
                    $nuevo_nombre = $carpeta_img . $contador . '.' . $ext;
                    move_uploaded_file($tmp, $nuevo_nombre);
                    $contador++;
                }
            }
        }
    }
    $mensaje_exito = "¡Catálogo subido con éxito! Se procesaron " . ($contador - 1) . " fotos.";
}

// 2. LÓGICA INVISIBLE: Cuando ella toca una foto para marcarla vendida
if (isset($_GET['marcar_vendida'])) {
    $id_joya = (int)$_GET['marcar_vendida'];
    $vendidas = json_decode(file_get_contents($archivo_datos), true);
    
    // Si la joya ya estaba vendida, la quitamos de la lista. Si no, la agregamos.
    if (($key = array_search($id_joya, $vendidas)) !== false) {
        unset($vendidas[$key]);
    } else {
        $vendidas[] = $id_joya;
    }
    
    // Guardamos los cambios
    file_put_contents($archivo_datos, json_encode(array_values($vendidas)));
    exit; // Terminamos aquí porque es una acción invisible
}

// Leemos las ventas actuales para mostrarlas en la pantalla
$joyas_vendidas = json_decode(file_get_contents($archivo_datos), true);
$total_fotos_actuales = count(glob($carpeta_img . '*.{jpg,jpeg,png}', GLOB_BRACE));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
<style>
        body { font-family: 'Arial', sans-serif; background-color: #0a0a0a; color: #E0BFB8; padding: 20px; text-align: center; }
        h1 { color: #D4AF37; letter-spacing: 2px; }
        h2 { color: #B76E79; }
        p { color: #cccccc; }
        .tarjeta { background: #111111; padding: 20px; border-radius: 10px; border: 1px solid #B76E79; box-shadow: 0 4px 15px rgba(0,0,0,0.5); max-width: 600px; margin: 0 auto 30px; }
        .btn-subir { background-color: #D4AF37; color: black; padding: 15px 30px; border: none; border-radius: 5px; font-size: 1.2em; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-subir:hover { background-color: #B76E79; }
        .mensaje { background-color: rgba(212, 175, 55, 0.2); color: #D4AF37; border: 1px solid #D4AF37; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .galeria { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .foto-admin { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 3px solid transparent; transition: 0.3s; }
        .foto-admin.vendida { border-color: #D4AF37; opacity: 0.3; transform: scale(0.9); }
    </style>
</head>
<body>

    <h1>👑 Panel de Control - Joyería</h1>

    <?php if(isset($mensaje_exito)) echo "<div class='mensaje'>$mensaje_exito</div>"; ?>

    <div class="tarjeta">
        <h2>1. Subir Nuevo Catálogo</h2>
        <p>Selecciona todas las fotos nuevas de tu celular. <b>Esto borrará el catálogo anterior.</b></p>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="fotos[]" multiple accept="image/*" required style="margin-bottom: 20px;">
            <br>
            <button type="submit" name="subir_catalogo" class="btn-subir">Subir Fotos y Reiniciar</button>
        </form>
    </div>

    <div class="tarjeta" style="max-width: 900px;">
        <h2>2. Marcar Joyas Vendidas</h2>
        <p>Toca la foto de la joya que ya se vendió (se pondrá roja). Los cambios se guardan solos.</p>
        
        <div class="galeria">
            <?php
            // Generamos las fotos que haya actualmente en la carpeta
            for ($i = 1; $i <= $total_fotos_actuales; $i++) {
                // Buscamos si existe la foto en jpg, jpeg o png
                $ruta = "";
                if (file_exists($carpeta_img . $i . '.jpg')) $ruta = $carpeta_img . $i . '.jpg';
                elseif (file_exists($carpeta_img . $i . '.jpeg')) $ruta = $carpeta_img . $i . '.jpeg';
                elseif (file_exists($carpeta_img . $i . '.png')) $ruta = $carpeta_img . $i . '.png';

                if ($ruta != "") {
                    $clase_vendida = in_array($i, $joyas_vendidas) ? "vendida" : "";
                    echo "<img src='$ruta' class='foto-admin $clase_vendida' onclick='marcarVendida(this, $i)'>";
                }
            }
            ?>
        </div>
    </div>

    <script>
        // Magia para que tu mamá no tenga que recargar la página al tocar una foto
        function marcarVendida(elementoImg, numeroJoya) {
            // Llama a PHP en secreto
            fetch('admin.php?marcar_vendida=' + numeroJoya)
            .then(() => {
                // Cambia el diseño de la foto inmediatamente
                elementoImg.classList.toggle('vendida');
            });
        }
    </script>
</body>
</html>