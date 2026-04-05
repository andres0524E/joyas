<?php
// --- 🔒 SEGURIDAD EXTREMA ---
// 1. La sesión muere al cerrar el navegador
session_set_cookie_params(0); 
session_start();

$usuario_secreto = "ElydaRI";
$contrasena_secreta = "eraigam07"; 
$tiempo_inactividad = 1800; // 30 minutos

// 2. Control de inactividad
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > $tiempo_inactividad)) {
    session_unset(); session_destroy();
    header("Location: admin.php?expirado=true");
    exit;
}
$_SESSION['ultimo_acceso'] = time();

// Cierre voluntario
if (isset($_GET['salir'])) {
    session_unset(); session_destroy();
    header("Location: admin.php");
    exit;
}

$error_login = "";
if (isset($_GET['expirado'])) $error_login = "Tu sesión ha expirado por seguridad.";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (($_POST['username'] ?? '') === $usuario_secreto && ($_POST['password'] ?? '') === $contrasena_secreta) {
        $_SESSION['logueado'] = true; $_SESSION['usuario'] = $_POST['username'];
    } else { $error_login = "Usuario o contraseña incorrectos."; }
}

// ==========================================
// PANTALLA DE LOGIN
// ==========================================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro - Lunae</title>
    <link rel="icon" type="image/png" href="fiveicon.png?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px;}
        .tarjeta-login { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 100%; border: 1px solid #eeeeee;}
        h1 { color: #D4AF37; margin-bottom: 30px; font-family: 'Cinzel', serif; letter-spacing: 2px;}
        input, button { width: 100%; padding: 15px; margin-bottom: 15px; border-radius: 5px; font-size: 1.1em; font-family: 'Montserrat', sans-serif;}
        input { border: 1px solid #ccc; box-sizing: border-box; }
        button { background-color: #D4AF37; color: white; border: none; font-weight: bold; cursor: pointer; transition: 0.3s;}
        button:hover { background-color: #B76E79;}
        .error { color: #d9534f; margin-bottom: 15px; font-weight: bold;}
    </style>
</head>
<body>
    <div class="tarjeta-login">
        <h1>LUNAE</h1>
        <?php if($error_login) echo "<p class='error'>$error_login</p>"; ?>
        <form action="admin.php" method="POST">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login">Entrar al Panel</button>
        </form>
    </div>
</body>
</html>
<?php exit; endif;

// ==========================================
// PANEL PROTEGIDO (Nuevas Funciones)
// ==========================================
$carpeta_img = 'assets/img/';
$archivo_datos = 'assets/js/datos.json';

// Formato nuevo de BD (Soporta vendidas y ocultas)
if (!file_exists($archivo_datos)) {
    file_put_contents($archivo_datos, json_encode(['vendidas' => [], 'ocultas' => []]));
}

// Lógica: Subir Catálogo
if (isset($_POST['subir_catalogo'])) {
    $fotos_viejas = glob($carpeta_img . '*');
    foreach($fotos_viejas as $f) { if(is_file($f)) unlink($f); }
    file_put_contents($archivo_datos, json_encode(['vendidas' => [], 'ocultas' => []]));
    $contador = 1;
    if(isset($_FILES['fotos'])) {
        $total = count($_FILES['fotos']['name']);
        for($i = 0; $i < $total; $i++) {
            $tmp = $_FILES['fotos']['tmp_name'][$i];
            if ($tmp != ""){
                $ext = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'heic'])) {
                    move_uploaded_file($tmp, $carpeta_img . $contador . '.' . $ext);
                    $contador++;
                }
            }
        }
    }
    $mensaje_exito = "¡Catálogo subido! Se procesaron " . ($contador - 1) . " fotos.";
}

// Lógica: Limpiar todas las ventas
if (isset($_GET['limpiar_ventas'])) {
    $datos = json_decode(file_get_contents($archivo_datos), true);
    // Solo borramos las vendidas, las ocultas se quedan ocultas
    $nuevos_datos = ['vendidas' => [], 'ocultas' => $datos['ocultas'] ?? []];
    file_put_contents($archivo_datos, json_encode($nuevos_datos));
    header("Location: admin.php?mensaje=limpio");
    exit;
}

// Lógica: Comunicación AJAX (Marcar/Ocultar)
if (isset($_GET['accion_joya'])) {
    $id_joya = (int)$_GET['accion_joya'];
    $tipo_accion = $_GET['tipo']; // 'vender' o 'ocultar'
    
    // Leemos la BD, si es vieja la actualizamos al nuevo formato
    $raw_data = json_decode(file_get_contents($archivo_datos), true);
    if(isset($raw_data[0])) $datos = ['vendidas' => $raw_data, 'ocultas' => []]; // Migración
    else $datos = $raw_data;

    $lista = &$datos[$tipo_accion === 'vender' ? 'vendidas' : 'ocultas'];
    
    if (($key = array_search($id_joya, $lista)) !== false) unset($lista[$key]); // Quitar
    else $lista[] = $id_joya; // Agregar

    file_put_contents($archivo_datos, json_encode(['vendidas' => array_values($datos['vendidas']), 'ocultas' => array_values($datos['ocultas'])]));
    exit; 
}

$datos_actuales = json_decode(file_get_contents($archivo_datos), true);
$joyas_vendidas = $datos_actuales['vendidas'] ?? (is_array($datos_actuales) ? $datos_actuales : []);
$joyas_ocultas = $datos_actuales['ocultas'] ?? [];
$total_fotos_actuales = count(glob($carpeta_img . '*.{jpg,jpeg,png}', GLOB_BRACE));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Lunae - <?php echo $_SESSION['usuario']; ?></title>
    <link rel="icon" type="image/png" href="fiveicon.png?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #ffffff; color: #333333; padding: 10px; text-align: center; margin: 0; }
        .encabezado-panel { position: relative; padding: 20px; }
        h1 { color: #D4AF37; font-family: 'Cinzel', serif; font-size: 2em; margin-bottom: 5px;}
        h2 { color: #B76E79; font-weight: 400; font-size: 1.2em;}
        .btn-salir { background-color: #f1f1f1; color: #333; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9em; position: absolute; top: 10px; right: 10px; font-weight: bold;}
        .tarjeta { background: #ffffff; padding: 20px; border-radius: 10px; border: 1px solid #eeeeee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto 20px; }
        .btn-lujo { background-color: #D4AF37; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%; font-family: 'Montserrat';}
        .btn-lujo:hover { background-color: #B76E79;}
        .btn-secundario { background-color: #666; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; font-family: 'Montserrat';}
        .mensaje { background-color: #fdfaf0; color: #b8901e; border: 1px solid #D4AF37; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .galeria { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; justify-content: center; margin-top: 20px; }
        .contenedor-foto { position: relative; display: inline-block; width: 100%; }
        .etiqueta-numero { position: absolute; top: 5px; left: 5px; background-color: #D4AF37; color: #ffffff; font-weight: bold; padding: 3px 6px; border-radius: 4px; font-size: 0.85em; pointer-events: none; z-index: 2;}
        .foto-admin { width: 100%; height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 4px solid transparent; transition: all 0.3s ease; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}
        .foto-admin.vendida { border-color: #B76E79; opacity: 0.4; transform: scale(0.9); }
        .foto-admin.oculta { border-color: #000; opacity: 0.2; transform: scale(0.7); filter: grayscale(100%);}
        #indicador-carga { display: none; color: #B76E79; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="encabezado-panel">
        <a href="admin.php?salir=true" class="btn-salir">Cerrar Sesión</a>
        <h1>LUNAE - PANEL</h1>
        <h2>Bienvenida, <?php echo $_SESSION['usuario']; ?> 👑</h2>
    </div>

    <?php if(isset($mensaje_exito)) echo "<div class='mensaje'>$mensaje_exito</div>"; ?>
    <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'limpio') echo "<div class='mensaje'>Catálogo renovado: Todas las joyas están disponibles nuevamente.</div>"; ?>

    <div class="tarjeta">
        <h3>1. Subir Nuevo Catálogo</h3>
        <p>Selecciona todas las fotos nuevas. <br><b>⚠️ Borrará el catálogo anterior.</b></p>
        <form action="admin.php" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('indicador-carga').style.display='block';">
            <input type="file" name="fotos[]" multiple accept="image/*" required style="margin-bottom:15px; width:100%;">
            <br>
            <button type="submit" name="subir_catalogo" class="btn-lujo">Subir Fotos</button>
            <p id="indicador-carga">⏳ Procesando imágenes, no cierres la ventana...</p>
        </form>
    </div>

    <div class="tarjeta" style="max-width: 900px;">
        <h3>2. Control de Catálogo</h3>
        <p>👉 <b>1 Toque:</b> Marcar/Desmarcar Vendida (Rojo)<br>✌️ <b>2 Toques rápidos:</b> Ocultar joya (Gris chiquito)</p>
        <button class="btn-secundario" onclick="if(confirm('¿Segura que quieres poner TODAS las joyas como disponibles?')) window.location.href='admin.php?limpiar_ventas=true'">♻️ Marcar TODO como disponible</button>
        
        <div class="galeria">
            <?php
            for ($i = 1; $i <= $total_fotos_actuales; $i++) {
                $ruta = "";
                if (file_exists($carpeta_img . $i . '.jpg')) $ruta = $carpeta_img . $i . '.jpg';
                elseif (file_exists($carpeta_img . $i . '.jpeg')) $ruta = $carpeta_img . $i . '.jpeg';
                elseif (file_exists($carpeta_img . $i . '.png')) $ruta = $carpeta_img . $i . '.png';

                if ($ruta != "") {
                    $clase_vendida = in_array($i, $joyas_vendidas) ? "vendida" : "";
                    $clase_oculta = in_array($i, $joyas_ocultas) ? "oculta" : "";
                    echo "<div class='contenedor-foto'>";
                    echo "<span class='etiqueta-numero'>#$i</span>";
                    echo "<img src='$ruta' class='foto-admin $clase_vendida $clase_oculta' ondblclick='accionJoya(this, $i, \"ocultar\")' onclick='toqueSimple(this, $i)'>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
    <script>
        // Lógica para diferenciar 1 toque de 2 toques (doble clic)
        let temporizador;
        function toqueSimple(elementoImg, numeroJoya) {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => {
                accionJoya(elementoImg, numeroJoya, 'vender');
            }, 250); // Si no hay otro toque en 250ms, es un toque simple
        }

        function accionJoya(elementoImg, numeroJoya, tipo) {
            clearTimeout(temporizador); // Cancelar toque simple si fue doble
            fetch(`admin.php?accion_joya=${numeroJoya}&tipo=${tipo}`)
            .then(() => {
                if(tipo === 'vender') elementoImg.classList.toggle('vendida');
                else elementoImg.classList.toggle('oculta');
            });
        }
    </script>
</body>
</html>