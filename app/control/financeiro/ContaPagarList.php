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

class ContaPagarList extends TPage
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
        $this->setActiveRecord('ContaPagar');
        $this->setDefaultOrder('id', 'asc');
        $this->setOrderCommand('pessoa->nome', '(SELECT nome FROM pessoa WHERE id=conta_pagar.pessoa_id)');
        $this->setLimit(10);

        $this->addFilterField('id', '=', 'id');
        $this->addFilterField('conta_id', '=', 'conta_id');
        $this->addFilterField('pessoa_id', '=', 'pessoa_id');
        $this->addFilterField('status', 'like', 'status');

        $this->form = new BootstrapFormBuilder('form_search_ContaPagar');
        $this->form->setFormTitle('Contas a Pagar');

        $id = new TEntry('id');
        $conta_id = new TDBUniqueSearch('conta_id', 'db_condominio', 'Conta', 'id', 'descricao');
        $conta_id->setMinLength(0);
        $conta_id->setMask('{descricao}');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'db_condominio', 'Pessoa', 'id', 'nome');
        $status = new TEntry('status');

        $this->form->addFields([new TLabel('Id')], [$id]);
        $this->form->addFields([new TLabel('Conta')], [$conta_id]);
        $this->form->addFields([new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([new TLabel('Status')], [$status]);

        $id->setSize('30%');
        $conta_id->setSize('100%');
        $pessoa_id->setSize('100%');
        $status->setSize('100%');

        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['ContaPagarForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        //Cria datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width 100%';

        //Criar as colunas
        $column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_conta_id = new TDataGridColumn('{conta->descricao}', 'Conta', 'left');
        $column_rateio = new TDataGridColumn('rateio', 'Rateio', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'left');
        $column_data_vencimento = new TDataGridColumn('data_vencimento', 'Data Vencimento', 'left');
        $column_data_pagamento = new TDataGridColumn('data_pagamento', 'Data Pagamento', 'left');
        $column_valor_pago = new TDataGridColumn('valor_pago', 'Valor Pago', 'left');
        $column_observacao = new TDataGridColumn('observacao', 'Observação', 'left');
        $column_pessoa_id = new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left');
        $column_saldo = new TDataGridColumn('saldo', 'Saldo', 'left');
        $column_status = new TDataGridColumn('status', 'Status', 'left');

        $column_valor->setTotalFunction(function($values){
             return array_sum((array)$values);
        });

        $column_valor_pago->setTotalFunction(function($values){
            return array_sum((array)$values);
       });

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

        $column_conta_id->enableAutoHide(500);
        $column_pessoa_id->enableAutoHide(500);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_conta_id);
        $this->datagrid->addColumn($column_rateio);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_data_vencimento);
        $this->datagrid->addColumn($column_data_pagamento);
        $this->datagrid->addColumn($column_valor_pago);
        $this->datagrid->addColumn($column_observacao);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_saldo);
        $this->datagrid->addColumn($column_status);

        //Formata valor na datagrid
        $format_value = function ($value) {
            if (is_numeric($value)) {
                return number_format($value, 2, ',', '.');
            }
            return $value;
        };

        $column_valor->setTransformer($format_value);
        $column_valor_pago->setTransformer($format_value);
        $column_saldo->setTransformer($format_value);

        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa->nome']);
        $column_conta_id->setAction(new TAction([$this, 'onReload']), ['order' => 'conta->descricao']);

        //Convert data inicio no datagrids
        $column_data_vencimento->setTransformer(function ($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });

        $column_data_pagamento->setTransformer(function ($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });

        $action1 = new TDataGridAction(['ContaPagarForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
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
