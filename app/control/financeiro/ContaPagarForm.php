<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

class ContaPagarForm extends TPage
{
    protected $form;

    use \Adianti\Base\AdiantiStandardFormTrait;

    function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['ContaPagarList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('db_condominio');
        $this->setActiveRecord('ContaPagar');

        $this->form = new BootstrapFormBuilder('form_Conta_Pagar');
        $this->form->setFormTitle('Conta a Pagar');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $id = new TEntry('id');
        $conta_id = new TDBUniqueSearch('conta_id', 'db_condominio', 'Conta', 'id', 'descricao');
        $conta_id->setMinLength(0);
        $conta_id->setMask('{descricao}');
        $rateio = new TCombo('rateio');
        $rateio->addItems(['fracao'=>'fracao','valor' => 'valor']);
        $valor = new TEntry('valor');
        $data_vencimento = new TDate('data_vencimento');
        $data_pagamento = new TDate('data_pagamento');
        $valor_pago = new TEntry('valor_pago');
        $observacao = new TEntry('observacao');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $pessoa_id->setMinLength(0);
        $saldo = new TEntry('saldo');
        $status = new TEntry('status');


        $this->form->addFields([ new TLabel('Id')], [$id]);
        $this->form->addFields([ new TLabel('Conta')], [$conta_id]);
        $this->form->addFields([ new TLabel('Rateio')], [$rateio]);
        $this->form->addFields([ new TLabel('Valor')], [$valor]);
        $this->form->addFields([ new TLabel('Data Vencimento')], [$data_vencimento]);
        $this->form->addFields([ new TLabel('Data Pagamento')], [$data_pagamento]);
        $this->form->addFields([ new TLabel('Valor Pago')], [$valor_pago]);
        $this->form->addFields([ new TLabel('Observação')], [$observacao]);
        $this->form->addFields([ new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([ new TLabel('Saldo')], [$saldo]);
        $this->form->addFields([ new TLabel('Status')], [$status]);

        $data_vencimento->setMask('dd/mm/yyyy');
        $data_vencimento->setDatabaseMask('yyyy-mm-dd');

        $data_pagamento->setMask('dd/mm/yyyy');
        $data_pagamento->setDatabaseMask('yyyy-mm-dd');

        $valor->setNumericMask(2, ',', '.' ,true);
        $valor_pago->setNumericMask(2, ',', '.' ,true);
        $saldo->setNumericMask(2, ',', '.' ,true);
        

        $pessoa_id->addValidation('Pessoa', new TRequiredValidator);
        $conta_id->addValidation('Conta', new TRequiredValidator);
        $valor->addValidation('Valor', new TRequiredValidator);
        $data_vencimento->addValidation('Data Vencimento', new TRequiredValidator);

        $id->setSize('100%');
        $conta_id->setSize('100%');
        $rateio->setSize('100%');
        $valor->setSize('100%');
        $data_vencimento->setSize('100%');
        $data_pagamento->setSize('100%');
        $valor_pago->setSize('100%');
        $observacao->setSize('100%');
        $pessoa_id->setSize('100%');
        $saldo->setSize('100%');
        $status->setSize('100%');

        $id->setEditable(FALSE);
        

        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save' );
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');

        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);

        parent::add($container);


        
    }


    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    
}