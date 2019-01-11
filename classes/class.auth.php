<?php
class Auth extends ClassDB implements InterfaceConfig {

  use Api;
  private $protected = false;
  private $user;
  private $pass;

  public function get_protected(){return $this->protected;}

  public function __construct($params = null){
    parent::__construct();
    $this->validateRequest(POST);
    $this->user = $params['user'];
    $this->pass = $params['pass'];
  }

  public function auth(){
                            # nome do campo, valor passado, tipagem do campo e se o campo é obrigatório (padrão true)
    self::validateParameter('user', $this->user, STRING);
    self::validateParameter('pass', $this->pass, STRING);

    $aBind[':usuario'] = $this->user;
    $aBind[':senha'] = $this->pass;

    $sql = 'SELECT DISTINCT usu.codusuario
                 , usu.nome
                 , usu.usuario
                 , usu.datahora
                 , usu.senha
                 , (SELECT DISTINCT ru.codRevenda
                      FROM '.DB_NAME.'.btv_sis_usuarios_revendas ru
                     WHERE ru.codUsuario = usu.codusuario LIMIT 1 ) codrevenda
                 , usu.email
                 , usu.situacao
                 , usu.consultorresponsavel
                 , usu.cod_setor
                 , CONCAT("[", (SELECT GROUP_CONCAT( DISTINCT ru.codRevenda)
                                FROM '.DB_NAME.'.btv_sis_usuarios_revendas ru
                                WHERE ru.codUsuario = usu.codusuario ) ,"]") revendas

                 , CONCAT("[", (SELECT GROUP_CONCAT(DISTINCT g.codgrupo)
                                 FROM '.DB_NAME.'.btv_sis_grupos_usuarios g
                                WHERE g.codusuario = usu.codusuario )  ,"]") grupos

              FROM '.DB_NAME.'.btv_sis_usuarios usu
              LEFT JOIN '.DB_NAME.'.btv_sis_usuarios_revendas r ON r.codUsuario = usu.codusuario
             WHERE usu.usuario = :usuario
               AND usu.senha = :senha';

    $curentUser = $this->select($sql, $aBind);
    unset($aBind);
    if($curentUser AND $curentUser[0]['situacao'] == 0){
      // valida se o usuario esta ativo
      $curentUser = $curentUser[0];

      $payload = [
        'iat' => time(),
        'iss' => URL,
        'exp' => time() + (60*60)*24, //(60*60)*24  valido por 1 dia
        'user' => $curentUser['codusuario']
      ];

      $jwt = new JWT();
      $token = $jwt->encode($payload, SALT_KEY);

      $result = ['token'=>$token,'user'=>$curentUser];

      $_SESSION = $result;

    }else{
      self::throwError(403, 'Usuário ou senha incorreta', 'INVALID_USER_PASS');
    }

    return $result;
  }

  public static function validateToken($token){
    //echo $token;
    $payload = JWT::decode($token, SALT_KEY, ['HS256']);

    $aBind[':codusuario'] = $payload->user;

    $sql = 'SELECT DISTINCT usu.codusuario
                 , usu.nome
                 , usu.usuario
                 , usu.datahora
                 , (SELECT DISTINCT ru.codRevenda
                      FROM '.DB_NAME.'.btv_sis_usuarios_revendas ru
                     WHERE ru.codUsuario = usu.codusuario LIMIT 1 ) codrevenda
                 , usu.email
                 , usu.situacao
                 , usu.consultorresponsavel
                 , usu.cod_setor
                 , CONCAT("[", (SELECT GROUP_CONCAT( DISTINCT ru.codRevenda)
                                FROM '.DB_NAME.'.btv_sis_usuarios_revendas ru
                                WHERE ru.codUsuario = usu.codusuario ) ,"]") revendas

                 , CONCAT("[", (SELECT GROUP_CONCAT(DISTINCT g.codgrupo)
                                 FROM '.DB_NAME.'.btv_sis_grupos_usuarios g
                                WHERE g.codusuario = usu.codusuario )  ,"]") grupos

              FROM btv_sis_usuarios usu
              LEFT JOIN btv_sis_usuarios_revendas r ON r.codUsuario = usu.codusuario
             WHERE usu.codusuario = :codusuario AND situacao = 0';

    $user = ClassDB::select($sql,$aBind);
    unset($aBind);
    $_SESSION['user'] = $payload->user;
    $_SESSION['bases'] =  $user;
    if (!is_array($user)) {
      self::throwError(403, 'Usuário não foi encontrado ou está desativado.', 'USER_NOT_FOUND');
    }
    if($payload){
      return true;
    }

  }

  }

  ?>
