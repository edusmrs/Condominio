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

class ContaReceberList extends TPage
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
        $this->setActiveRecord('ContaReceber');
        $this->setDefaultOrder('id', 'asc');
        $this->setOrderCommand('pessoa->nome','(SELECT nome FROM pessoa WHERE id=conta_receber.pessoa_id');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');
        $this->addFilterField('conta_id', '=', 'conta_id');
        $this->addFilterField('pessoa_id', 'like', 'pessoa_id');
        $this->addFilterField('documento', 'like', 'documento');
        $this->addFilterField('status', 'like', 'status');
        
        

        $this->form = new BootstrapFormBuilder('form_search_ContaReceber');
        $this->form->setFormTitle('Contas a Receber');

        $id = new TEntry('id');
        $conta_id = new TDBUniqueSearch('conta_id', 'db_condominio', 'Conta', 'id', 'descricao');
        $conta_id->setMinLength(0);
        $conta_id->setMask('{descricao}');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $status = new TCombo('status');
        $status->addItems(['Liquidado'=>'Liquidado','Pendente' => 'Pendente','Parcelado' => 'Parcelado']);
        $documento = new TEntry('documento');
        

        $this->form->addFields([ new TLabel('Id') ], [ $id]);
        $this->form->addFields([ new TLabel('Conta') ], [$conta_id]);
        $this->form->addFields([ new TLabel('Pessoa') ], [$pessoa_id]);
        $this->form->addFields([ new TLabel('Status') ], [ $status]);
        $this->form->addFields([ new TLabel('Documento') ], [ $documento]);

        $id->setSize('30%');
        $conta_id->setSize('100%');
        $pessoa_id->setSize('100%');
        $status->setSize('100%');
        $documento->setSize('100%');

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn = $this->form->addAction(_t('Find'), new TAction([ $this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['ContaReceberForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';
        //$this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', '<b>{nome}<br>{estado->nome}</b>');

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'left', '10%');
        $column_documento = new TDataGridColumn('documento', 'Documento', 'left');
        $column_conta_id = new TDataGridColumn('{conta->descricao}', 'Conta', 'left');
        $column_data_vencimento = new TDataGridColumn('data_vencimento', 'Data Vencimento', 'left');
        $column_pessoa_id = new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_data_recebimento = new TDataGridColumn('data_recebimento', 'Data Recebimento', 'left');
        $column_valor_recebido = new TDataGridColumn('valor_recebido', 'Valor Recebido', 'left');
        $column_juros_recebido = new TDataGridColumn('juros_recebido', 'Juros Recebido', 'left');
        $column_status = new TDataGridColumn('status', 'Status', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_documento);
        $this->datagrid->addColumn($column_conta_id);
        $this->datagrid->addColumn($column_data_vencimento);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_data_recebimento);
        $this->datagrid->addColumn($column_valor_recebido);
        $this->datagrid->addColumn($column_juros_recebido);
        $this->datagrid->addColumn($column_observacao);
        $this->datagrid->addColumn($column_status);
        
        $column_status->setTransformer(function($value, $object, $row) { 
            $lbl = new TLabel(''); 
            if ($value == 'Liquidado') { 
                $lbl->setValue('Liquidado'); 
                $lbl->class = 'label label-success'; 
            } 
            elseif ($value == 'Pendente') { 
                $lbl->setValue('Pendente'); 
                $lbl->class = 'label label-danger'; 
            }  
            elseif ($value == 'Parcelado') { 
                $lbl->setValue('Parcelado'); 
                $lbl->class = 'label label-warning'; 
            }  
            return $lbl; 
            });

        $format_value = function($value) {
            if (is_numeric($value)) {
                return number_format($value, 2, ',', '.');
            }
            return $value;
        };

        $column_valor->setTransformer($format_value);
        $column_valor_recebido->setTransformer($format_value);
        $column_juros_recebido->setTransformer($format_value);

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_conta_id->setAction(new TAction([$this, 'onReload']), ['order' => 'conta_id']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa_id']);

        $column_data_vencimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $column_data_recebimento->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $action1 = new TDataGridAction(['ContaReceberForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
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