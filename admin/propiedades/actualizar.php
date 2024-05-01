<?php

require '../../includes/funciones.php';

$auth = estaAutenticado();

if (!$auth) {
    header("Location: /");
}

//Autenticar el usuario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =  mysqli_real_escape_string($db, $_POST["email"]);
    $password =  mysqli_real_escape_string($db, $_POST["password"]);

    if (!$email) {
        $errores[] = "El email es obligatorio o no es válido";
    }

    if (!$password) {
        $errores[] = "El password es obligatorio";
    }

    if (empty($errores)) {
        //Revisar si el usuario existe
        $query = "SELECT * FROM usuarios WHERE email = '${email}'";
        $resultado = mysqli_query($db, $query);

        if ($resultado->num_rows) {

            //Revisar si el password es correcto
            $usuario = mysqli_fetch_assoc($resultado);

            $auth = password_verify($password, $usuario["password"]);

            if ($auth) {
                //El usuario esta autenticado
                session_start();

                //Llenar el arreglo de la sesión
                $_SESSION["usuario"] = $usuario["email"];
                $_SESSION["login"] = true;
            } else {
                $errores[] = "El password es incorrecto";
            }
        } else {
            $errores[] = "El usuario no existe";
        }
    }
}

//Validar la URL por ID valido
$id = $_GET["id"];
$id = filter_var($id, FILTER_VALIDATE_INT);

if (!$id) {
    //Redireccionar al usuario
    header("Location: /admin");
}

// Base de datos
require '../../includes/config/database.php';
$db = conectarDB();

//Obtener los datos de la propiedadpre
$consulta = "SELECT * FROM propiedades WHERE id = ${id}";
$resultado = $db->query($consulta);
$propiedad = $resultado->fetch_assoc();

/*     echo "<pre>";
    var_dump($propiedad);
    echo "</pre>"; */



//Consultar para obtener los vendedores
$consulta = "SELECT * FROM vendedores";
$resultado = $db->query($consulta);

/*     echo "<pre>";
    var_dump($resultado);
    echo "</pre>"; */


// Arreglo con mensajes de errores
$errores = [];

$titulo = $propiedad["titulo"];
$precio = $propiedad["precio"];
$descripcion = $propiedad["descripcion"];
$habitaciones = $propiedad["habitaciones"];
$wc = $propiedad["wc"];
$estacionamiento = $propiedad["estacionamiento"];
$vendedores_id = $propiedad["vendedores_id"];
$imagenPropiedad = $propiedad["imagen"];

