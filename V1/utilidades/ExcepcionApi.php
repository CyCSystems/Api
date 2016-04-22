<?php

class ExcepcionApi extends Exception{
    public $estado;

    public function __construct($estado, $mensaje, $codigo){
        $this->estado = $estado;
        $this->message = $mensaje;
        $this->code = $codigo;
    }
}