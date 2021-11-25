<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Wrapper\BootstrapFormWrapper;

class PessoaList extends TPage{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;

    use \Adianti\Base\AdiantiStandardListTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('db_condominio');
        $this->setActiveRecord('Pessoa');
        $this->setDefaultOrder('id','asc');
        $this->setLimit(10);

        $this->addFilterField('id','=','id');
        $this->addFilterField('nome_fantasia','like','nome_fantasia');
        $this->addFilterField('fone','like','fone');
        $this->addFilterField('email','like','email');
        $this->addFilterField('grupo_id','=','grupo_id');

        //Criar os forms
        $this->form = new BootstrapFormBuilder('form_search_Pessoa');
        $this->form->setFormTitle('Pessoa');

        $id = new TEntry('id');
        $nome_fantasia = new TEntry('nome_fantasia');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $grupo_id = new TDBUniqueSearch('grupo_id','db_condominio','Grupo','id','nome');
        $grupo_id->setMinLength(0);

        $this->form->addFields([new TLabel('id')],[$id]);
        $this->form->addFields([new TLabel('Nome Fantasia')],[$nome_fantasia]);
        $this->form->addFields([new TLabel('Fone')],[$fone]);
        $this->form->addFields([new TLabel('Email')],[$email]);
        $this->form->addFields([new TLabel('Grupo')],[$grupo_id]);

        $id->setSize('100%');
        $nome_fantasia->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $grupo_id->setSize('100%');

        $this->form->setData(TSession::getValue(__CLASS__.'_filter_data'));

        $btn = $this->form->addAction(_t('Find'), new TAction([$this,'onSearch']),'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),new TAction(['PessoaForm', 'onEdit']), 'fa:plus green');

        //Criar o Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        //Criar columas no datagrid
        $column_id = new TDataGridColumn('id','Id','left');
        $column_nome_fantasia = new TDataGridColumn('nome_fantasia','Nome Fantasia','left');
        $column_fone = new TDataGridColumn('fone','Fone','left');
        $column_email = new TDataGridColumn('email','Email','left');
        $column_grupo_id = new TDataGridColumn('grupo_id','Grupo','left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome_fantasia);
        $this->datagrid->addColumn($column_fone);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_grupo_id);

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome_fantasia->setAction(new TAction([$this, 'onReload']), ['order' => 'nome_fanatasia']);
        
        $action1 = new TDataGridAction(['pessoaFormView', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['pessoaForm', 'onEdit'], ['id' => '{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}', 'register_state' => 'false']);
        
        
        $this->datagrid->addAction($action1, _t('View'), 'fa:search grey');
        $this->datagrid->addAction($action2, _t('Edit'), 'far:edit blue');
        $this->datagrid->addAction($action3, _t('Delete'), 'far:trash-alt red');
        

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $panel = new TPanelGroup('','white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('bts btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['regular_state' => 'false', 'static' => '1']), 'fa:table blue');
        $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['regular_state' => 'false', 'static' => '1']), 'fa:file-pdf red');
        $panel->addHeaderWidget($dropdown);

        $container = new TVBox;
        $container->style = "whidth: 100%";
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }

}