// Ejecutar el código después de que el usuario envia el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*         echo "<pre>";
        var_dump($_POST);
        echo "</pre>";

        echo "<pre>";
        var_dump($_FILES);
        echo "</pre>"; */

    $titulo = mysqli_real_escape_string($db, $_POST["titulo"]);
    $precio = mysqli_real_escape_string($db, $_POST["precio"]);
    $descripcion = mysqli_real_escape_string($db, $_POST["descripcion"]);
    $habitaciones = mysqli_real_escape_string($db, $_POST["habitaciones"]);
    $wc = mysqli_real_escape_string($db, $_POST["wc"]);
    $estacionamiento = mysqli_real_escape_string($db, $_POST["estacionamiento"]);
    $vendedores_id = mysqli_real_escape_string($db, $_POST["vendedor"]);
    $creado = date("Y/m/d");

    //Asignar files hacia una variable
    $imagen = $_FILES["imagen"];

    if (!$titulo) {
        $errores[] = "Debes añadir un titulo";
    }

    if (!$precio) {
        $errores[] = 'El Precio es Obligatorio';
    }

    if (strlen($descripcion) < 50) {
        $errores[] = 'La descripción es obligatoria y debe tener al menos 50 caracteres';
    }

    if (!$habitaciones) {
        $errores[] = 'El Número de habitaciones es obligatorio';
    }

    if (!$wc) {
        $errores[] = 'El Número de Baños es obligatorio';
    }

    if (!$estacionamiento) {
        $errores[] = 'El Número de lugares de Estacionamiento es obligatorio';
    }

    if (!$vendedores_id) {
        $errores[] = 'Elige un vendedor';
    }

    //Validar por tamaño (1mb maximo)
    $medida = 1000 * 1000;

    if ($imagen["size"] > $medida) {
        $errores[] = "La imagen es muy pesada";
    }


    if (empty($errores)) {
        //Crear carpeta
        $carpetaImagenes = "../../imagenes/";


        if (!is_dir($carpetaImagenes)) {
            mkdir($carpetaImagenes);
        }

        $nombreImagen = "";

        //Subida de archivos
        if ($imagen["name"]) {
            //Eliminar la imagen previa

            unlink($carpetaImagenes . $propiedad["imagen"]);

            //Generar un nombre único
            $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";

            //Subir la imagen
            move_uploaded_file($imagen["tmp_name"], $carpetaImagenes . $nombreImagen);
        } else {
            $nombreImagen = $propiedad["imagen"];
        }


        //Insertar en la base de datos
        $query = "UPDATE propiedades SET titulo = '${titulo}', precio = '${precio}', imagen = '${nombreImagen}', descripcion = '${descripcion}', habitaciones = ${habitaciones}, wc = ${wc}, estacionamiento = ${estacionamiento}, vendedores_id = ${vendedores_id} WHERE id = ${id}";

        //echo $query;

        $resultado = $db->query($query);

        if ($resultado) {
            //Redireccionar al usuario
            header("Location: /admin?resultad");
        }
    }
}

incluirTemplate('header');
?>

<main class="contenedor seccion">
    <h1>Actualizar Propiedad</h1>



    <a href="/admin" class="boton boton-verde">Volver</a>

    <?php foreach ($errores as $error) : ?>
        <div class="alerta error">
            <?php echo $error; ?>
        </div>
    <?php endforeach; ?>

    <form class="formulario" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Información General</legend>

            <label for="titulo">Titulo:</label>
            <input type="text" id="titulo" name="titulo" placeholder="Titulo Propiedad" value="<?php echo $titulo; ?>">

            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" placeholder="Precio Propiedad" value="<?php echo $precio; ?>">

            <label for="imagen">Imagen:</label>
            <input type="file" id="imagen" accept="image/jpeg, image/png" name="imagen">

            <img src="/imagenes/<?php echo $imagenPropiedad; ?>" class="imagen-small">

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion"><?php echo $descripcion; ?></textarea>

        </fieldset>

        <fieldset>
            <legend>Información Propiedad</legend>

            <label for="habitaciones">Habitaciones:</label>
            <input type="number" id="habitaciones" name="habitaciones" placeholder="Ej: 3" min="1" max="9" value="<?php echo $habitaciones ?>">

            <label for="wc">Baños:</label>
            <input type="number" id="wc" name="wc" placeholder="Ej: 3" min="1" max="9" value="<?php echo $wc; ?>">

            <label for="estacionamiento">Estacionamiento:</label>
            <input type="number" id="estacionamiento" name="estacionamiento" placeholder="Ej: 3" min="1" max="9" value="<?php echo $estacionamiento; ?>">

        </fieldset>

        <fieldset>
            <legend>Vendedor</legend>

            <select name="vendedor">
                <option value="">-- Seleccione --</option>
                <?php while ($vendedor = $resultado->fetch_assoc()) : ?>
                    <option <?php echo $vendedores_id === $vendedor["id"] ? "selected" : ""; ?> value="<?php echo $vendedor["id"] ?>"><?php echo $vendedor["nombre"] . " " . $vendedor["apellido"]; ?></option>
                <?php endwhile; ?>
            </select>
        </fieldset>

        <input type="submit" value="Actualizar Propiedad" class="boton boton-verde">
    </form>

</main>

<?php
incluirTemplate('footer');
?>