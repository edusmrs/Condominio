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

class UnidadeList extends TPage
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
        $this->setActiveRecord('Unidade');
        $this-> setDefaultOrder('id', 'asc');
        $this->setOrderCommand('pessoa->nome', '(SELECT nome FROM pessoa WHERE id=unidade.pessoa_id)');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');
        $this->addFilterField('descricao', 'like', 'descricao');
        $this->addFilterField('grupo_id', '=', 'grupo_id');        
        $this->addFilterField('pessoa_id', '=', 'pessoa_id');
        $this->addFilterField('papel_id', '=', 'papel_id');

        $this->form = new BootstrapFormBuilder('form_search_Unidade');
        $this->form->setFormTitle('Unidade');

        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $grupo_id = new TDBUniqueSearch('grupo_id', 'db_condominio', 'Grupo', 'id', 'nome');
        $grupo_id->setMinLength(0);
        $grupo_id->setMask('{nome}');
        $bloco = new TEntry('bloco');        
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');        
        $papel_id = new TDBUniqueSearch('papel_id', 'db_condominio', 'Papel', 'id', 'nome');
        $papel_id->setMinLength(0);
        $papel_id->setMask('{nome}');
        //$fracao = new TEntry('fracao');
        //$area_util = new TEntry('area_util');
        //$area_total = new TEntry('area_total');
        //$observacao = new TEntry('observacao');        
        

        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Descrição') ], [ $descricao]);
        $this->form->addFields([ new TLabel('Grupo') ], [ $grupo_id]);
        $this->form->addFields([ new TLabel('Bloco') ], [ $bloco]);
        $this->form->addFields([ new TLabel('Pessoa') ], [ $pessoa_id]);
        $this->form->addFields([ new TLabel('Papel') ], [ $papel_id]);
        //$this->form->addFields([ new TLabel('Fração') ], [ $fracao]);
        //$this->form->addFields([ new TLabel('Área Útil') ], [ $area_util]);
        //$this->form->addFields([ new TLabel('Área total') ], [ $area_total]);
        //$this->form->addFields([ new TLabel('Observação') ], [ $observacao]);

        $id->setSize('30%');
        $descricao->setSize('100%');
        $grupo_id->setSize('100%');
        $bloco->setSize('100%');
        $pessoa_id->setSize('100%');
        $papel_id->setSize('100%');
        //$fracao->setSize('100%');
        //$area_util->setSize('100%');
        //$area_total->setSize('100%');
        //$observacao->setSize('100%');

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['UnidadeForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_grupo_id = new TDataGridColumn('{grupo->nome}', 'Grupo', 'left');
        $column_bloco = new TDataGridColumn('bloco', 'Bloco', 'left');
        $column_pessoa_id = new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left');
        $column_papel_id = new TDataGridColumn('{papel->nome}', 'Papel', 'left');
        $column_fracao = new TDataGridColumn('fracao', 'Fração', 'left');
        $column_area_util = new TDataGridColumn('area_util', 'Área Útil', 'left');
        $column_area_total = new TDataGridColumn('area_total', 'Área Total', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');

        $column_papel_id->enableAutoHide(500);
        $column_pessoa_id->enableAutoHide(500);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_grupo_id);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_papel_id);
        $this->datagrid->addColumn($column_fracao);        
        $this->datagrid->addColumn($column_area_util);
        $this->datagrid->addColumn($column_area_total);
        $this->datagrid->addColumn($column_observacao);

        //Formata valor na datagrid
        $format_value = function($value) {
            if (is_numeric($value)) {
                return number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_area_util->setTransformer( $format_value );
        $column_area_total->setTransformer( $format_value );

        $format_value_fracao = function($value_fracao) {
            if (is_numeric($value_fracao)) {
                return number_format($value_fracao, 8, ',', '.');
            }
            return $value_fracao;
        };
        $column_fracao->setTransformer( $format_value_fracao );

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa->nome']);
        $column_papel_id->setAction(new TAction([$this, 'onReload']), ['order' => 'papel->nome']);

        $action1 = new TDataGridAction(['UnidadeForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
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