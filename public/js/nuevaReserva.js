
const formulario = document.querySelector("#formNuevaReserva");
const mensajeFormulario = document.querySelector("#mensajeFormulario");
let temporizadorMensajes;

const campos = [
    "nombre_huesped",
    "email_huesped",
    "nombre_alojamiento",
    "fecha_entrada",
    "fecha_salida",
    "importe",
    "notas"
];

function limpiarErrores() {
    campos.forEach((campo) => {
        const elementoError = document.querySelector(`#error_${campo}`);

        if (elementoError) {
            elementoError.textContent = "";
        }
    });

    mensajeFormulario.textContent = "";
}

function mostrarError(campo, mensaje) {
    const elementoError = document.querySelector(`#error_${campo}`);

    if (elementoError) {
        elementoError.textContent = mensaje;
    } else {
        mensajeFormulario.textContent += `${mensaje} `;
    }
}

function cancelarTemporizador() {
    clearTimeout(temporizadorMensajes);
}

function programarLimpiezaMensajes(segundos = 5) {
    cancelarTemporizador();

    temporizadorMensajes = setTimeout(() => {
        limpiarErrores();
    }, segundos * 1000);
}

function validarFormulario(datos) {
    const errores = {};

    const nombre = datos.nombre_huesped.trim();
    const email = datos.email_huesped.trim();
    const alojamiento = datos.nombre_alojamiento.trim();
    const entrada = datos.fecha_entrada;
    const salida = datos.fecha_salida;
    const importeTexto = datos.importe.trim();
    const importe = Number(importeTexto);

    if (!nombre) {
        errores.nombre_huesped = "El nombre del huésped es obligatorio.";
    } else if (nombre.length > 100) {
        errores.nombre_huesped =
            "El nombre del huésped no puede superar los 100 caracteres.";
    }

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errores.email_huesped =
            "Introduce un correo electrónico válido.";
    }

    if (!alojamiento) {
        errores.nombre_alojamiento =
            "El nombre del alojamiento es obligatorio.";
    } else if (alojamiento.length > 100) {
        errores.nombre_alojamiento =
            "El nombre del alojamiento no puede superar los 100 caracteres.";
    }

    if (!entrada) {
        errores.fecha_entrada = "La fecha de entrada es obligatoria.";
    }

    if (!salida) {
        errores.fecha_salida = "La fecha de salida es obligatoria.";
    }

    if (entrada && salida && salida <= entrada) {
        errores.fecha_salida =
            "La salida debe ser posterior a la entrada.";
    }

    if (
        importeTexto === "" ||
        !Number.isFinite(importe) ||
        importe < 0
    ) {
        errores.importe =
            "El importe es obligatorio y debe ser mayor o igual que cero.";
    }

    if (datos.notas.length > 500) {
        errores.notas =
            "El contenido de la nota no puede superar los 500 caracteres.";
    }

    return errores;
}

formulario.addEventListener("submit", async (event) => {
    event.preventDefault();
    cancelarTemporizador();

    limpiarErrores();

    const datos = {
        nombre_huesped: document.querySelector("#nombre_huesped").value.trim(),
        email_huesped: document.querySelector("#email_huesped").value.trim(),
        nombre_alojamiento: document.querySelector("#nombre_alojamiento").value.trim(),
        fecha_entrada: document.querySelector("#fecha_entrada").value,
        fecha_salida: document.querySelector("#fecha_salida").value,
        importe: document.querySelector("#importe").value.trim(),
        notas: document.querySelector("#notas").value.trim()
    };

    const errores = validarFormulario(datos);

    if (Object.keys(errores).length > 0) {
        Object.entries(errores).forEach(([campo, mensaje]) => {
            mostrarError(campo, mensaje);
        });

        mensajeFormulario.textContent =
            "Revisa los campos indicados antes de continuar.";

        programarLimpiezaMensajes(5);
        return;
    }

    const boton = formulario.querySelector('button[type="submit"]');
    boton.disabled = true;
    mensajeFormulario.textContent = "Guardando reserva...";
    let reservaCreada = false;

    try {

        const respuesta = await fetch("/api-proxy.php?accion=crear", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(datos)
        });

        let resultado;

        try {
            resultado = await respuesta.json();
        } catch {
            throw new Error("El servidor no ha devuelto una respuesta JSON válida.");
        }

        if (!respuesta.ok || resultado.status !== "success") {
            const erroresServidor = Array.isArray(resultado.errors)
                ? resultado.errors
                : [];

            if (erroresServidor.length > 0) {
                erroresServidor.forEach((mensaje) => {
                    const texto = mensaje.toLowerCase();

                    if (texto.includes("nombre del huésped")) {
                        mostrarError("nombre_huesped", mensaje);
                    } else if (texto.includes("correo electrónico")) {
                        mostrarError("email_huesped", mensaje);
                    } else if (texto.includes("alojamiento")) {
                        mostrarError("nombre_alojamiento", mensaje);
                    } else if (texto.includes("fecha")) {
                        mensajeFormulario.textContent += `${mensaje} `;
                    } else if (texto.includes("importe")) {
                        mostrarError("importe", mensaje);
                    } else if (texto.includes("nota")) {
                        mostrarError("notas", mensaje);
                    } else {
                        mensajeFormulario.textContent += `${mensaje} `;
                    }
                });
            } else {
                mensajeFormulario.textContent =
                    resultado.message || "No se pudo crear la reserva.";
            }

            programarLimpiezaMensajes(5);
            return;
        }

        reservaCreada = true;
        mensajeFormulario.textContent =
            `${resultado.message} ID: ${resultado.data.id}`;

        formulario.reset();

        cancelarTemporizador();

        setTimeout(() => {
            window.location.href = "/";
        }, 2000);

    } catch (error) {
        mensajeFormulario.textContent =
            "No se pudo completar la petición. Comprueba la conexión e inténtalo de nuevo.";
        console.error(error);
    } finally {
        if (!reservaCreada) {
            boton.disabled = false;
        }
    }
});
