<?php

require_once "vistaApi.php";

class vistaJson extends vistaApi{

    
    public function imprimir($Cuerpo)
    {
        if($this->Estado){
            http_response_code($this->Estado);
        }
        header('Content-Type: application/json; charset=utf8');
        echo json_encode($Cuerpo, JSON_PRETTY_PRINT);
        exit;
    }
}
