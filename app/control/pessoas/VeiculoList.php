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

class VeiculoList extends TPage
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
        $this->setActiveRecord('Veiculo');
        $this-> setDefaultOrder('id', 'asc');
        $this->setOrderCommand('pessoa->nome', '(SELECT nome FROM pessoa WHERE id=veiculo.pessoa_id)');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');        
        $this->addFilterField('placa', '=', 'placa');
        $this->addFilterField('marca', 'like', 'marca');

        $this->form = new BootstrapFormBuilder('form_search_Veiculo');
        $this->form->setFormTitle('Veículos');

        $id = new TEntry('id');
        $placa = new TEntry('placa');
        $marca = new TEntry('marca');
        $modelo = new TEntry('modelo');
        $cor = new TEntry('cor');
        $ano_modelo = new TEntry('ano_modelo');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $pessoa_id->setMinLength(0);
        //$pessoa_id->setMask('nome'); 
        $placa->forceUpperCase();
        $marca->forceUpperCase();      

        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Placa') ], [ $placa]);
        $this->form->addFields([ new TLabel('Marca') ], [ $marca]);
        $this->form->addFields([ new TLabel('Modelo') ], [ $modelo]);
        $this->form->addFields([ new TLabel('cor') ], [ $cor]);
        $this->form->addFields([ new TLabel('Ano Modelo') ], [ $ano_modelo]);
        $this->form->addFields([ new TLabel('Pessoa') ], [ $pessoa_id]);

        $id->setSize('100%');
        $placa->setSize('100%');
        $marca->setSize('100%');
        $modelo->setSize('100%');
        $cor->setSize('100%');
        $ano_modelo->setSize('100%');
        $pessoa_id->setSize('100%');

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['VeiculoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';
        //$this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', '<b>Nome: {nome}<br>Estado: {estado->nome}<br>UF: {estado->uf}</b>');

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_placa = new TDataGridColumn('placa', 'Placa', 'left');
        $column_marca = new TDataGridColumn('marca', 'Marca', 'left');
        $column_pessoa_id = new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left');

        $column_placa->enableAutoHide(500);
        $column_pessoa_id->enableAutoHide(500);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_placa);
        $this->datagrid->addColumn($column_marca);
        $this->datagrid->addColumn($column_pessoa_id);

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_placa->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_marca->setAction(new TAction([$this, 'onReload']), ['order' => 'codigo_ibge']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'estado->nome']);

        $action1 = new TDataGridAction(['VeiculoForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
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