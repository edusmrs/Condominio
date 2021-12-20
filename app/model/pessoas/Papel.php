<?php

use Adianti\Database\TRecord;

class Papel extends TRecord
{
    const TABLENAME = 'papel';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    //Metodo Construct
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }
}