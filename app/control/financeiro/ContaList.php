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
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class ContaList extends TPage
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
        $this->setActiveRecord('Conta');
        $this-> setDefaultOrder('id', 'asc');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');
        $this->addFilterField('categoria_conta', 'like', 'categoria_conta');
        $this->addFilterField('descricao', 'like', 'descricao');

        $this->form = new BootstrapFormBuilder('form_search_Conta');
        $this->form->setFormTitle('Conta');

        $id = new TEntry('id');
        $categoria_conta = new TCombo('categoria_conta');
        $descricao = new TEntry('descricao');

        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Categoria Conta') ], [ $categoria_conta]);
        $this->form->addFields([ new TLabel('Descrição') ], [ $descricao]);

        $categoria_conta->addItems( ['Despesa' => 'Despesa', 'Receita' => 'Receita'] );
        
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data_') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['ContaForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_categoria_conta = new TDataGridColumn('categoria_conta', 'Categoria Conta', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_categoria_conta);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_observacao);

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_categoria_conta->setAction(new TAction([$this, 'onReload']), ['order' => 'categoria_conta']);
        $column_descricao->setAction(new TAction([$this, 'onReload']), ['order' => 'descricao']);
        $column_observacao->setAction(new TAction([$this, 'onReload']), ['order' => 'observacao']);

        $action1 = new TDataGridAction(['ContaForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
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