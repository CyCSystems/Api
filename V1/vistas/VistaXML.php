<?php

require_once "vistaApi.php";

class VistaXML extends vistaApi{

    public function imprimir($Cuerpo)
    {
        if($this->Estado){
            http_response_code($this->Estado);
        }

        header('Content-Type: text/xml');
        $xml = new SimpleXMLElement('<respuesta/>');

    }

    public function parsearArreglo($data, &$xml_data){
        foreach ($data as $key => $value){
            if(is_array($value)){
                if(is_numeric($key)){
                    $key = 'item'.$key;
                }
                $subnode = $xml_data->addChild($key);
                self::parsearArreglo($value, $subnode);
            }else{
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}