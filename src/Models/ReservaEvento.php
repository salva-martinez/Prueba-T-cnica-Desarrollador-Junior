<?php

class ReservaEvento
{
    private $id;
    private $reserva_id;
    private $tipo;
    private $descripcion;
    private $fecha_creacion;

    public function __construct()
    {

        $this->id = 0;
        $this->reserva_id = 0;
        $this->tipo = "";
        $this->descripcion = "";
        $this->fecha_creacion = "";

    }

    //Getters
    public function getId()
    {
        return $this->id;
    }

    public function getReservaId()
    {
        return $this->reserva_id;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getdescripcion()
    {
        return $this->descripcion;
    }

    public function getFecha()
    {
        return $this->fecha_creacion;
    }

    //Setters
    public function setId($id)
    {
        return $this->id = $id;
    }

    public function setReservaId($reserva_id)
    {
        return $this->reserva_id = $reserva_id;
    }

    public function setTipo($tipo)
    {
        return $this->tipo = $tipo;
    }

    public function setDescripcion($descripcion)
    {
        return $this->descripcion = $descripcion;
    }

}
