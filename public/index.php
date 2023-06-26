<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

//NECESARIO PARA GENERAR EL JWT
use Firebase\JWT\JWT;


$app = AppFactory::create();


//************************************************************************************************************//

$app->get('/', function (Request $request, Response $response, array $args) : Response {  

  $datos = new stdclass();

  $datos->mensaje = "API => GET";

  $newResponse = $response->withStatus(200);
  $newResponse->getBody()->write(json_encode($datos));

  return $newResponse->withHeader('Content-Type', 'application/json');
});

$app->post('/', function (Request $request, Response $response, array $args) : Response { 

  $datos = new stdclass();

  $datos->mensaje = "API => POST";

  $newResponse = $response->withStatus(200);
  $newResponse->getBody()->write(json_encode($datos));
  
  return $newResponse->withHeader('Content-Type', 'application/json');
});

$app->put('/', function (Request $request, Response $response, array $args) : Response {  

  $datos = new stdclass();

  $datos->mensaje = "API => PUT";

  $newResponse = $response->withStatus(200);
  $newResponse->getBody()->write(json_encode($datos));
  
  return $newResponse->withHeader('Content-Type', 'application/json');
});

$app->delete('/', function (Request $request, Response $response, array $args) : Response {  

  $datos = new stdclass();

  $datos->mensaje = "API => DELETE";

  $newResponse = $response->withStatus(200);
  $newResponse->getBody()->write(json_encode($datos));
  
  return $newResponse->withHeader('Content-Type', 'application/json');
});

//************************************************************************************************************//


//************************************************************************************************************//
// CREAR TOKEN
//************************************************************************************************************//

$app->post("/jwt/crearToken[/]", function (Request $request, Response $response, array $args) : Response {

  $datos = $request->getParsedBody();
  $ahora = time();
  
  //PARAMETROS DEL PAYLOAD -- https://tools.ietf.org/html/rfc7519#section-4.1 --
  //SE PUEDEN AGREGAR LOS PROPIOS, EJ. 'app'=> "API REST 2021"       
  $payload = array(
      'iat' => $ahora,            //CUANDO SE CREO EL JWT (OPCIONAL)
      'exp' => $ahora + (30),     //INDICA EL TIEMPO DE VENCIMIENTO DEL JWT (OPCIONAL)
      'data' => $datos,           //DATOS DEL JWT
      'app' => "API REST 2022"    //INFO DE LA APLICACION (PROPIO)
  );
    
  //CODIFICO A JWT (PAYLOAD, CLAVE, ALGORITMO DE CODIFICACION)
  $token = JWT::encode($payload, "miClaveSecreta", "HS256");

  $newResponse = $response->withStatus(200, "Éxito!!! JSON enviado.");
  
  //GENERO EL JSON A PARTIR DEL ARRAY.
  $newResponse->getBody()->write(json_encode($token));

  //INDICO EL TIPO DE CONTENIDO QUE SE RETORNARÁ (EN EL HEADER).
  return $newResponse->withHeader('Content-Type', 'application/json');

});
//************************************************************************************************************//

//************************************************************************************************************//
// VERIFICAR TOKEN
//************************************************************************************************************//

$app->post("/jwt/verificarToken[/]", function (Request $request, Response $response, array $args) : Response {
  
  $datos = $request->getParsedBody();
  $token = $datos['token'];

  $retorno = new stdClass();
  $status = 200;

  try {
    //DECODIFICO EL TOKEN RECIBIDO            
    JWT::decode(
        $token,                    //JWT
        "miClaveSecreta",          //CLAVE USASA EN LA CREACION
        ['HS256']                  //ALGORITMO DE CODIFICACION
      );

    $retorno->mensaje = "Token OK!!!";

  } 
  catch (Exception $e) {

    $retorno->mensaje = "Token no válido!!! --> " . $e->getMessage();
    $status = 500;
  }
  
  $newResponse = $response->withStatus($status);

  $newResponse->getBody()->write(json_encode($retorno));

  return $newResponse->withHeader('Content-Type', 'application/json');

});
//************************************************************************************************************//

