<?php
// Configuración de carpetas
$carpeta_img = 'assets/img/';
$archivo_datos = 'assets/js/datos.json';

// Si el archivo JSON no existe, lo creamos vacío
if (!file_exists($archivo_datos)) {
    file_put_contents($archivo_datos, '[]');
}

// ==========================================
// 1. LÓGICA: SUBIR NUEVO CATÁLOGO
// ==========================================
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

// ==========================================
// 2. LÓGICA: MARCAR / DESMARCAR VENDIDAS
// ==========================================
if (isset($_GET['marcar_vendida'])) {
    $id_joya = (int)$_GET['marcar_vendida'];
    $vendidas = json_decode(file_get_contents($archivo_datos), true);
    
    // Si la joya ya estaba vendida, la quitamos (DESMARCAR). Si no, la agregamos (MARCAR).
    if (($key = array_search($id_joya, $vendidas)) !== false) {
        unset($vendidas[$key]);
    } else {
        $vendidas[] = $id_joya;
    }
    
    // Guardamos los cambios
    file_put_contents($archivo_datos, json_encode(array_values($vendidas)));
    exit; // Terminamos aquí porque es una acción invisible
}

// Leemos las ventas y contamos fotos
$joyas_vendidas = json_decode(file_get_contents($archivo_datos), true);
$total_fotos_actuales = count(glob($carpeta_img . '*.{jpg,jpeg,png}', GLOB_BRACE));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Lunae</title>
    <style>
        /* Diseño Blanco y Luminoso para el Panel */
        body { font-family: 'Montserrat', Arial, sans-serif; background-color: #ffffff; color: #333333; padding: 20px; text-align: center; margin: 0; }
        h1 { color: #D4AF37; letter-spacing: 2px; text-transform: uppercase; font-family: 'Bodoni Moda', serif;}
        h2 { color: #B76E79; font-weight: 400;}
        p { color: #666666; font-size: 1.1em; line-height: 1.5; }
        
        .tarjeta { background: #ffffff; padding: 30px; border-radius: 10px; border: 1px solid #eeeeee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto 30px; }
        
        /* Botones y Alertas */
        .btn-lujo { background-color: #D4AF37; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%; max-width: 300px; margin-top: 10px;}
        .btn-lujo:hover { background-color: #B76E79; transform: scale(1.02); }
        
        .mensaje { background-color: #fdfaf0; color: #b8901e; border: 1px solid #D4AF37; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        
        /* Galería de Admin */
        .galeria { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 20px; }
        .foto-admin { width: 110px; height: 110px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 4px solid transparent; transition: all 0.3s ease; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .foto-admin.vendida { border-color: #B76E79; opacity: 0.4; transform: scale(0.9); }
        
        input[type="file"] { margin-bottom: 20px; font-family: 'Montserrat', sans-serif;}
    </style>
</head>
<body>

    <h1 style="margin-top: 20px;">LUNAE - PANEL</h1>

    <?php if(isset($mensaje_exito)) echo "<div class='mensaje'>$mensaje_exito</div>"; ?>

    <div class="tarjeta">
        <h2>1. Nuevo Catálogo</h2>
        <p>Selecciona todas las fotos nuevas de tu celular. <br><b>⚠️ Esto borrará el catálogo anterior.</b></p>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="fotos[]" multiple accept="image/*" required>
            <br>
            <button type="submit" name="subir_catalogo" class="btn-lujo">Subir y Reiniciar</button>
        </form>
    </div>

    <div class="tarjeta" style="max-width: 900px;">
        <h2>2. Control de Ventas</h2>
        <p>Toca la foto de la joya que ya se vendió. Los cambios se guardan solos.<br><b>💡 Tip: Si te equivocas, vuelve a tocar la foto para desmarcarla.</b></p>
        
        <div class="galeria">
            <?php
            for ($i = 1; $i <= $total_fotos_actuales; $i++) {
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
        // JavaScript que se comunica con PHP sin recargar la página
        function marcarVendida(elementoImg, numeroJoya) {
            fetch('admin.php?marcar_vendida=' + numeroJoya)
            .then(() => {
                elementoImg.classList.toggle('vendida');
            });
        }
    </script>

</body>
</html>