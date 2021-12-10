<?php

use Adianti\Database\TRecord;

class ContaPagar extends TRecord
{
    const TABLENAME = 'conta_pagar';
    const PRIMARYKEY= 'id';
    const IDPOLICY  = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('conta_id');
        parent::addAttribute('rateio');
        parent::addAttribute('valor');
        parent::addAttribute('data_vencimento');
        parent::addAttribute('data_pagamento');
        parent::addAttribute('valor_pago');
        parent::addAttribute('observacao');
        parent::addAttribute('pessoa_id');
        parent::addAttribute('saldo');
        parent::addAttribute('status');
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