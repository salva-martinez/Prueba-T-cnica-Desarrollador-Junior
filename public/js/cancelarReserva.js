
document.addEventListener("DOMContentLoaded", () => {
    const boton = document.querySelector("#btnCancelarReserva");
    const mensaje = document.querySelector("#mensajeCancelacion");

    // Si la reserva ya está cancelada, el botón no existe.
    if (!boton || !mensaje) {
        return;
    }

    boton.addEventListener("click", async () => {
        const id = boton.dataset.id;

        // Pedir confirmación antes de cancelar.
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

            const resultado = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(
                    resultado.error ||
                    resultado.mensaje ||
                    "No se pudo cancelar la reserva."
                );
            }

            mensaje.textContent =
                resultado.mensaje ||
                "Reserva cancelada correctamente.";

            // Actualizar el estado visible.
            const parrafos = document.querySelectorAll("p");

            parrafos.forEach((parrafo) => {
                if (parrafo.textContent.trim().startsWith("Estado:")) {
                    parrafo.innerHTML =
                        "<strong>Estado:</strong> cancelada";
                }
            });

            // Quitar el botón para evitar una segunda cancelación.
            boton.remove();

            // Recargar el detalle para mostrar el evento registrado.
            setTimeout(() => {
                window.location.reload();
            }, 1200);

        } catch (error) {
            mensaje.textContent = error.message;
            boton.disabled = false;
        }
    });
});
