<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class CidadeList extends TPage
{
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
        $this->setActiveRecord('Cidade');
        $this-> setDefaultOrder('id', 'asc');
        $this->setOrderCommand('estado->nome', '(SELECT nome FROM estado WHERE id=cidade.estado_id)');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');        
        $this->addFilterField('nome', 'like', 'nome');
        $this->addFilterField('codigo_ibge', '=', 'codigo_ibge');

        $this->form = new BootstrapFormBuilder('form_search_Cidade');
        $this->form->setFormTitle('Cidade');

        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $codigo_ibge = new TEntry('codigo_ibge');
        $estado_id = new TDBUniqueSearch('estado_id', 'db_condominio', 'Estado', 'id', 'uf');
        $estado_id->setMinLength(0);
        $estado_id->setMask('{nome} ({uf})');        

        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Nome') ], [ $nome]);
        $this->form->addFields([ new TLabel('Código IBGE') ], [ $codigo_ibge]);
        $this->form->addFields([ new TLabel('Estado') ], [ $estado_id]);

        $id->setSize('100%');
        $nome->setSize('100%');
        $codigo_ibge->setSize('100%');
        $estado_id->setSize('100%');

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data_') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['CidadeForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';
        //$this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', '<b>Nome: {nome}<br>Estado: {estado->nome}<br>UF: {estado->uf}</b>');

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_codigo_ibge = new TDataGridColumn('codigo_ibge', 'Código IBGE', 'center', '10%');
        $column_estado_id = new TDataGridColumn('{estado->nome} ({estado->uf})', 'Estado', 'center', '10%');

        $column_codigo_ibge->enableAutoHide(500);
        $column_estado_id->enableAutoHide(500);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_codigo_ibge);
        $this->datagrid->addColumn($column_estado_id);

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_codigo_ibge->setAction(new TAction([$this, 'onReload']), ['order' => 'codigo_ibge']);
        $column_estado_id->setAction(new TAction([$this, 'onReload']), ['order' => 'estado->nome']);

        $action1 = new TDataGridAction(['CidadeForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

        $this->datagrid->addAction($action1, _t('Edit'), 'fa:edit blue');
        $this->datagrid->addAction($action2, _t('Delete'), 'fa:trash red');

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table blue');
        $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf red');
        $panel->addHeaderWidget($dropdown);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }
}