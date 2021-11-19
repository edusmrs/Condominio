<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

class CidadeForm extends TPage
{
    protected $form;

    use \Adianti\Base\AdiantiStandardFormTrait;

    function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['CidadeList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('db_condominio');
        $this->setActiveRecord('Cidade');

        $this->form = new BootstrapFormBuilder('form_Cidade');
        $this->form->setFormTitle('Cidades');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $codigo_ibge = new TEntry('codigo_ibge');
        $estado_id = new TDBUniqueSearch('estado_id', 'db_condominio', 'Estado', 'id', 'uf');
        $estado_id->setMinLength(0);
        $estado_id->setMask('{nome} ({uf})');

        $this->form->addFields([ new TLabel('Id')], [$id]);
        $this->form->addFields([ new TLabel('Nome')], [$nome]);
        $this->form->addFields([ new TLabel('Código IBGE')], [$codigo_ibge]);
        $this->form->addFields([ new TLabel('Estado Id')], [$estado_id]);
        

        $codigo_ibge->addValidation('Codigo_ibge', new TRequiredValidator);
        $estado_id->addValidation('Estado_id', new TRequiredValidator);
        $nome->addValidation('Nome', new TRequiredValidator);

        $id->setSize('100%');
        $nome->setSize('100%');
        $codigo_ibge->setSize('100%');
        $estado_id->setSize('100%');

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