<?php
session_start();

// --- 🔒 SEGURIDAD ---
$usuario_secreto = "ElydaRI";
$contrasena_secreta = "eraigam07"; 

if (isset($_GET['salir'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

$error_login = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user_input = $_POST['username'] ?? '';
    $pass_input = $_POST['password'] ?? '';
    
    if ($user_input === $usuario_secreto && $pass_input === $contrasena_secreta) {
        $_SESSION['logueado'] = true;
        $_SESSION['usuario'] = $user_input;
    } else {
        $error_login = "Usuario o contraseña incorrectos.";
    }
}

if (isset($_GET['recuperar'])) {
    $mensaje_recuperar = "Por seguridad, contacta a tu desarrollador para restablecer la contraseña.";
}

// Bloqueo: Pantalla de Login
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro - Lunae</title>
    <link rel="icon" type="image/png" href="fiveicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px;}
        .tarjeta-login { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; max-width: 400px; width: 100%; border: 1px solid #eeeeee;}
        h1 { color: #D4AF37; margin-bottom: 30px; font-family: 'Cinzel', serif; letter-spacing: 2px;}
        input { width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 1.1em; font-family: 'Montserrat', sans-serif;}
        button { width: 100%; padding: 15px; background-color: #D4AF37; color: white; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: 0.3s;}
        button:hover { background-color: #B76E79;}
        .error { color: #d9534f; margin-bottom: 15px; font-weight: bold;}
        .recuperar { color: #666; font-size: 0.9em; margin-top: 15px; text-decoration: none; display: inline-block;}
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
        <a href="admin.php?recuperar=true" class="recuperar">¿Olvidaste tu contraseña?</a>
        <?php if(isset($mensaje_recuperar)) echo "<p style='color: #666; margin-top: 10px;'>$mensaje_recuperar</p>"; ?>
    </div>
</body>
</html>
<?php
exit; 
endif;

// --- PANEL PROTEGIDO ---
$carpeta_img = 'assets/img/';
$archivo_datos = 'assets/js/datos.json';

if (!file_exists($archivo_datos)) {
    file_put_contents($archivo_datos, '[]');
}

// Subir Catálogo
if (isset($_POST['subir_catalogo'])) {
    $fotos_viejas = glob($carpeta_img . '*');
    foreach($fotos_viejas as $f) { if(is_file($f)) unlink($f); }
    file_put_contents($archivo_datos, '[]');
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

// Marcar/Desmarcar
if (isset($_GET['marcar_vendida'])) {
    $id_joya = (int)$_GET['marcar_vendida'];
    $vendidas = json_decode(file_get_contents($archivo_datos), true);
    if (($key = array_search($id_joya, $vendidas)) !== false) {
        unset($vendidas[$key]);
    } else {
        $vendidas[] = $id_joya;
    }
    file_put_contents($archivo_datos, json_encode(array_values($vendidas)));
    exit; 
}

$joyas_vendidas = json_decode(file_get_contents($archivo_datos), true);
$total_fotos_actuales = count(glob($carpeta_img . '*.{jpg,jpeg,png}', GLOB_BRACE));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Lunae - <?php echo $_SESSION['usuario']; ?></title>
    <link rel="icon" type="image/png" href="fiveicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #ffffff; color: #333333; padding: 10px; text-align: center; margin: 0; }
        @media (min-width: 768px) { body { padding: 20px; } }
        
        .encabezado-panel { position: relative; padding: 20px; }
        h1 { color: #D4AF37; letter-spacing: 2px; text-transform: uppercase; font-family: 'Cinzel', serif; font-size: 2em; margin-bottom: 5px;}
        h2 { color: #B76E79; font-weight: 400; font-size: 1.2em;}
        p { color: #666666; font-size: 1em; line-height: 1.5; }
        
        .btn-salir { background-color: #f1f1f1; color: #333; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.9em; position: absolute; top: 10px; right: 10px; font-weight: bold;}
        
        .tarjeta { background: #ffffff; padding: 20px; border-radius: 10px; border: 1px solid #eeeeee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto 20px; }
        @media (min-width: 768px) { .tarjeta { padding: 30px; margin-bottom: 30px; } }
        
        .btn-lujo { background-color: #D4AF37; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%; max-width: 300px; margin-top: 10px; font-family: 'Montserrat', sans-serif;}
        .btn-lujo:hover { background-color: #B76E79;}
        .mensaje { background-color: #fdfaf0; color: #b8901e; border: 1px solid #D4AF37; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        
        .galeria { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; justify-content: center; margin-top: 20px; }
        @media (min-width: 768px) { .galeria { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; } }
        
        /* Contenedor y etiqueta de número */
        .contenedor-foto { position: relative; display: inline-block; width: 100%; }
        .etiqueta-numero { position: absolute; top: 5px; left: 5px; background-color: #D4AF37; color: #ffffff; font-weight: bold; padding: 3px 6px; border-radius: 4px; font-size: 0.85em; pointer-events: none; z-index: 2; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        
        .foto-admin { width: 100%; height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 4px solid transparent; transition: all 0.3s ease; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: block;}
        @media (min-width: 768px) { .foto-admin { height: 120px; } }
        .foto-admin.vendida { border-color: #B76E79; opacity: 0.4; transform: scale(0.9); }
        
        input[type="file"] { margin-bottom: 20px; font-family: 'Montserrat', sans-serif; font-size: 0.9em; max-width: 100%;}
    </style>
</head>
<body>
    <div class="encabezado-panel">
        <a href="admin.php?salir=true" class="btn-salir">Cerrar Sesión</a>
        <h1>LUNAE - PANEL</h1>
        <h2>Bienvenida, <?php echo $_SESSION['usuario']; ?> 👑</h2>
    </div>

    <?php if(isset($mensaje_exito)) echo "<div class='mensaje'>$mensaje_exito</div>"; ?>

    <div class="tarjeta">
        <h3>1. Subir Nuevo Catálogo</h3>
        <p>Selecciona todas las fotos nuevas de tu celular. <br><b>⚠️ Esto borrará el catálogo anterior.</b></p>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="fotos[]" multiple accept="image/*" required>
            <br>
            <button type="submit" name="subir_catalogo" class="btn-lujo">Subir y Reiniciar</button>
        </form>
    </div>

    <div class="tarjeta" style="max-width: 900px;">
        <h3>2. Control de Ventas</h3>
        <p>Toca la foto de la joya que ya se vendió. <br><b>💡 Tip: Si te equivocas, vuelve a tocar la foto para desmarcarla.</b></p>
        
        <div class="galeria">
            <?php
            for ($i = 1; $i <= $total_fotos_actuales; $i++) {
                $ruta = "";
                if (file_exists($carpeta_img . $i . '.jpg')) $ruta = $carpeta_img . $i . '.jpg';
                elseif (file_exists($carpeta_img . $i . '.jpeg')) $ruta = $carpeta_img . $i . '.jpeg';
                elseif (file_exists($carpeta_img . $i . '.png')) $ruta = $carpeta_img . $i . '.png';

                if ($ruta != "") {
                    $clase_vendida = in_array($i, $joyas_vendidas) ? "vendida" : "";
                    echo "<div class='contenedor-foto'>";
                    echo "<span class='etiqueta-numero'>#$i</span>";
                    echo "<img src='$ruta' class='foto-admin $clase_vendida' onclick='marcarVendida(this, $i)'>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
    <script>
        function marcarVendida(elementoImg, numeroJoya) {
            fetch('admin.php?marcar_vendida=' + numeroJoya)
            .then(() => { elementoImg.classList.toggle('vendida'); });
        }
    </script>
</body>
</html>