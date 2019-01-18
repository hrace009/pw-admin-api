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
                            # nome do campo, valor passado, tipagem do campo e se o campo é obrigatório (padrão false)
    self::validateParameter('user', $this->user, STRING, true);
    self::validateParameter('pass', $this->pass, STRING, true);
    $aBind[':usuario'] = $this->user;
    $aBind[':senha'] = md5($this->pass.SALT_KEY);
    // var_dump($aBind);
    $sql = 'SELECT * FROM admin WHERE username = :usuario AND passwd = :senha';

    $curentUser = $this->select($sql, $aBind);

    unset($aBind);
    if(sizeof($curentUser)>0){
      // valida se o usuario esta ativo
      $curentUser = $curentUser[0];

      $payload = [
        'iat' => time(),
        'iss' => URL,
        'exp' => time() + (60*60)*24, //(60*60)*24  valido por 1 dia
        'user' => $curentUser['id']
      ];

      $jwt = new JWT();
      $token = $jwt->encode($payload, SALT_KEY);

      $result = ['token'=>$token,'user'=>$curentUser];


    }else{
      self::throwError(401, 'Usuário ou senha incorreta', 'INVALID_USER_PASS');
    }

    return $result;
  }

  public static function validateToken($token){

    $payload = JWT::decode($token, SALT_KEY, ['HS256']);
     
    $aBind[':id'] = $payload->user;

    $sql = 'SELECT * FROM admin WHERE id = :id';

    $user = ClassDB::select($sql,$aBind);
    unset($aBind);
    if (!is_array($user)) {
      self::throwError(401, 'Usuário não foi encontrado.', 'USER_NOT_FOUND');
    }

    if($payload){
      return true;
    }

  }

  }

  ?>
