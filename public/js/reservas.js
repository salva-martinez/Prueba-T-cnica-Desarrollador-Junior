
const formulario = document.querySelector("#filtros-reservas");
const cuerpo = document.querySelector("#cuerpo-reservas");
const estadoListado = document.querySelector("#estado-listado");
const paginacion = document.querySelector("#paginacion-reservas");
const botonLimpiar = document.querySelector("#limpiarFiltros");

const reservasPorPagina = 10;
let paginaActual = 1;
let reservasActuales = [];

if (formulario && cuerpo && estadoListado) {
    formulario.addEventListener("submit", (event) => {
        event.preventDefault();
        paginaActual = 1;
        cargarReservas();
    });

    cargarReservas();
}

if (botonLimpiar) {
    botonLimpiar.addEventListener("click", () => {
        document.querySelector("#nombre").value = "";
        document.querySelector("#estado").value = "";
        document.querySelector("#fecha_inicio").value = "";
        document.querySelector("#fecha_fin").value = "";

        paginaActual = 1;
        cargarReservas();
    });
}

async function cargarReservas() {
    estadoListado.textContent = "Cargando reservas...";

    try {
        const respuesta = await fetch("/api-proxy.php?accion=listar");

        let resultado;

        try {
            resultado = await respuesta.json();
        } catch {
            throw new Error("El servidor no ha devuelto una respuesta JSON válida.");
        }

        if (!respuesta.ok) {
            throw new Error(
                resultado.message || `Error HTTP ${respuesta.status}`
            );
        }

        if (resultado.status !== "success") {
            throw new Error(
                resultado.message || "La API ha devuelto un error."
            );
        }

        if (!Array.isArray(resultado.data)) {
            throw new Error("La respuesta de la API no contiene una lista válida.");
        }

        reservasActuales = filtrarReservas(resultado.data);

        const totalPaginas = Math.ceil(
            reservasActuales.length / reservasPorPagina
        );

        if (totalPaginas === 0) {
            paginaActual = 1;
        } else if (paginaActual > totalPaginas) {
            paginaActual = totalPaginas;
        }

        pintarReservas();

        estadoListado.textContent =
            `${reservasActuales.length} reservas encontradas.`;

    } catch (error) {
        reservasActuales = [];
        paginaActual = 1;

        estadoListado.textContent =
            `No se pudieron cargar las reservas: ${error.message}`;

        cuerpo.replaceChildren();

        if (paginacion) {
            paginacion.replaceChildren();
        }
    }
}

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

function pintarReservas() {
    cuerpo.replaceChildren();

    if (reservasActuales.length === 0) {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");

        celda.colSpan = 8;
        celda.textContent =
            "No hay reservas que coincidan con los filtros.";

        fila.append(celda);
        cuerpo.append(fila);

        if (paginacion) {
            paginacion.replaceChildren();
        }

        return;
    }

    const inicio = (paginaActual - 1) * reservasPorPagina;
    const reservasPagina = reservasActuales.slice(
        inicio,
        inicio + reservasPorPagina
    );

    for (const reserva of reservasPagina) {
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

        for (const [indice, valor] of valores.entries()) {
            const celda = document.createElement("td");

            if (indice === 5) {
                const etiqueta = document.createElement("span");
                const estado = String(valor ?? "").toLowerCase();

                etiqueta.classList.add(
                    "estado-etiqueta",
                    `estado-${estado}`
                );

                etiqueta.textContent =
                    estado.charAt(0).toUpperCase() + estado.slice(1);

                celda.append(etiqueta);
            } else {
                celda.textContent = valor ?? "";
            }

            fila.append(celda);
        }

        const acciones = document.createElement("td");
        const enlace = document.createElement("a");

        enlace.href =
            `/reservas/${encodeURIComponent(reserva.id)}`;
        enlace.textContent = "Ver detalle";

        acciones.append(enlace);
        fila.append(acciones);
        cuerpo.append(fila);
    }

    crearPaginacion();
}

function crearPaginacion() {
    if (!paginacion) {
        return;
    }

    paginacion.replaceChildren();

    const totalPaginas = Math.ceil(
        reservasActuales.length / reservasPorPagina
    );

    if (totalPaginas <= 1) {
        return;
    }

    const botonAnterior = document.createElement("button");
    botonAnterior.type = "button";
    botonAnterior.textContent = "Anterior";
    botonAnterior.disabled = paginaActual === 1;

    botonAnterior.addEventListener("click", () => {
        paginaActual--;
        pintarReservas();
    });

    paginacion.append(botonAnterior);

    for (let numero = 1; numero <= totalPaginas; numero++) {
        const boton = document.createElement("button");

        boton.type = "button";
        boton.textContent = numero;
        boton.className = "pagina-numero";
        boton.disabled = numero === paginaActual;

        if (numero === paginaActual) {
            boton.setAttribute("aria-current", "page");
        }

        boton.addEventListener("click", () => {
            paginaActual = numero;
            pintarReservas();
        });

        paginacion.append(boton);
    }

    const botonSiguiente = document.createElement("button");
    botonSiguiente.type = "button";
    botonSiguiente.textContent = "Siguiente";
    botonSiguiente.disabled = paginaActual === totalPaginas;

    botonSiguiente.addEventListener("click", () => {
        paginaActual++;
        pintarReservas();
    });

    paginacion.append(botonSiguiente);
}
