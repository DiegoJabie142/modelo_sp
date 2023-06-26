<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once __DIR__ . "/autentificadora.php";
require_once __DIR__ . "/islimeable.php";
require_once __DIR__ . "/imiddleware.php";
require_once __DIR__ . "/usuario.php";

class Verificadora{

    public function VerificarUsuario(Request $request, Response $response, array $args) : Response{

        $retorno = new stdClass();
        
        $params = $request->getParsedBody();
        $obj_json = json_decode($params["obj_json"]);
        $correo = $obj_json->correo;
        $clave = $obj_json->clave;

        $usuario = Usuario::verificar($correo, $clave);

        if(isset($usuario->id)){
            $token = Autentificadora::crearJWT(json_encode($usuario),600);
            $newResponse = $response->withStatus(200);
            $status = 200;
        }else{
            $token = Autentificadora::crearJWT("", 0);
            $newResponse = $response->withStatus(403);
            $status = 403;
        }
        $retorno->jwt = $token;
        $retorno->status = $status;

        $newResponse->getBody()->write(json_encode($retorno));
        
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function ValidarParametrosUsuario(Request $request, RequestHandler $handler) : ResponseMW{
        
        $mensajeRtn = "";
        $error = false;
        $obj_datos = new stdClass();

        $params = $request->getParsedBody();
        $obj_json = isset($params["obj_json"]) ? json_decode($params["obj_json"]) : NULL;
        $correo = isset($obj_json->correo) ? $obj_json->correo : NULL;
        $clave = isset($obj_json->clave) ? $obj_json->clave : NULL;

        if(!isset($obj_json)){
           $mensajeRtn = "El parametro obj_json no fue recibido o su formato es incorrecto.";
           $error = true;
        }

        if(isset($obj_json) && (!isset($correo) || !isset($clave))){
            $mensajeRtn = "La clave y/o el correo no fue recibido.";
            $error = true;
        }else if($clave == "" || $correo == ""){
            $error = true;
            $mensajeRtn = "La clave y/o el correo están vacíos.";
        }

        if($error){
            $obj_datos->mensaje = $mensajeRtn;
            $status = 403;
        }else{
            $response = $handler->handle($request);
            $obj_datos->VerificarUsuario = json_decode($response->getBody());
            $status = 200;
        }

        $obj_datos->status = $status;

        $response = new ResponseMW($status);
        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerDataJWT(Request $request, Response $response, array $args) : Response {

        $token = $request->getHeaderLine('token'); 

        $newResponse = $response->withStatus(200);

        $newResponse->getBody()->write($this->obtenerPayLoad($token));

        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function obtenerPayLoad($token){

        $obj_rta = Autentificadora::obtenerPayLoad($token);
        $obj_rta->exito ? true : false;
    
        return json_encode($obj_rta);
    }

    public function ChequearJWT(Request $request, RequestHandler $handler) : ResponseMW{

        $token = $request->getHeaderLine('token'); 

        $obj_rta = Autentificadora::verificarJWT($token);

        if($obj_rta->verificado == TRUE){
            $response = $handler->handle($request);
            $obj_rta->datos = json_decode($response->getBody());
        }

        $response = new ResponseMW(200);
        $response->getBody()->write(json_encode($obj_rta));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ValidarParametrosCDAgregar(Request $request, RequestHandler $handler) : ResponseMW{
        
        $mensajeRtn = "";
        $error = false;
        $obj_datos = new stdClass();

        $params = $request->getParsedBody();

        $mensajeRtn = "";

        if(!isset($params['titulo'])){
            $mensajeRtn = "El parametro titulo no fue recibido o su formato es incorrecto.\n";
            $error = true;
        }
        if(!isset($params['cantante'])){
            $mensajeRtn .= "El parametro cantante no fue recibido o su formato es incorrecto.\n";
            $error = true;
        }
        if(!isset($params['anio'])){
            $mensajeRtn .= "El parametro anio no fue recibido o su formato es incorrecto.";
            $error = true;
        }

        if($error){
            $obj_datos->mensaje = $mensajeRtn;
            $status = 403;
        }else{
            $response = $handler->handle($request);
            $obj_datos->calleable = json_decode($response->getBody());
            $status = 200;
        }

        $obj_datos->status = $status;

        $response = new ResponseMW($status);
        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ValidarParametrosCDModificar(Request $request, RequestHandler $handler) : ResponseMW{
        
        $requestBody = $request->getBody()->getContents();
        $obj = json_decode($requestBody);
	    $micd = new Cd();

        if(isset($obj->id)){
            $micd->id = $obj->id;
        }
        if(isset($obj->titulo)){
            $micd->titulo = $obj->titulo;
        }

        if(isset($obj->cantante)){
            $micd->cantante = $obj->cantante;
        }

        if(isset($obj->anio)){
            $micd->año = $obj->anio;
        }

        $mensajeRtn = "";
        $error = false;
        $obj_datos = new stdClass();

        $mensajeRtn = "";

        if(!isset($micd->id)){
            $mensajeRtn = "El parametro id no fue recibido o su formato es incorrecto.\n";
            $error = true;
        }

        if(!isset($micd->titulo)){
            $mensajeRtn .= "El parametro titulo no fue recibido o su formato es incorrecto.\n";
            $error = true;
        }

        if(!isset($micd->cantante)){
            $mensajeRtn .= "El parametro cantante no fue recibido o su formato es incorrecto.\n";
            $error = true;
        }
        if(!isset($micd->año)){
            $mensajeRtn .= "El parametro anio no fue recibido o su formato es incorrecto.";
            $error = true;
        }

        if($error){
            $obj_datos->mensaje = $mensajeRtn;
            $status = 500;
        }else{
            $response = $handler->handle($request);
            $obj_datos->calleable = json_decode($response->getBody());
            $status = 200;
        }

        $obj_datos->status = $status;

        $response = new ResponseMW($status);
        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ValidarParametrosCDBorrar(Request $request, RequestHandler $handler) : ResponseMW{
        
        $requestBody = $request->getBody()->getContents();
        $obj = json_decode($requestBody);
	    $micd = new Cd();

        if(isset($obj->id)){
            $micd->id = $obj->id;
        }

        $mensajeRtn = "";
        $error = false;
        $obj_datos = new stdClass();

        $mensajeRtn = "";

        if(!isset($micd->id)){
            $mensajeRtn = "El parametro id no fue recibido o su formato es incorrecto.";
            $error = true;
        }

        if($error){
            $obj_datos->mensaje = $mensajeRtn;
            $status = 500;
        }else{
            $response = $handler->handle($request);
            $obj_datos->calleable = json_decode($response->getBody());
            $status = 200;
        }

        $obj_datos->status = $status;

        $response = new ResponseMW($status);
        $response->getBody()->write(json_encode($obj_datos));

        return $response->withHeader('Content-Type', 'application/json');
    }
}