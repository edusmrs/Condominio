<?php

use Adianti\Database\TRecord;

class Evento extends TRecord
{
    const TABLENAME = 'evento';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('inicio');
        parent::addAttribute('fim');
        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('cor');        
        parent::addAttribute('system_user_id');
    }

}