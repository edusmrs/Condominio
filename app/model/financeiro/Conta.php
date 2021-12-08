<?php

use Adianti\Database\TRecord;

class Conta extends TRecord
{
    const TABLENAME = 'conta';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    //Metodo Construct
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('categoria_conta');
        parent::addAttribute('descricao');
        parent::addAttribute('observacao');
    }
}