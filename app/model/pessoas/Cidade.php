<?php

use Adianti\Database\TRecord;

class Cidade extends TRecord
{
    const TABLENAME = 'cidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('codigo_ibge');
        parent::addAttribute('estado_id');
    }

    public function get_estado()
    {
        return Estado::find($this->estado_id);
    }    
}