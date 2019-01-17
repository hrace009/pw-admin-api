<?php
trait Api{

  public function validateParameter($fieldName, $value, $dataType, $required = false){
    if($required == true && empty($value)){
      $this->throwError(406, "O $fieldName é obrigatório.", 'VALIDATE_PARAMETER_REQUIRED');
    }
    switch ($dataType) {
      case BOOLEAN:
        if(!is_bool($value)){
          $this->throwError(406, "O campo $fieldName deve ser um valor booleano.", 'VALIDATE_PARAMETER_DATATYPE');
        };
        break;
      case INTEGER:
        if(!is_numeric($value)){
          $this->throwError(406, "O campo $fieldName deve ser um valor númerico.", 'VALIDATE_PARAMETER_DATATYPE');
        };
        break;
      case STRING:
        if(!is_string($value)){
          $this->throwError(406, "O campo $fieldName deve ser uma string.", 'VALIDATE_PARAMETER_DATATYPE');
        };
        break;
    }
    return;
  }

  public function validateRequest($tipo){
    if ($tipo == $_SERVER['REQUEST_METHOD']) {
      return;
    }else{
      $this->throwError(400, 'Esta URL só aceita requisições do tipo '.$tipo, 'REQUEST_NOT_VALID');
    }
  }

  public static function throwError($code, $msg, $statusText){
    $result = ['erro'=>$msg];
    header('Content-type:application/json');
    header("HTTP/1.0 $code $statusText");
    echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function successMessage($message){
      return array('status'=>'200','message'=>$message);
  }

}

 ?>
