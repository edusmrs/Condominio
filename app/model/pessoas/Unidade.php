<?php

use Adianti\Database\TRecord;

class Unidade extends TRecord
{
    const TABLENAME = 'unidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('grupo_id');
        parent::addAttribute('bloco');
        parent::addAttribute('pessoa_id');
        parent::addAttribute('papel_id');
        parent::addAttribute('fracao');
        parent::addAttribute('area_util');
        parent::addAttribute('area_total');
        parent::addAttribute('observacao');
    }

    public function get_grupo()
    {
        return Grupo::find($this->grupo_id);
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