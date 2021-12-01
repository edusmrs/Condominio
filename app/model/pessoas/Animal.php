<?php

use Adianti\Database\TRecord;

class Animal extends TRecord
{
    const TABLENAME = 'animal';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('observacao');
        parent::addAttribute('pessoa_id');
    }

    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }    
}