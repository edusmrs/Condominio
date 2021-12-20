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

class EleicaoForm extends TPage
{
    protected $form;

    use \Adianti\Base\AdiantiStandardFormTrait;

    function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['EleicaoList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('db_condominio');
        $this->setActiveRecord('Eleicao');

        $this->form = new BootstrapFormBuilder('form_Eleicao');
        $this->form->setFormTitle('Eleicao');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses(2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8']);

        $id = new TEntry('id');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $papel_id = new TDBUniqueSearch('papel_id', 'db_condominio', 'Papel', 'id', 'nome');
        $data_inicio = new TDate('data_inicio');
        $data_fim = new TDate('data_fim');
        $observacao = new TEntry('observacao');

        $this->form->addFields([ new TLabel('Id')], [$id]);
        $this->form->addFields([ new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([ new TLabel('Papel')], [$papel_id]);
        $this->form->addFields([ new TLabel('Data Ínicio')], [$data_inicio]);
        $this->form->addFields([ new TLabel('Data Fim')], [$data_fim]);
        $this->form->addFields([ new TLabel('Observação')], [$observacao]);

        $pessoa_id->addValidation('Pessoa', new TRequiredValidator);
        $papel_id->addValidation('Papel', new TRequiredValidator);
        $data_inicio->addValidation('Papel', new TRequiredValidator);
        $data_fim->addValidation('Papel', new TRequiredValidator);

        $data_inicio->setMask('dd/mm/yyyy');
        $data_inicio->setDatabaseMask('yyyy-mm-dd');
        $data_fim->setMask('dd/mm/yyyy');
        $data_fim->setDatabaseMask('yyyy-mm-dd');
        
        $id->setSize('100%');
        $pessoa_id->setSize('100%');
        $papel_id->setSize('100%');
        $data_inicio->setSize('100%');
        $data_fim->setSize('100%');
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