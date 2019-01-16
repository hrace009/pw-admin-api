<?php
  /* @Thalys A. Wolf
    * Em construção, essa classe vai montar os SQL para SELECT
  
  class selectSQL extends ClassDB {
    private $propriedades;
    private $colunas = [];
    private $criterios = [];


    // Classe não permite montar selects com aninhamento, pois com JOINS é mais peformático
    public function addPropriedade($propriedade){
      $this->propriedades = $propriedade;
    }

    public function addColuna($coluna){
      foreach ($colunas as $key =>$value) {
        $this->colunas[] = $key;
      }
    }

    public function addCriterio($criterio, $operador = null){
      public function addCriterio($criterio){
        if (sizeof($this->criterio) > 0) {
          $this->criterio[] = $criterio;
        }else if ($operador != null){
          $this->criterio .= $operador.' '.$criterio;
        }
      }
    }

  }

  */


 ?>
