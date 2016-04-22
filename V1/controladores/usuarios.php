<?php

require '/../datos/Conexion.php';

class usuarios{

    const NOMBRE_TABLA = "usuario";
    const ID_USUARIO = "idUsuario";
    const NOMBRE = "nombre";
    const CONTRASENA = "contrasena";
    const CORREO = "correo";
    const CLAVE_API = "claveApi";

    const ESTADO_CREACION_EXITOSA = 1;
    const ESTADO_CREACION_FALLIDA = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_AUSENCIA_CLAVE_API = 4;
    const ESTADO_CLAVE_NO_AUTORIZADA = 5;
    const ESTADO_URL_INCORRECTA = 6;
    const ESTADO_FALLA_DESCONOCIDA = 7;
    const ESTADO_PARAMETROS_INCORRECTOS = 8;

    public static function post($peticion){
        if($peticion[0] == 'registro'){
            return self::registrar();
        }elseif ($peticion[0] == 'login'){
            return self::loguear();
        }else{
            throw new ExcepcionApi(self::ESTADO_ULR_INCORRECTA, "Url mal Formada", 400);
        }
    }

    private function registrar(){
        $cuerpo = file_get_contents('php://input');
        //$cuerpo = http_get_request_body();
        $usuario = json_decode($cuerpo);

        $resultado = self::crear($usuario);
        switch ($resultado){
            case self::ESTADO_CREACION_EXITOSA:
                    http_response_code(201);
                    return
                        [
                            "estado" => self::ESTADO_CREACION_EXITOSA,
                            "mensaje" => utf8_encode("Registro con Exito")
                        ];
                break;
            case self::ESTADO_CREACION_FALLIDA:
                    throw new ExcepcionApi(self::ESTADO_CREACION_FALLIDA, "Ha ocurrido un Error");
                break;
            default:
                throw new ExcepcionApi(self::ESTADO_FALLA_DESCONOCIDA, "Falla desconocida", 400);
        }
    }

    private function crear($datosUsuario){
        $nombre = $datosUsuario->nombre;
        $contrasena = $datosUsuario->contrasena;
        $contrasenaEncriptada = self::encriptarContrasena($contrasena);
        $correo = $datosUsuario->correo;
        $claveAPI = self::generarClaveApi();

        try{
            $pdo = Conexion::obtenerInstancia()->obtenerDB();

            $query = "INSERT INTO ". self::NOMBRE_TABLA . " (".
                self::NOMBRE .",".
                self::CONTRASENA .",".
                self::CLAVE_API .",".
                self::CORREO .")".
                "VALUES (?,?,?,?)";

            $sentencia = $pdo->prepare($query);

            $sentencia->bindParam(1, $nombre);
            $sentencia->bindParam(2, $contrasenaEncriptada);
            $sentencia->bindParam(3, $claveAPI);
            $sentencia->bindParam(4, $correo);

            $resultado = $sentencia->execute();

            if($resultado){
                return self::ESTADO_CREACION_EXITOSA;
            }else{
                return self::ESTADO_CREACION_FALLIDA;
            }
        }catch (PDOException $e){
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Funcion para encriptar las contraseñas con MD5
     * @param $ContrasenaPlana
     * @return null|string
     */
    private function encriptarContrasena($ContrasenaPlana){
        if($ContrasenaPlana){
            return md5($ContrasenaPlana);
        }else return null;
    }

    /**
     * Funcion para generar claves aleatorias para la API
     * @return string
     */
    private function generarClaveApi(){
        return md5(microtime().rand());
    }

    private function loguear(){
        $respuesta = array();

        $body = file_get_contents('php://input');
        $usuario = json_decode($body);

        $correo = $usuario->correo;
        $contrasena = $usuario->contrasena;

        if(self::autenticar($correo, $contrasena)){
            $usuarioBD = self::obtenerUsuarioPorCorreo($correo);

            if($usuarioBD != NULL){
                http_response_code(200);
                $respuesta["nombre"] = $usuarioBD["nombre"];
                $respuesta["correo"] = $usuarioBD["correo"];
                $respuesta["claveApi"] = $usuarioBD["claveApi"];
                return
                    [
                        "estado" => 1, "usuario" => $respuesta
                    ];
            }else{
                throw new ExcepcionApi(self::ESTADO_PARAMETROS_INCORRECTOS, utf8_encode("Correo o Contraseña invalidos"));
            }
        }
    }

    private function autenticar($correo, $contrasena){
        $Query = "SELECT contrasena FROM " . self::NOMBRE_TABLA . " WHERE " . self::CORREO . " = ?";
        try{
            $sentencia = Conexion::obtenerInstancia()->obtenerDB()->prepare($Query);
            $sentencia->bindParam(1, $correo);
            $sentencia->execute();
            if($sentencia){
                $resultado =  $sentencia->fetch();
                if(self::validarContrasena($contrasena, $resultado['contrasena'])){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }catch(PDOException $e){
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private function validarContrasena($contrasenaPlana, $contrasenaMd5){
        return (md5($contrasenaPlana) == $contrasenaMd5) ? true : false ;
    }

    private function obtenerUsuarioPorCorreo($correo){
        $Query = "SELECT 
                        " . self::NOMBRE . ", " . self::CONTRASENA . ", " . self::CORREO . ", " . self::CLAVE_API . " 
                  FROM 
                        " . self::NOMBRE_TABLA . "
                  WHERE 
                        " . self::CORREO . " = ?";
        $sentencia =  Conexion::obtenerInstancia()->obtenerDB()->prepare($Query);

        $sentencia->bindParam(1, $correo);

        if($sentencia->execute()){
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        }else{
            return null;
        }
    }
}