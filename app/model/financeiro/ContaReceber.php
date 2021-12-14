<?php

use Adianti\Database\TRecord;

class ContaReceber extends TRecord
{
    const TABLENAME = 'conta_receber';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('documento');
        parent::addAttribute('conta_id');
        parent::addAttribute('data_vencimento');
        parent::addAttribute('pessoa_id');
        parent::addAttribute('valor');
        parent::addAttribute('data_recebimento');
        parent::addAttribute('valor_recebido');
        parent::addAttribute('juros_recebido');
        parent::addAttribute('status');
        parent::addAttribute('observacao');
    }

    public function get_conta()
    {
        return Conta::find($this->conta_id);
    }

    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }
    
}