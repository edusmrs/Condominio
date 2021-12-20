<?php

use Adianti\Database\TRecord;

class PessoaPapel extends TRecord
{
    const TABLENAME = 'pessoa_papel';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'max';
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('papel_id');
    }
}