//************************************************************************************************************//
// OBTENER PAYLOAD
//************************************************************************************************************//

$app->post("/jwt/obtenerPayLoad[/]", function (Request $request, Response $response, array $args) : Response {
  
  $datos = $request->getParsedBody();
  $token = $datos['token'];

  $retorno = new stdClass();
  $status = 200;

  try {

    $payLoad = JWT::decode(
        $token,
        "miClaveSecreta",
        ['HS256']
      );

    $retorno = $payLoad;
  } 
  catch (Exception $e) {

    $retorno->mensaje = "Token no válido!!! --> " . $e->getMessage();
    $status = 500;
  }

  $newResponse = $response->withStatus($status);

  $newResponse->getBody()->write(json_encode($retorno));

  return $newResponse->withHeader('Content-Type', 'application/json');

});
//************************************************************************************************************//

//************************************************************************************************************//
// OBTENER DATA
//************************************************************************************************************//

$app->post("/jwt/obtenerData[/]", function (Request $request, Response $response) {
  
  $datos = $request->getParsedBody();
  $token = $datos['token'];

  $retorno = new stdClass();
  $status = 200;

  try {

    $payLoadData = JWT::decode(
        $token,
        "miClaveSecreta",
        ['HS256']
      )->data;

    $retorno = $payLoadData;
  } 
  catch (Exception $e) {

    $retorno->mensaje = "Token no válido!!! --> " . $e->getMessage();
    $status = 500;
  }

  $newResponse = $response->withStatus($status);

  $newResponse->getBody()->write(json_encode($retorno));

  return $newResponse->withHeader('Content-Type', 'application/json');

  });
//************************************************************************************************************//

//************************************************************************************************************//
// POO
//************************************************************************************************************//
require_once __DIR__ . "/../src/poo/ejemploJWT.php";


$app->post('/jwt/poo/crear[/]', \EjemploJWT::class . ':crear');

$app->post('/jwt/poo/verificar[/]', \EjemploJWT::class . ':verificar');

$app->post('/jwt/poo/obtener_payload[/]', \EjemploJWT::class . ':obtenerPayLoad');

$app->post('/jwt/poo/obtener_datos[/]', \EjemploJWT::class . ':obtenerDatos');

$app->get('/jwt/poo/verificar[/]', \EjemploJWT::class . ':verificarPorHeader');

$app->get('/listado/autos[/]', \EjemploJWT::class . ':obtenerAutosJson');

$app->get('/listado/autos/jwt[/]', \EjemploJWT::class . ':obtenerAutosJson')->add(\EjemploJWT::class . ':verificarToken');


//************************************************************************************************************//

//************************************************************************************************************//
// POO USUARIO
//************************************************************************************************************//


require_once __DIR__ . "/../src/poo/verificadora.php";

$app->post('/login[/]', \Verificadora::class . ':VerificarUsuario')->add(\Verificadora::class . ':ValidarParametrosUsuario');
$app->get('/login/test', \Verificadora::class . ':ObtenerDataJWT')->add(\Verificadora::class. ':ChequearJWT');

use \Slim\Routing\RouteCollectorProxy as RouteCollectorProxy;
require_once __DIR__ . "/../src/poo/cd.php";

$app->group('/json_bd', function(RouteCollectorProxy $grupo){

  $grupo->get('/', \Cd::class . ':traerTodos');

  $grupo->get('/{id}', \Cd::class . ':traerUno');

  $grupo->post('/', \Cd::class . ':agregarUno')->add(\Verificadora::class . ':ValidarParametrosCDAgregar');

  $grupo->put('/', \Cd::class . ':modificarUno')->add(\Verificadora::class . ':ValidarParametrosCDModificar');

  $grupo->delete('/', \Cd::class . ':borrarUno')->add(\Verificadora::class . ':ValidarParametrosCDBorrar');

})->add(\Verificadora::class . ':ChequearJWT');

$app->run();
//CORRE LA APLICACIÓN.