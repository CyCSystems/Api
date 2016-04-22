<?php

require 'controladores/usuarios.php';
require 'vistas/VistaXML.php';
require 'vistas/VistaJson.php';
require 'utilidades/ExcepcionApi.php';

// Constantes de estado
const ESTADO_URL_INCORRECTA = 2;
const ESTADO_EXISTENCIA_RECURSO = 3;
const ESTADO_METODO_NO_PERMITIDO = 4;
// Obtiene el recurso

$vista = new vistaJson();

set_exception_handler(function($Exception) use ($vista){
    $cuerpo = array(
        "estado" => $Exception->estado,
        "mensaje" => $Exception->getMessage()
    );
    if($Exception->getCode()){
        $vista->Estado = $Exception->getCode();
    }else{
        $vista->Estado = 500;
    }
    $vista->imprimir($cuerpo);
});

// Extraer segmento de la url
if (isset($_GET['PATH_INFO']))
    $peticion = explode('/', $_GET['PATH_INFO']);
else
    throw new ExcepcionApi(ESTADO_URL_INCORRECTA, utf8_encode("No se reconoce la petición"));


$recurso = array_shift($peticion);
$recursos_existentes = array('contactos', 'usuarios');

//Comprobamos si existe el recurso

if(!in_array($recurso, $recursos_existentes)){
    throw new ExcepcionApi(ESTADO_EXISTENCIA_RECURSO, "No se reconoce el recurso al que intentas acceder");
}

$metodo = strtolower($_SERVER['REQUEST_METHOD']);

switch ($metodo){
    case 'get':

        break;
    case 'post':
        $vista->imprimir(usuarios::post($peticion));
        break;
    case 'put':

        break;
    case 'delete':

        break;
    default :
        //Metodo No aceptado
}