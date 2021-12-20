<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapFormBuilder;

class ContaReceberForm extends TPage
{
    protected $form;

    use \Adianti\Base\AdiantiStandardFormTrait;

    function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['ContaReceberList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('db_condominio');
        $this->setActiveRecord('ContaReceber');

        $this->form = new BootstrapFormBuilder('form_ContaReceber');
        $this->form->setFormTitle('Contas a Receber');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $id = new TEntry('id');
        $documento = new TEntry('documento');     
        $conta_id = new TDBUniqueSearch('conta_id', 'db_condominio', 'Conta', 'id', 'descricao');
        $conta_id->setMinLength(0);
        $conta_id->setMask('{descricao}');        
        $data_vencimento = new TDate('data_vencimento');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $valor = new TEntry('valor');        
        $data_recebimento = new TDate('data_recebimento');
        $valor_recebido = new TEntry('valor_recebido');
        $juros_recebido = new TEntry('juros_recebido');
        $status = new TCombo('status');
        $status->addItems(['Liquidado' => 'Liquidado', 'Pendente' => 'Pendente', 'Parcelado' => 'Parcelado']);
        $observacao = new TEntry('observacao');
        
        // Chama o método onJuroRecebido
        $valor_recebido->setExitAction(new TAction(array($this, 'onJuroRecebido')));

        $this->form->addFields([ new TLabel('Id')], [$id]);
        $this->form->addFields([ new TLabel('Documento')], [$documento]);
        $this->form->addFields([ new TLabel('Conta')], [$conta_id]);
        $this->form->addFields([ new TLabel('Data Vencimento')], [$data_vencimento]);
        $this->form->addFields([ new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([ new TLabel('Valor')], [$valor]);        
        $this->form->addFields([ new TLabel('Data Recebimento')], [$data_recebimento]);
        $this->form->addFields([ new TLabel('Valor Recebido')], [$valor_recebido]);
        $this->form->addFields([ new TLabel('Juros Recebido')], [$juros_recebido]);
        $this->form->addFields([ new TLabel('Status')], [$status]);
        $this->form->addFields([ new TLabel('Observação')], [$observacao]);
        
        // set exit action for input_exit
        $exit_action = new TAction(array($this, 'onExitAction'));
        $valor->setExitAction($exit_action);
        $data_recebimento->setExitAction($exit_action);

        $data_vencimento->setMask('dd/mm/yyyy');
        $data_vencimento->setDatabaseMask('yyyy-mm-dd');
        $data_recebimento->setMask('dd/mm/yyyy');
        $data_recebimento->setDatabaseMask('yyyy-mm-dd');

        $valor->setNumericMask(2, ',', '.', true);
        //$valor_recebido->setNumericMask(2, ',', '.', true);
        //$juros_recebido->setNumericMask(2, ',', '.', true);

        $valor_recebido->setEditable(FALSE);
        $juros_recebido->setEditable(FALSE);

        $pessoa_id->addValidation('Pessoa', new TRequiredValidator);
        $conta_id->addValidation('Conta', new TRequiredValidator);
        $valor->addValidation('Valor', new TRequiredValidator);
        $data_vencimento->addValidation('Data Vencimento', new TRequiredValidator);
        
        $id->setSize('100%');
        $documento->setSize('100%');
        $conta_id->setSize('100%');
        $data_vencimento->setSize('100%');
        $pessoa_id->setSize('100%');
        $valor->setSize('100%');        
        $data_recebimento->setSize('100%');
        $valor_recebido->setSize('100%');
        $juros_recebido->setSize('100%');
        $status->setSize('100%'); 
        $observacao->setSize('100%');

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

    public static function onJuroRecebido($param)
    { 
        $valor = (double) str_replace(['.', ','], ['', '.'], $param['valor']);
        $data_vencimento = $param['data_vencimento'];
        $data_recebimento = $param['data_recebimento'];
        
        $object = new StdClass;
       
        if ($data_recebimento > $data_vencimento)
        {        
        $multa = 2/100;
        $data_recebimento = new DateTime(TDate::date2us($data_recebimento));
        $data_vencimento = new DateTime(TDate::date2us($data_vencimento));
        $tempo = $data_recebimento->diff($data_vencimento);
        $meses = $tempo->y * 12 + $tempo->m;
        
        $object->juros_recebido  = ($valor * pow(1+1/100, $meses) - $valor)+($valor * $multa);
        $object->juros_recebido  = number_format($object->juros_recebido, 2, '.', '');        
        $object->valor_recebido = $valor + $object->juros_recebido;
        $object->valor_recebido = number_format($object->valor_recebido, 2, '.', '');
        }
        else
        {  
            $object->valor_recebido = $valor;
            $object->juros_recebido  = 0.00;
        }

        TForm::sendData('form_ContaReceber', $object);
    }
    
    public static function onExitAction($param)
    {
        $obj = new StdClass;
        $obj->valor_recebido = $param['valor_recebido'];
        $obj->juros_recebido  = $param['juros_recebido'];
             
        TForm::sendData('form_ContaReceber', $obj);
    }

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}