<?php

use Adianti\Database\TRecord;

class Grupo extends TRecord
{
    const TABLENAME = 'grupo';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    //Metodo Construct
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }
}