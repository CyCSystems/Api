<?php


abstract class vistaApi{
    //Codigo de error
    public $Estado;

    /**
     * @param $Cuerpo
     * @return mixed
     */
    public abstract function imprimir($Cuerpo);
}