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
        $this->setDefaultOrder('id', 'asc');
        $this->setOrderCommand('pessoa->nome','(SELECT nome FROM pessoa WHERE id=veiculo.pessoa_id');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');
        $this->addFilterField('placa', 'like', 'placa');
        $this->addFilterField('marca', 'like', 'marca');
        $this->addFilterField('modelo', 'like', 'modelo');
        $this->addFilterField('cor', 'like', 'cor');
        $this->addFilterField('ano_modelo', 'like', 'ano_modelo');
        $this->addFilterField('pessoa_id', 'like', 'pessoa_id');

        $this->form = new BootstrapFormBuilder('form_search_Veiculo');
        $this->form->setFormTitle('Veiculos');

        $id = new TEntry('id');
        $placa = new TEntry('placa');
        $marca = new TEntry('marca');
        $modelo = new TEntry('modelo');
        $cor = new TEntry('cor');
        $ano_modelo = new TEntry('ano_modelo');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        
        $placa->setMinLength(7);
        $placa->setMask('AAAAAAA');


        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Placa') ], [ $placa]);
        $this->form->addFields([ new TLabel('Marca') ], [$marca]);
        $this->form->addFields([ new TLabel('Modelo') ], [$modelo]);
        $this->form->addFields([ new TLabel('Cor') ], [$cor]);
        $this->form->addFields([ new TLabel('Ano Modelo') ], [$ano_modelo]);
        $this->form->addFields([ new TLabel('Nome') ], [$pessoa_id]);

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data_') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['VeiculoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';
        //$this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', '<b>{nome}<br>{estado->nome}</b>');

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_placa = new TDataGridColumn('placa', 'Placa', 'left');
        $column_marca = new TDataGridColumn('marca', 'Marca', 'center', '10%');
        $column_modelo = new TDataGridColumn('modelo', 'Modelo', 'center', '10%');
        $column_cor = new TDataGridColumn('cor', 'Cor', 'center', '10%');
        $column_ano_modelo = new TDataGridColumn('ano_modelo', 'Ano Modelo', 'center', '10%');
        $column_pessoa_id = new TDataGridColumn('{pessoa->nome}', 'Nome', 'center', '10%');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_placa);
        $this->datagrid->addColumn($column_marca);
        $this->datagrid->addColumn($column_modelo);
        $this->datagrid->addColumn($column_cor);
        $this->datagrid->addColumn($column_ano_modelo);
        $this->datagrid->addColumn($column_pessoa_id);
        

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_placa->setAction(new TAction([$this, 'onReload']), ['order' => 'placa']);
        $column_marca->setAction(new TAction([$this, 'onReload']), ['order' => 'marca']);
        $column_modelo->setAction(new TAction([$this, 'onReload']), ['order' => 'modelo']);
        $column_cor->setAction(new TAction([$this, 'onReload']), ['order' => 'cor']);
        $column_ano_modelo->setAction(new TAction([$this, 'onReload']), ['order' => 'ano_modelo']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa_id']);

        $action1 = new TDataGridAction(['VeiculoForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

        $this->datagrid->addAction($action1, _t('Edit'), 'fa:edit blue');
        $this->datagrid->addAction($action2, _t('Delete'), 'fa:trash red');

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