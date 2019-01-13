<?php
  /* @Thalys A. Wolf
    * Em construção, essa classe vai montar os SQL para SELECT
  */
  class selectSQL extends ClassDB {
    private $propriedades = [];
    private $colunas = [];
    private $criterios = [];


    public function addColunas($colunas){
      foreach ($colunas as $key) {
        $this->colunas[] = $key;
      }
    }



  }


 ?>
