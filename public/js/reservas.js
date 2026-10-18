
const formulario = document.querySelector("#filtros-reservas");
const cuerpo = document.querySelector("#cuerpo-reservas");
const estadoListado = document.querySelector("#estado-listado");

if (formulario && cuerpo && estadoListado) {
    formulario.addEventListener("submit", (event) => {
        event.preventDefault();
        cargarReservas();
    });

    cargarReservas();
}

async function cargarReservas() {
    estadoListado.textContent = "Cargando reservas...";

    try {
        const respuesta = await fetch(
            "/api-proxy.php?accion=listar"
        );

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(
                resultado.error || `Error HTTP ${respuesta.status}`
            );
        }

        if (!Array.isArray(resultado.data)) {
            throw new Error("La respuesta de la API no contiene una lista válida.");
        }

        const reservasFiltradas = filtrarReservas(resultado.data);

        pintarReservas(reservasFiltradas);

        estadoListado.textContent =
            `${reservasFiltradas.length} reservas encontradas.`;

    } catch (error) {
        estadoListado.textContent =
            `No se pudieron cargar las reservas: ${error.message}`;
    }
}

const botonLimpiar = document.querySelector("#limpiarFiltros");

botonLimpiar.addEventListener("click", () => {
    document.querySelector("#nombre").value = "";

    document.querySelector("#estado").value = "";

    document.querySelector("#fecha_inicio").value = "";
    document.querySelector("#fecha_fin").value = "";

    cargarReservas();
});

function filtrarReservas(reservas) {
    const nombre = document.querySelector("#nombre").value
        .trim()
        .toLocaleLowerCase("es");

    const estado = document.querySelector("#estado").value;
    const fechaInicio = document.querySelector("#fecha_inicio").value;
    const fechaFin = document.querySelector("#fecha_fin").value;

    return reservas.filter((reserva) => {
        const coincideNombre = (reserva.nombre_huesped ?? "")
            .toLocaleLowerCase("es")
            .includes(nombre);

        const coincideEstado =
            estado === "" || reserva.estado === estado;

        const coincideFechaInicio =
            fechaInicio === "" || reserva.fecha_entrada >= fechaInicio;

        const coincideFechaFin =
            fechaFin === "" || reserva.fecha_salida <= fechaFin;

        return coincideNombre
            && coincideEstado
            && coincideFechaInicio
            && coincideFechaFin;
    });
}

function pintarReservas(reservas) {
    cuerpo.replaceChildren();

    if (reservas.length === 0) {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");

        celda.colSpan = 8;
        celda.textContent = "No hay reservas que coincidan con los filtros.";

        fila.append(celda);
        cuerpo.append(fila);
        return;
    }

    for (const reserva of reservas) {
        const fila = document.createElement("tr");

        const valores = [
            reserva.id,
            reserva.nombre_huesped,
            reserva.nombre_alojamiento,
            reserva.fecha_entrada,
            reserva.fecha_salida,
            reserva.estado,
            `${reserva.importe ?? ""} €`
        ];

        for (const valor of valores) {
            const celda = document.createElement("td");
            celda.textContent = valor ?? "";
            fila.append(celda);
        }

        const acciones = document.createElement("td");
        const enlace = document.createElement("a");

        enlace.href =
            `/public/index.php?id=${encodeURIComponent(reserva.id)}`;
        enlace.textContent = "Ver detalle";

        acciones.append(enlace);
        fila.append(acciones);
        cuerpo.append(fila);
    }
}
