
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

            const parrafos = document.querySelectorAll("p");

            parrafos.forEach((parrafo) => {
                if (parrafo.textContent.trim().startsWith("Estado:")) {
                    parrafo.innerHTML =
                        "<strong>Estado:</strong> cancelada";
                }
            });

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
