<?php

class Reserva
{
    private $id;
    private $nombre_huesped;
    private $email_huesped;
    private $nombre_alojamiento;
    private $fecha_entrada;
    private $fecha_salida;
    private $estado;
    private $importe;
    private $notas;
    private $fechaCreacion;

    public function __construct()
    {
        $this->id = 0;
        $this->nombre_huesped = "";
        $this->email_huesped = "";
        $this->nombre_alojamiento = "";
        $this->fecha_entrada = "";
        $this->fecha_salida = "";
        $this->estado = "";
        $this->importe = 0.00;
        $this->notas = "";
        $this->fechaCreacion = "";
    }

    //Getters
    public function getId()
    {
        return $this->id;
    }

    public function getNombreHuesped()
    {
        return $this->nombre_huesped;
    }

    public function getEmailHuesped()
    {
        return $this->email_huesped;
    }

    public function getnombreAlojamiento()
    {
        return $this->nombre_alojamiento;
    }

    public function getFechaEntrada()
    {
        return $this->fecha_entrada;
    }

    public function getFechaSalida()
    {
        return $this->fecha_salida;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function getImporte()
    {
        return $this->importe;
    }

    public function getNotas()
    {
        return $this->notas;
    }

    public function getFecha()
    {
        return $this->fechaCreacion;
    }

    //Setters
    public function setId($id)
    {
        return $this->id = $id;
    }

    public function setNombreHuesped($nombreHuesped)
    {
        return $this->nombre_huesped = $nombreHuesped;
    }

    public function setEmailHuesped($emailHuesped)
    {
        return $this->email_huesped = $emailHuesped;
    }

    public function setNombreAlojamiento($nombreAlojamiento)
    {
        return $this->nombre_alojamiento = $nombreAlojamiento;
    }

    public function setFechaEntrada($fechaEntrada)
    {
        return $this->fecha_entrada = $fechaEntrada;
    }

    public function setFechaSalida($fechaSalida)
    {
        return $this->fecha_salida = $fechaSalida;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function setImporte($importe)
    {
        return $this->importe = $importe;
    }

    public function setNotas($notas)
    {
        return $this->notas = $notas;
    }

}
