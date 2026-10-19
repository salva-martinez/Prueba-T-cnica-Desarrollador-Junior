
document.addEventListener("DOMContentLoaded", () => {
    const boton = document.querySelector("#btnCancelarReserva");
    const mensaje = document.querySelector("#mensajeCancelacion");

    if (!boton || !mensaje) {
        return;
    }

    boton.addEventListener("click", async () => {
        const id = boton.dataset.id;

        const confirmado = confirm(
            `¿Seguro que quieres cancelar la reserva ${id}?`
        );

        if (!confirmado) {
            return;
        }

        boton.disabled = true;
        mensaje.textContent = "Cancelando reserva...";

        try {
            const respuesta = await fetch(
                "/api-proxy.php?accion=cancelar",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        accion: "cancelar",
                        id: Number(id)
                    })
                }
            );

            let resultado;

            try {
                resultado = await respuesta.json();
            } catch {
                throw new Error(
                    "El servidor no ha devuelto una respuesta JSON válida."
                );
            }

            if (!respuesta.ok || resultado.status !== "success") {
                throw new Error(
                    resultado.error ||
                    resultado.mensaje ||
                    "No se pudo cancelar la reserva."
                );
            }

            mensaje.textContent =
                resultado.mensaje ||
                "Reserva cancelada correctamente.";

            const estadoReserva = document.querySelector("#estadoReserva");

            if (estadoReserva) {
                estadoReserva.textContent = "Cancelada";
                estadoReserva.className =
                    "estado-detalle estado-detalle-cancelada";
            }

            boton.remove();

            setTimeout(() => {
                window.location.reload();
            }, 1200);

        } catch (error) {
            mensaje.textContent = error.message;
            boton.disabled = false;
        }
    });
});
