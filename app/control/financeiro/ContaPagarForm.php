<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
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

        $this->form = new BootstrapFormBuilder('form_ContaPagar');
        $this->form->setFormTitle('Contas a Pagar');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $id = new TEntry('id');      
        $conta_id = new TDBUniqueSearch('conta_id', 'db_condominio', 'Conta', 'id', 'descricao');
        $conta_id->setMinLength(0);
        $conta_id->setMask('{descricao}');        
        $rateio = new TCombo('rateio');
        $rateio->addItems(['fracao' => 'fracao', 'valor' => 'valor']);
        $valor = new TEntry('valor');
        $data_vencimento = new TDate('data_vencimento');
        $data_pagamento = new TDate('data_pagamento');
        $valor_pago = new TEntry('valor_pago');
        $observacao = new TEntry('observacao');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $saldo = new TEntry('saldo');
        $status = new TCombo('status');
        $status->addItems(['Liquidado' => 'Liquidado', 'Parcelado' => 'Parcelado', 'Pendente' => 'Pendente']);
        
        // Chama o método onSaldo
        $saldo->setExitAction(new TAction(array($this, 'onSaldo')));

        $this->form->addFields([ new TLabel('Id')], [$id]);
        $this->form->addFields([ new TLabel('Conta')], [$conta_id]);
        $this->form->addFields([ new TLabel('Rateio')], [$rateio]);
        $this->form->addFields([ new TLabel('Valor')], [$valor]);
        $this->form->addFields([ new TLabel('Data Vencimento')], [$data_vencimento]);
        $this->form->addFields([ new TLabel('Data Pagamento')], [$data_pagamento]);
        $this->form->addFields([ new TLabel('Valor Pago')], [$valor_pago]);
        $this->form->addFields([ new TLabel('Observação')], [$observacao]);
        $this->form->addFields([ new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([ new TLabel('Juros')], [$saldo]);
        $this->form->addFields([ new TLabel('Status')], [$status]);

        // set exit action for input_exit
        $exit_action = new TAction(array($this, 'onExitAction'));
        //$valor->setExitAction($exit_action);
        $data_pagamento->setExitAction($exit_action);

        $data_vencimento->setMask('dd/mm/yyyy');
        $data_vencimento->setDatabaseMask('yyyy-mm-dd');
        $data_pagamento->setMask('dd/mm/yyyy');
        $data_pagamento->setDatabaseMask('yyyy-mm-dd');

        $saldo->setEditable(FALSE);
        $valor_pago->setEditable(FALSE);

        $valor->setNumericMask(2, ',', '.', true);
        //$valor_pago->setNumericMask(2, ',', '.', true);
        //$saldo->setNumericMask(2, ',', '.', true);

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

    public static function onSaldo($param)
    {   
        $valor = (double) str_replace(['.', ','], ['', '.'], $param['valor']);
        $data_vencimento = $param['data_vencimento'];
        $data_pagamento = $param['data_pagamento'];
        
        $object = new StdClass;
        
        if ($data_pagamento > $data_vencimento)
        {
        
        $multa = 2/100;
        $data_pagamento = new DateTime(TDate::date2us($data_pagamento));
        $data_vencimento = new DateTime(TDate::date2us($data_vencimento));
        $tempo = $data_pagamento->diff($data_vencimento);
        $meses = $tempo->y * 12 + $tempo->m;
        
        $object->saldo = ($valor * pow(1+1/100, $meses) - $valor)+($valor * $multa);
        $object->saldo = number_format($object->saldo, 2, '.', '');        
        $object->valor_pago = $valor + $object->saldo;
        $object->valor_pago = number_format($object->valor_pago, 2, '.', '');
        }
        else
        {   
            $object->valor_pago = $object->valor;
            $object->saldo = 0.00;
        }

        TForm::sendData('form_ContaPagar', $object);
    }
    
    public static function onExitAction($param)
    {
        $obj = new StdClass;
        $obj->valor_pago = $param['valor_pago'];
        $obj->saldo      = $param['saldo'];
             
        TForm::sendData('form_ContaPagar', $obj);
    }

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}