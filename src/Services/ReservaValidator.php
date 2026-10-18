<?php

class ReservaValidator
{


    public static function validarCreacion(array $input): array
    {
        $errores = [];

        $nombre = trim($input['nombre_huesped'] ?? '');

        if ($nombre === '') {
            $errores[] = "El nombre del huésped es obligatorio.";
        } elseif (mb_strlen($nombre) > 100) {
            $errores[] = "El nombre del huésped no puede superar los 100 caracteres.";
        }

        $email = trim($input['email_huesped'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del correo electrónico no es válido.";
        }

        $alojamiento = trim($input['nombre_alojamiento'] ?? '');

        if ($alojamiento === '') {
            $errores[] = "El nombre del alojamiento es obligatorio.";
        }

        $entrada = $input['fecha_entrada'] ?? '';
        $salida = $input['fecha_salida'] ?? '';

        if ($entrada === '' || $salida === '') {
            $errores[] = "Las fechas de entrada y salida son obligatorias.";
        } else {
            $fechaEntrada = DateTime::createFromFormat('!Y-m-d', $entrada);
            $fechaSalida = DateTime::createFromFormat('!Y-m-d', $salida);

            $entradaValida = $fechaEntrada
                && $fechaEntrada->format('Y-m-d') === $entrada;

            $salidaValida = $fechaSalida
                && $fechaSalida->format('Y-m-d') === $salida;

            if (!$entradaValida || !$salidaValida) {
                $errores[] = "Las fechas deben tener un formato válido.";
            } elseif ($salida <= $entrada) {
                $errores[] = "La fecha de salida debe ser posterior a la fecha de entrada.";
            }
        }

        if (
            !isset($input['importe'])
            || $input['importe'] === ''
            || !is_numeric($input['importe'])
            || !is_finite((float) $input['importe'])
            || (float) $input['importe'] < 0
        ) {
            $errores[] = "El importe es obligatorio, debe ser numérico y no puede ser negativo.";
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
