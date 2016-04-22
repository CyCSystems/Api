<?php

require_once 'login.php';

class Conexion{
    private static $db = null;
    private static $pdo;

    /**
     * Conexion constructor.
     */
    final private function __construct(){
        try{
            self::obtenerDB();
        }catch(PDOException $e){

        }
    }

    /**
     * Retorna en la única instancia de la clase
     * @return ConexionBD|null
     */
    public static function obtenerInstancia(){
        if(self::$db === null){
            self::$db = new self();
        }
        return self::$db;
    }

    /**
     * Crear una nueva conexión PDO basada
     * en las constantes de conexión
     * @return PDO Objeto PDO
     */
    public function obtenerDB(){
        if(self::$pdo == null){
            self::$pdo = new PDO(
                'mysql:dbname='.BASE_DE_DATOS.
                ';host='.NOMBRE_HOST.";",
                USUARIO,
                CONTRASENA,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );

            self::$pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    /**
     * Evita la Clonacion del Objeto
     */
    final protected function  __clone(){

    }

    function __destruct()
    {
        self::$pdo = null;
    }
}