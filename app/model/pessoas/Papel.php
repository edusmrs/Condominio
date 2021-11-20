<?php

use Adianti\Database\TRecord;

class Papel extends TRecord
{
    const TABLENAME = 'Papel';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    } 
}