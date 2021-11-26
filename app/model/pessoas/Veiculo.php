<?php

use Adianti\Database\TRecord;

class Veiculo extends TRecord
{
    const TABLENAME = 'veiculo';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('placa');
        parent::addAttribute('marca');
        parent::addAttribute('modelo');
        parent::addAttribute('cor');
        parent::addAttribute('ano_modelo');
        parent::addAttribute('pessoa_id');
    }

    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }    
}