<?php

  class User extends ClassDB implements InterfaceConfig{
    use Api;
    private $protected = false;

    private $lista = array();

    public function get_protected(){
      return $this->protected;
    }

    public function create($user){
      $this->validateRequest(POST);
      // Valida os paramêtros, função encontra-se na trait API
      $this->validateParameter('name', $user['name'], STRING, true);
      $this->validateParameter('passwd', $user['passwd'], STRING, true);
      $this->validateParameter('prompt', $user['prompt'], STRING, true); //na tabela o Prompt é P maiúsculo
      $this->validateParameter('answer', $user['answer'], STRING, true);
      $this->validateParameter('truename', $user['truename'], STRING, true);
      $this->validateParameter('idnumber', $user['idnumber'], STRING, true);
      $this->validateParameter('email', $user['email'], STRING, true);
      $this->validateParameter('mobilenumber', $user['mobilenumber'], STRING);
      $this->validateParameter('province', $user['province'], STRING);
      $this->validateParameter('city', $user['city'], STRING);
      $this->validateParameter('phonenumber', $user['phonenumber'], STRING);
      $this->validateParameter('address', $user['address'], STRING);
      $this->validateParameter('postalcode', $user['postalcode'], STRING);
      $this->validateParameter('gender', $user['gender'], INTEGER);
      $this->validateParameter('birthday', $user['birthday'], STRING);
      $this->validateParameter('qq', $user['qq'], STRING);
      $this->validateParameter('passwd2', $user['passwd2'], STRING);

      if (is_array($this->getUserByName($user['name']))) {
        $this->throwError(403, 'Username já cadastrado', 'DUPLICATE NAME');
      }

      // monta o SQL para inserir usuários e junta todos os parametros em um array pra fazer o bindParams
      $aBind = array();
      $sBodySql = '';
      foreach ($user as $key => $value) {
        $aBind[':'.$key] = $value;
        if ($sBodySql == '') {
          $sBodySql = ':'.$key;
        }else{
          $sBodySql .= ', :'.$key;
        }
      }

      $sSql = '
        CALL adduser ('.$sBodySql.');
      ';

      try {
        $res = $this->insert($sSql, $aBind);
        unset($aBind);
        return $this->successMessage('O usuário '.$user['name'].' foi cadastrado com sucesso.');
      } catch (\Exception $e) {
        echo '<pre>'.$e;
      }
    }

    public function get(){
      $this->validateRequest(GET);
      $sSql = '
              SELECT
                id
                name,
                truename,
                email
              FROM
                  users
              LIMIT 30;
      ';

      $res = $this->select($sSql);
      $this->lista['previous'] = null;
      $this->lista['next'] = $_SERVER['HTTP_HOST'].'/user/page/1';
      $this->lista['users'] = $res;
      return $this->lista;
    }

    public function get_user_detail($id){
      $this->validateRequest(GET);
      $aBind[':id'] = $id;
      $sSql = '
              SELECT
                *
              FROM
                  users
              WHERE
                id = :id
      ';

      $res = $this->select($sSql, $aBind);
      
      return $res;
    }

    public function page(){

    }

    private function getUserByName($name){
      $aBind[':name'] = $name;
      try {
        $sSql = 'SELECT * FROM users WHERE name = :name';
        $res = $this->select($sSql, $aBind);
        unset($aBind);
        return $res;
      } catch (\Exception $e) {
        echo '<pre>'.$e;
      }
    }

  }

 ?>
