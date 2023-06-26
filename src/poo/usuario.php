<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once "accesoDatos.php";
require_once "islimeable.php";

class Usuario{

    static function Verificar($correo, $clave){

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT id, nombre, correo, foto, id_perfil FROM usuarios WHERE correo = :correo AND clave = :clave");        
        $consulta->bindValue(':correo', $correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $clave, PDO::PARAM_INT);			
        $consulta->execute();

        $usuario= $consulta->fetchObject('usuario');

		return $usuario;	
    }
}