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

    $sql = 'SELECT * FROM users WHERE name = :usuario AND passwd = :senha';

    $curentUser = $this->select($sql, $aBind);
    unset($aBind);
    if(sizeof($curentUser)>0){
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

    $sql = 'buscar usuário';

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
