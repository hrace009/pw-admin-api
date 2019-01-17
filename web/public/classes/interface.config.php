<?php
/**
 *
 */
interface InterfaceConfig {
  /*
    * Método abaixo é obrigatório em todas as classes
    * classes com o atributo protected = true, não serão acessadas via URL e somente por código.
  */
  public function get_protected();
}


 ?>
