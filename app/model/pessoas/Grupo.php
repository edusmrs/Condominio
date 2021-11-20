<?php

use Adianti\Database\TRecord;

class Grupo extends TRecord
{
    const TABLENAME = 'Grupo';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    } 
}