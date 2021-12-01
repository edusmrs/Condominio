<?php

use Adianti\Database\TRecord;

class Eleicao extends TRecord
{
    const TABLENAME = 'eleicao';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('papel_id');
        parent::addAttribute('data_inicio');
        parent::addAttribute('data_fim');
        parent::addAttribute('observacao');
    }

    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }
    public function get_papel()
    {
        return Papel::find($this->papel_id);
    }       
}