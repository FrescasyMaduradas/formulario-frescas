const Resend = require('resend'); // Asegúrate de incluir la biblioteca Resend en tu proyecto

// Configura los límites del tamaño de carga de archivos
const uploadMaxSize = '20M';

// Iniciar la instancia de Resend
const resend = new Resend('re_ZKbLvu5d_BU4FubgbBDvzgGUqMx89JzbR');

// Verifica si el formulario se envía con el método post
if (window.location.href.indexOf('submit') > -1) {

    // Obtener y limpiar los datos del formulario
    const nombre = document.getElementById("Name").value;
    const email = document.getElementById("Email").value;
    const vacante = document.getElementById("Vacante").value;
    const experiencia = document.getElementById("Experiencia").value;
    const telefono = document.getElementById("Teléfono").value;
    const seleccion = document.getElementById("Selección").value;

    // Agregar archivo adjunto y verifica si hay algún archivo adjunto
    const fileInput = document.getElementById("file");
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];

        // Directorio donde se almacenarán los archivos subidos
        const directorioDestino = "archivos/";

        // Obtener información sobre el archivo
        const nombreArchivo = file.name;
        const rutaArchivo = directorioDestino + nombreArchivo;

        // Enviar archivo al servidor (debes implementar la lógica del servidor)
        // ...

        // Construir el cuerpo del correo
        const cuerpo = `<p> Nombre: ${nombre} </p>
                        <p> Correo: ${email} </p>
                        <p> Vacante: ${vacante} </p>
                        <p> Experiencia: ${experiencia} </p>
                        <p> Teléfono: ${telefono} </p>
                        <p> Selección: ${seleccion} </p>`;

        // Intentar enviar el correo electrónico con Resend
        try {
            resend.emails.send({
                from: 'Formulario <onboarding@resend.dev>',
                to: ['publicidadfrescas@gmail.com'],
                subject: 'Hoja de vida',
                html: cuerpo,
                attachments: [
                    {
                        filename: nombreArchivo,
                        content: rutaArchivo
                    }
                ],
                headers: {
                    'X-Entity-Ref-ID': 're_ZKbLvu5d_BU4FubgbBDvzgGUqMx89JzbR'
                },
                tags: [
                    {
                        name: 'category',
                        value: 'confirm_email'
                    }
                ]
            });

            // Redirigir a una página de agradecimiento
            window.location.href = 'index.html';
        } catch (error) {
            // Manejar el error si falla el envío del correo electrónico con Resend
            console.error("Error al enviar el correo electrónico con Resend: ", error.message);
        }
    } else {
        console.error("No se ha seleccionado ningún archivo o se produjo un error en la carga.");
    }
} else {
    // Código si no hay datos en el formulario
    console.error("Error: No se recibieron datos del formulario.");
}
