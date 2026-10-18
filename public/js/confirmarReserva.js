document.addEventListener("DOMContentLoaded", () => {
    const boton = document.querySelector("#btnConfirmarReserva");
    const mensaje = document.querySelector("#mensajeConfirmacion");

    if (!boton || !mensaje) {
        return;
    }

    boton.addEventListener("click", async () => {
        const id = boton.dataset.id;

        const confirmado = confirm(
            `¿Quieres confirmar la reserva ${id}?`
        );

        if (!confirmado) {
            return;
        }

        boton.disabled = true;
        mensaje.textContent = "Confirmando reserva...";

        try {
            const respuesta = await fetch(
                "/api-proxy.php?accion=confirmar",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        accion: "confirmar",
                        id: Number(id)
                    })
                }
            );

            const resultado = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(
                    resultado.error ||
                    resultado.mensaje ||
                    "No se pudo confirmar la reserva."
                );
            }

            mensaje.textContent =
                resultado.mensaje ||
                "Reserva confirmada correctamente.";

            window.location.reload();

        } catch (error) {
            mensaje.textContent = error.message;
            boton.disabled = false;
        }
    });
});