<?php
// Carga la biblioteca Resend y Configura limites del tamaño de carga de archivos
require __DIR__ . '/vendor/autoload.php';

//phpinfo(INFO_VARIABLES);  // Puedes utilizar constantes como INFO_VARIABLES, INFO_CONFIGURATION, etc.);

// límite de carga de archivos en bytes (20 MB en bytes)
ini_set('upload_max_filesize', '20971520'); 

// límite de tamaño total de la solicitud en bytes (20 MB en bytes)
ini_set('post_max_size', '20971520');

try {
    //Iniciar la instancia de Resend
    $resend = Resend::client('re_bTN3yHkt_JTCsKFZohVTSVfmWXTLkCY9K');

} catch (Exception $e) {
    // Manejar el error si no se puede crear la instancia de Resend
    echo "Error al inicializar Resend: " . $e->getMessage();
    exit();
}

$max_size = 50 * 1024 * 1024; // Acepta 50 megas

// Verifica el tipo de archivo
$allowed_types = [
    'application/pdf',
    'application/msword'
];

// Verifica que los archivos sean los requeridos y permite el máximo de megas de los archivos
if ($_FILES['archivo']['size'] > $max_size || !in_array(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION), $allowed_types)) {
    echo 'Archivo no permitido o excede el tamaño máximo permitido.';
}

// Verifica si el formulario se envía con el método post
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Agregar archivo adjunto y verifica si hay algún archivo adjunto
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {

        // Directorio donde se almacenarán los archivos subidos
        $directorio_destino = "archivos/";

        // Obtener información sobre el archivo
        $nombre_archivo = basename($_FILES["file"]["name"]);
        $ruta_archivo = $directorio_destino . $nombre_archivo;

        // Verificar el tamaño del archivo
        $file_size = $_FILES['file']['size'];
        if ($file_size > $max_size) {
            die('El archivo es demasiado grande.');
        }

        // Mover el archivo al servidor
        $new_file_name = uniqid() . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . "/" . $new_file_name);
        
        // Mover el archivo al directorio de destino
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $ruta_archivo)) {

        //Verifica si el archivo existe
        if (!file_exists($ruta_archivo)) {
            echo "El archivo no existe: $ruta_archivo ";
            exit();
        }
            switch ($_FILES["file"]["error"]) {
                case UPLOAD_ERR_OK:
                    // Todo bien
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    echo "El archivo es demasiado grande.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "El archivo se subió parcialmente.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "No se seleccionó ningún archivo.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "Falta el directorio temporal.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Error al escribir el archivo en el servidor.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "Error de extensión del archivo.";
                    break;
                    exit();
            }
        } else {
            echo "Error al subir el archivo.";
            exit();
        }

    } else {
        echo "No se ha seleccionado ningún archivo o se produjo un error en la carga.";
        exit();
    }

    // Obtener y limpiar los datos del formulario
    $nombre = htmlspecialchars($_POST["Name"]);
    $email = filter_var($_POST["Email"], FILTER_SANITIZE_EMAIL);
    $vacante = htmlspecialchars($_POST["Vacante"]);
    $experiencia = htmlspecialchars($_POST["Experiencia"]);
    $telefono = htmlspecialchars($_POST["Teléfono"]);
    $seleccion = htmlspecialchars($_POST["Selección"]);

    // Construir el cuerpo del correo
    $cuerpo = "<p> Nombre:  $nombre\r\n </p>";
    $cuerpo .= "<p> Correo: $email\r\n </p>";
    $cuerpo .= "<p> Vacante: $vacante\r\n </p>";
    $cuerpo .= "<p> Experiencia: $experiencia\r\n </p>";
    $cuerpo .= "<p> Teléfono: $telefono\r\n </p>";
    $cuerpo .= "<p> Ubicación: $seleccion\r\n </p>";

    // Intentar enviar el correo electrónico con Resend
    try {
        $resend->emails->send([
            'from' => 'Formulario <onboarding@resend.dev>',
            'to' => ['publicidadfrescas@gmail.com'],
            'subject' => 'Hoja de vida',
            'html' => "$cuerpo",

            'attachments' => [
                [
                    'filename' => "$nombre_archivo",
                    'content' =>  $new_file_name
                ]
            ],

            'headers' => [
                'X-Entity-Ref-ID' => 're_bTN3yHkt_JTCsKFZohVTSVfmWXTLkCY9K',
            ],
            'tags' => [
                [
                    'name' => 'category',
                    'value' => 'form',
                ],
            ],
        ]);

        // Redirigir a una página de agradecimiento
        header("Location: index.html");
        exit();

    } catch (Exception $e) {
        
        // Manejar el error si falla el envío del correo electrónico con Resend
        echo "Error al enviar el correo electrónico con Resend: " . $e->getMessage();
        exit();
    }
} else {

    // Código si no hay datos en $_POST
    echo "Error: No se recibieron datos del formulario.";
}   
?>
