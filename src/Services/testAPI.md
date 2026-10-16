# 🧪 Guía de Pruebas y Casos de Uso - API de Reservas

Esta guía contiene todas las peticiones listas para copiar y probar en **Thunder Client** (o Postman), abarcando el flujo correcto de la aplicación y los casos de error para comprobar su robustez.

---

## ⚙️ Configuración Global para Thunder Client
* **URL Base:** `http://localhost:8008/api`
* **Headers comunes requeridos:**
  * `Authorization`: `Bearer MI_TOKEN_SECRETO_123`
  * `Content-Type`: `application/json`

---

## 1. POST /api/postReserva.php (Crear Reserva)

### A. Caso de Éxito (Creación Correcta)
* **Método:** `POST`
* **URL:** `http://localhost:8008/api/postReserva.php`
* **Body (JSON):**
```json
{
    "nombre_huesped": "Laura Martínez",
    "email_huesped": "laura.martinez@example.com",
    "nombre_alojamiento": "Hotel Gran Vía",
    "fecha_entrada": "2026-08-01",
    "fecha_salida": "2026-08-07",
    "estado": "pendiente",
    "importe": 350.00,
    "notas": "Solicita cama de matrimonio"
}
```
* **Respuesta Esperada:** `201 Created`
* **Verificación extra:** Comprueba en tu base de datos que se ha insertado la reserva y automáticamente se ha creado un registro en la tabla `reserva_evento` con el tipo `"creada"`.

### B. Pruebas de Robustez y Errores (Validación)
* **Error 401 - Sin Token o Token Inválido:**
  * Elimina la cabecera `Authorization`.
  * **Respuesta Esperada:** `401 Unauthorized`
* **Error 400 - Campos vacíos y formato de correo erróneo:**
  * **Body (JSON):**
    ```json
    {
        "nombre_huesped": "",
        "email_huesped": "correo-mal-formato",
        "fecha_entrada": "2026-08-10",
        "fecha_salida": "2026-08-05"
    }
    ```
  * **Respuesta Esperada:** `400 Bad Request` (Devolverá array con los errores de validación de campos obligatorios, email y fechas invertidas).
* **Error 400 - Importe negativo:**
  * Añade `"importe": -100.00` al JSON de creación.
  * **Respuesta Esperada:** `400 Bad Request` indicando que el importe no puede ser negativo.

---

## 2. PUT /api/putReservas.php (Actualizar Reserva Parcialmente)

### A. Caso de Éxito (Actualizar Estado y/o Importe)
* **Método:** `POST` (o `PUT` si tu entorno lo permite)
* **URL:** `http://localhost:8008/api/putReservas.php`
* **Body (JSON):**
```json
{
    "id": 1,
    "estado": "confirmada",
    "importe": 380.00
}
```
* **Respuesta Esperada:** `200 OK` ("Reserva actualizada correctamente")

### B. Pruebas de Robustez y Errores en Actualización
* **Error 400 - Falta el ID obligatorio:**
  * **Body (JSON):**
    ```json
    {
        "estado": "cancelada"
    }
    ```
  * **Respuesta Esperada:** `400 Bad Request` ("El ID de la reserva es obligatorio.")
* **Error 404 - Reserva no encontrada:**
  * **Body (JSON):**
    ```json
    {
        "id": 9999,
        "estado": "confirmada"
    }
    ```
  * **Respuesta Esperada:** `404 Not Found` ("Reserva no encontrada")
* **Error 400 - Fechas incoherentes:**
  * **Body (JSON):**
    ```json
    {
        "id": 1,
        "fecha_entrada": "2026-09-10",
        "fecha_salida": "2026-09-01"
    }
    ```
  * **Respuesta Esperada:** `400 Bad Request` ("La fecha de salida no puede ser anterior a la fecha de entrada.")

---

## 3. GET /api/getReservas.php (Listar Reservas)

* **Método:** `GET`
* **URL:** `http://localhost:8008/api/getReservas.php`
* **Headers:** Incluir token `Authorization`.
* **Respuesta Esperada:** `200 OK` con el listado JSON de todas las reservas almacenadas.