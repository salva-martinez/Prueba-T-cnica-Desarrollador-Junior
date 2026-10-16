<?php

class ReservaValidator
{

    public static function validarCreacion(array $input): array
    {
        $errores = [];

        if (empty($input['nombre_huesped'])) {
            $errores[] = "El nombre del huésped es obligatorio.";
        } elseif (strlen($input['nombre_huesped']) > 100) {
            $errores[] = "El nombre del huésped no puede superar los 100 caracteres.";
        }

        if (empty($input['email_huesped']) || !filter_var($input['email_huesped'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del correo electrónico no es válido.";
        }

        if (isset($input['importe'])) {
            if (!is_numeric($input['importe']) || $input['importe'] < 0) {
                $errores[] = "El importe debe ser un valor numérico y no puede ser negativo.";
            }
        }

        if (empty($input['fecha_entrada']) || empty($input['fecha_salida'])) {
            $errores[] = "Las fechas de entrada y salida son obligatorias.";
        } elseif ($input['fecha_entrada'] > $input['fecha_salida']) {
            $errores[] = "La fecha de salida no puede ser anterior a la fecha de entrada.";
        }

        return $errores;
    }

    public static function validarActualizacion(array $input): array
    {
        $errores = [];

        if (empty($input['id'])) {
            $errores[] = "El ID de la reserva es obligatorio.";
        }

        if (isset($input['email_huesped']) && !filter_var($input['email_huesped'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del correo electrónico no es válido.";
        }

        if (isset($input['importe']) && (!is_numeric($input['importe']) || $input['importe'] < 0)) {
            $errores[] = "El importe no puede ser negativo.";
        }

        if (!empty($input['fecha_entrada']) && !empty($input['fecha_salida'])) {
            if ($input['fecha_entrada'] > $input['fecha_salida']) {
                $errores[] = "La fecha de salida no puede ser anterior a la fecha de entrada.";
            }
        }

        return $errores;
    }
}
