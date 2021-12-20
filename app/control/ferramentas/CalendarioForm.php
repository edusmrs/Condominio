<?php
/**
 * CalendarForm
 *
 * @version    1.0
 * @package    erphouse
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CalendarioForm extends TPage
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_event');
        $this->form->setFormTitle('Evento');
        $this->form->setProperty('style', 'margin-bottom:0;box-shadow:none');
        
        $hours = array();
        $minutes = array();
        for ($n=0; $n<24; $n++)
        {
            $hours[$n] = str_pad($n, 2, '0', STR_PAD_LEFT);
        }
        
        for ($n=0; $n<=55; $n+=5)
        {
            $minutes[$n] = str_pad($n, 2, '0', STR_PAD_LEFT);
        }
        
        // create the form fields
        $view           = new THidden('view');
        $id             = new TEntry('id');
        $cor            = new TColor('cor');
        $data_inicial   = new TDate('data_inicial');
        $hora_inicial   = new TCombo('hora_inicial');
        $minuto_inicial = new TCombo('minuto_inicial');
        $data_final     = new TDate('data_final');
        $hora_final     = new TCombo('hora_final');
        $minuto_final   = new TCombo('minuto_final');
        $titulo         = new TEntry('titulo');
        $descricao      = new TText('descricao');
        $cor->setValue('#3a87ad');
        
        $hora_inicial->addItems($hours);
        $minuto_inicial->addItems($minutes);
        $hora_final->addItems($hours);
        $minuto_final->addItems($minutes);
        
        $id->setEditable(FALSE);
        
        // define the sizes
        $id->setSize(40);
        $cor->setSize(100);
        $data_inicial->setSize(120);
        $data_final->setSize(120);
        $hora_inicial->setSize(70);
        $hora_final->setSize(70);
        $minuto_inicial->setSize(70);
        $minuto_final->setSize(70);
        $titulo->setSize(400);
        $descricao->setSize(400, 50);
        
        $hora_inicial->setChangeAction(new TAction(array($this, 'onChangeStartHour')));
        $hora_final->setChangeAction(new TAction(array($this, 'onChangeEndHour')));
        $data_inicial->setExitAction(new TAction(array($this, 'onChangeStartDate')));
        $data_final->setExitAction(new TAction(array($this, 'onChangeEndDate')));

        // add one row for each form field
        $this->form->addFields( [$view] );
        $this->form->addFields( [new TLabel('ID:', null, null, 'b')]);
        $this->form->addFields( [$id] );
        $this->form->addFields( [new TLabel('Cor:', null, null, 'b')] );
        $this->form->addFields( [$cor] );
        $this->form->addFields( [new TLabel('Início:', null, null, 'b')]);
        $this->form->addFields( [$data_inicial, $hora_inicial, ':', $minuto_inicial] );
        $this->form->addFields( [new TLabel('Fim:', null, null, 'b')]);
        $this->form->addFields( [$data_final, $hora_final, ':', $minuto_final] );
        $this->form->addFields( [new TLabel('Título:', null, null, 'b')]);
        $this->form->addFields( [$titulo] );
        $this->form->addFields( [new TLabel('Descrição:', null, null, 'b')]);
        $this->form->addFields( [$descricao] );
        
        $this->form->addAction( _t('Save'),   new TAction(array($this, 'onSave')),   'fa:save green');
        $this->form->addAction( _t('Clear'),  new TAction(array($this, 'onEdit')),   'fa:eraser orange');
        $this->form->addAction( _t('Delete'), new TAction(array($this, 'onDelete')), 'far:trash-alt red');
        $this->form->addHeaderActionLink( _t('Close'), new TAction(array($this, 'onClose')), 'fa:times red');
        
        parent::add($this->form);
    }
    
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
    
    /**
     * Executed when user leaves start hour field
     */
    public static function onChangeStartHour($param=NULL)
    {
        $obj = new stdClass;
        if (empty($param['minuto_inicial']))
        {
            $obj->minuto_inicial = '0';
            TForm::sendData('form_event', $obj);
        }
        
        if (empty($param['hora_final']) AND empty($param['minuto_final']))
        {
            $obj->hora_final = $param['hora_inicial'] +1;
            $obj->minuto_final = '0';
            TForm::sendData('form_event', $obj);
        }
    }
    
    /**
     * Executed when user leaves end hour field
     */
    public static function onChangeEndHour($param=NULL)
    {
        if (empty($param['minuto_final']))
        {
            $obj = new stdClass;
            $obj->minuto_final = '0';
            TForm::sendData('form_event', $obj);
        }
    }
    
    /**
     * Executed when user leaves start date field
     */
    public static function onChangeStartDate($param=NULL)
    {
        if (empty($param['data_final']) AND !empty($param['data_inicial']))
        {
            $obj = new stdClass;
            $obj->data_final = $param['data_inicial'];
            TForm::sendData('form_event', $obj);
        }
    }
    
    /**
     * Executed when user leaves end date field
     */
    public static function onChangeEndDate($param=NULL)
    {
        if (empty($param['hora_final']) AND empty($param['minuto_final']) AND !empty($param['hora_inicial']))
        {
            $obj = new stdClass;
            $obj->hora_final = min($param['hora_inicial'],22) +1;
            $obj->minuto_final = '0';
            TForm::sendData('form_event', $obj);
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public static function onSave($param)
    {
        try
        {
            // open a transaction with database 'erphouse'
            TTransaction::open('db_condominio');
            
            // get the form data into an active record Entry
            $data = (object) $param;
            
            $object = new Evento;
            $object->cor       = $data->cor;
            $object->id        = $data->id;
            $object->titulo    = $data->titulo;
            $object->descricao = $data->descricao;
            $object->inicio    = $data->data_inicial . ' ' . str_pad($data->hora_inicial, 2, '0', STR_PAD_LEFT) . ':' . str_pad($data->minuto_inicial, 2, '0', STR_PAD_LEFT) . ':00';
            $object->fim       = $data->data_final   . ' ' . str_pad($data->hora_final, 2, '0', STR_PAD_LEFT)   . ':' . str_pad($data->minuto_final, 2, '0', STR_PAD_LEFT)   . ':00';
            $object->system_user_id = TSession::getValue('userid');
            $object->store(); // stores the object
            
            TTransaction::close(); // close the transaction
            
            TScript::create("Template.closeRightPanel()");
            
            $posAction = new TAction(array('CalendarioView', 'onReload'));
            $posAction->setParameter('target_container', 'adianti_div_content');
            $posAction->setParameter('view', $data->view);
            $posAction->setParameter('date', $data->data_inicial);
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), $posAction);
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key = $param['key'];
                
                // open a transaction with database 'db_gloria'
                TTransaction::open('db_condominio');
                
                $object = new Evento($key);
                
                if ($object->system_user_id !== TSession::getValue('userid'))
                {
                    throw new Exception(_t('Permission denied'));
                }
                
                $data = new stdClass;
                $data->id             = $object->id;
                $data->cor            = $object->cor;
                $data->titulo         = $object->titulo;
                $data->descricao      = $object->descricao;
                $data->data_inicial   = substr($object->inicio,0,10);
                $data->hora_inicial   = substr($object->inicio,11,2);
                $data->minuto_inicial = substr($object->inicio,14,2);
                $data->data_final     = substr($object->fim,0,10);
                $data->hora_final     = substr($object->fim,11,2);
                $data->minuto_final   = substr($object->fim,14,2);
                $data->view = $param['view'];
                
                // fill the form with the active record data
                $this->form->setData($data);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Delete event
     */
    public static function onDelete($param)
    {
        $action = new TAction(array('CalendarioForm', 'Delete'));
        $action->setParameters($param);

        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            // get the parameter $key
            $key = $param['id'];
            // open a transaction with database
            TTransaction::open('db_condominio');
            
            // instantiates object
            $object = new Evento($key, FALSE);
            
            // deletes the object from the database
            $object->delete();
            
            // close the transaction
            TTransaction::close();
            
            TScript::create("Template.closeRightPanel()");
            
            $posAction = new TAction(array('CalendarioView', 'onReload'));
            $posAction->setParameter('target_container', 'adianti_div_content');
            $posAction->setParameter('view', $param['view']);
            $posAction->setParameter('date', $param['data_inicial']);
            
            // shows the success message
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'), $posAction);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Fill form from the user selected time
     */
    public function onStartEdit($param)
    {
        $this->form->clear();
        $data = new stdClass;
        $data->view = $param['view']; // calendar view
        $data->cor = '#3a87ad';
        
        if ($param['date'])
        {
            if (strlen($param['date']) == 10) // data
            {
                $data->data_inicial = $param['date'];
                $data->data_final = $param['date'];
            }
            if (strlen($param['date']) == 19) // datahora
            {
                $data->data_inicial   = substr($param['date'],0,10);
                $data->hora_inicial   = substr($param['date'],11,2);
                $data->minuto_inicial = substr($param['date'],14,2);
                
                $data->data_final   = substr($param['date'],0,10);
                $data->hora_final   = substr($param['date'],11,2) +1;
                $data->minuto_final = substr($param['date'],14,2);
            }
            $this->form->setData( $data );
        }
    }
    
    /**
     * Update event. Result of the drag and drop or resize.
     */
    public static function onUpdateEvent($param)
    {
        try
        {
            if (isset($param['id']))
            {
                // get the parameter $key
                $key=$param['id'];
                
                // open a transaction with database 'db_gloria'
                TTransaction::open('db_condominio');
                
                $object = new Evento($key);
                $object->inicio = str_replace('T', ' ', $param['start_time']);
                $object->fim   = str_replace('T', ' ', $param['end_time']);
                $object->store();
                                
                // close the transaction
                TTransaction::close();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
