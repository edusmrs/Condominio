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
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class EleicaoList extends TPage
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
        $this->setActiveRecord('Eleicao');
        $this->setDefaultOrder('id', 'asc');
        $this->setOrderCommand('pessoa->nome','(SELECT nome FROM pessoa WHERE id=eleicao.pessoa_id');
        $this->setOrderCommand('papel->nome','(SELECT nome FROM papel WHERE id=eleicao.papel_id');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');
        $this->addFilterField('pessoa_id', 'like', 'pessoa_id');
        $this->addFilterField('papel_id', 'like', 'papel_id');
        /*$this->addFilterField('data_inicio', 'like', 'data_inicio', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction */
        
        /* $this->addFilterField('data_fim', 'like', 'data_fim', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); */

        $this->addFilterField('data_inicio', '=', 'data_inicio');
        $this->addFilterField('data_fim', '=', 'data_fim');
        $this->addFilterField('observacao', 'like', 'observacao');

        $this->form = new BootstrapFormBuilder('form_search_Eleicao');
        $this->form->setFormTitle('Eleicao');

        $id = new TEntry('id');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $papel_id = new TDBUniqueSearch('papel_id', 'db_condominio', 'Papel', 'id', 'nome');
        $data_inicio = new TDate('data_inicio');
        $data_fim = new TDate('data_fim');
        $observacao = new TEntry('observacao');
        

        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Pessoa') ], [$pessoa_id]);
        $this->form->addFields([ new TLabel('Papel') ], [ $papel_id]);
        $this->form->addFields([ new TLabel('Início Mandato') ], [$data_inicio]);
        $this->form->addFields([ new TLabel('Término Mandato') ], [$data_fim]);
        $this->form->addFields([ new TLabel('Observação') ], [$observacao]);

        $data_inicio->setMask('dd/mm/yyyy');
        $data_inicio->setDatabaseMask('yyyy-mm-dd');
        $data_fim->setMask('dd/mm/yyyy');
        $data_fim->setDatabaseMask('yyyy-mm-dd');

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['EleicaoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';
        //$this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', '<b>{nome}<br>{estado->nome}</b>');

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'left', '10%');
        $column_pessoa_id = new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left');
        $column_papel_id = new TDataGridColumn('{papel->nome}', 'Papel', 'left');
        $column_data_inicio = new TDataGridColumn('data_inicio', 'Início Mandato', 'left');
        $column_data_fim = new TDataGridColumn('data_fim', 'Término Mandato', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_papel_id);
        $this->datagrid->addColumn($column_data_inicio);
        $this->datagrid->addColumn($column_data_fim);
        $this->datagrid->addColumn($column_observacao);
        

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa_id']);
        $column_papel_id->setAction(new TAction([$this, 'onReload']), ['order' => 'papel_id']);
        $column_data_inicio->setAction(new TAction([$this, 'onReload']), ['order' => 'data_inicio']);
        $column_data_fim->setAction(new TAction([$this, 'onReload']), ['order' => 'data_fim']);
        $column_observacao->setAction(new TAction([$this, 'onReload']), ['order' => 'observacao']);

        $column_data_inicio->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $column_data_fim->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $action1 = new TDataGridAction(['EleicaoForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
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