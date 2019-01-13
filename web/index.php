<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

session_start();

require '../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

include('../classes/includes/consts.php');

$app = new Slim\App();


$app->get('/', function () {
    echo 'Api rodando';
});


// URL local http://pw-admin.thalys.com/{nome da classe}/{metodo}/{parametro único}
$app->get('/{class}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {

  // $_SESSION['Auth'] = $request->getHeader('Auth')[0];

  $class = $args['class'];
  $class = ucfirst($class);

  if (class_exists($class)) {

    $res = new $class;

    if (!$res->get_protected()) {

      $response->getBody()->write(json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    }else{
      return $response->withStatus(404, 'Not found');
    }

  }else{
     return $response->withStatus(404, 'Not found');
  }

});

$app->get('/{class}/{method}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
  $class = $args['class'];
  $method = $args['method'];
  $class = ucfirst($class);
  if (class_exists($class)) {
    $res = new $class;
    if ($res->get_protected()) {
      return $response->withStatus(404, 'Not found');
    }
    if (method_exists($res, $method)) {
      $response->getBody()->write(json_encode($res->{$method}(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }else{
      return $response->withStatus(404, 'Not found');
    }
  }
});

$app->get('/{class}/{method}/{param}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {

  $class = $args['class'];
  $method = $args['method'];
  $param = $args['param'];
  $class = ucfirst($class);

  if (class_exists($class)) {

    $res = new $class;

    if ($res->get_protected()) {

      return $response->withStatus(404, 'Not found');
    }
    if (method_exists($res, $method)) {

      $res = $res->{$method}($param);
      $response->getBody()->write(json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }else{
      return $response->withStatus(404, 'Not found');
    }

  }

});

$app->get('/{class}/{method}/{param1}/{param2}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {

  $class = $args['class'];
  $method = $args['method'];
  $param1 = $args['param1'];
  $param2 = $args['param2'];
  $class = ucfirst($class);

  if (class_exists($class)) {

    $res = new $class;

    if ($res->get_protected()) {

      return $response->withStatus(404, 'Not found');
    }
    if (method_exists($res, $method)){

// Api::throwError(406, "O tipo do parâmetro do campo  está incorreto.", 'VALIDATE_PARAMETER_DATATYPE');

      $res = $res->{$method}($param1,$param2);

      $response->getBody()->write(json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    }else{
      return $response->withStatus(404, 'Not found');
    }

  }

});

$app->post('/{class}/{method}',  function (ServerRequestInterface $request, ResponseInterface $response, $args){
  $class = $args['class'];
  $method = $args['method'];
  $class = ucfirst($class);
  if (class_exists($class)) {
    $res = new $class;
    if (method_exists($res, $method)) {
      $res = $res->{$method}(json_decode($request->getBody(), true));
      $response->getBody()->write(json_encode($res, true));
    }else{
      return $response->withStatus(404, 'Not found');
    }
  }
});

$app->post('/auth',  function (ServerRequestInterface $request, ResponseInterface $response, $args){
  $res = new Auth(json_decode($request->getBody(), true));
  $response->getBody()->write(json_encode($res->auth(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
});

//MIDDLEWARE
$app->add(function($request, $response, $next) {
    $response = $next($request, $response);
    return $response->withHeader('Content-Type', 'application/json');
});

/* Thalys
* Middleware abaixo valida o token do cabeçalho
* Se o token for inválido ele já informa o status e uma mensagem.
*/
// $app->add(function ($request, $response, $next) {
//
//   if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
//
//     $response->write('{}');
//
//   }else{
//     if ($_SERVER['REQUEST_URI'] !== '/auth') {
//
//       $token = $request->getHeader('Auth');
//
//       $_SERVER['REDIRECT_HTTP_AUTH'] = isset($_SERVER['REDIRECT_HTTP_AUTH']) ? $_SERVER['REDIRECT_HTTP_AUTH']: null;
//
//       $token = isset($token[0]) ? $token[0] : $_SERVER['REDIRECT_HTTP_AUTH'];
//
//       $_SESSION['Auth'] = $token;
//
//       Auth::validateToken($token);
//       $response = $next($request, $response);
//
//     }else{
//       $response = $next($request, $response);
//     }
//
//   }
//
//   return $response;
// });

$app->run();
?>
