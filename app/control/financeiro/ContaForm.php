<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Wrapper\BootstrapFormBuilder;

class ContaForm extends TPage
{
    protected $form;

    use \Adianti\Base\AdiantiStandardFormTrait;

    function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['ContaList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('db_condominio');
        $this->setActiveRecord('Conta');

        $this->form = new BootstrapFormBuilder('form_Conta');
        $this->form->setFormTitle('Contas');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $id = new TEntry('id');
        $categoria_conta = new TCombo('categoria_conta');
        $descricao = new TEntry('descricao');
        $observacao = new TEntry('uf');

        $categoria_conta->addItems(['D' => 'Despesa', 'R' => 'Receita']);

        $this->form->addFields([ new TLabel('Id')], [$id]);
        $this->form->addFields([ new TLabel('Categoria Contas')], [$categoria_conta]);
        $this->form->addFields([ new TLabel('Descrição')], [$descricao]);
        $this->form->addFields([ new TLabel('Observação')], [$observacao]);

        $categoria_conta->addValidation('Categoria Contas', new TRequiredValidator);
        $descricao->addValidation('Descrição', new TRequiredValidator);

        $id->setSize('100%');
        $categoria_conta->setSize('100%');
        $descricao->setSize('100%');
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

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}