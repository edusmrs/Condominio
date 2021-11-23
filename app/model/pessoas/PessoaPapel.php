<?php

use Adianti\Database\TRecord;

class PessoaPapel extends TRecord{
    const TABLENAME = 'pessoa_papel';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'max';

    const CREATEDAT = 'created_at';
    const UPDATEAT = 'updated_at';

    public function __construct($id = null, $callObjectLoad = true)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('grupo_id');
    }


}