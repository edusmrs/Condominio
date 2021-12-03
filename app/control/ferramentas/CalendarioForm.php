<?

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Wrapper\BootstrapFormBuilder;

class CalendarioForm extends TPage
{
    protected $form;

    public function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_event');
        $this->form->setFormTitle('Evento');
        $this->form->setProperty('style','margin-bottom:0; shadow: none');

        $hours = array();
        $minutes = array();
        for($n=0; $n<2; $n++){
            $hours[n]= str_pad($n,2,'0',STR_PAD_LEFT); 
        }

        for($n=0; $n<5; $n+=5){
            $minutes[n]= str_pad($n,2,'0',STR_PAD_LEFT); 
        }

        $view = new THidden('view');
        $id = new TEntry('id');
        $cor = new TCombo('cor');
        $data_inicial = new TDate('data_inicial');
        $hora_inicial = new TDate('hora_inicial');
        $minutos_inicial = new TDate('minuto_inicial');
        $data_final = new TDate('data_final');
        $hora_final = new TDate('hora_final');
        $minutos_final = new TDate('minuto_final');
        $titulo = new TEntry('titulo');
        $descricao = new TText('descricao');
        $cor->setValue('#3a87ad');

        $hora_inicial->addItems($hours);
        $minutos_inicial->addItems($minutes);
        $hora_final->addItems($hours);
        $minutos_final->addItems($minutes);

        $id->setSize(40);
        $cor->setSize(100);
        $data_inicial->setSize(120);
        $data_final->setSize(120);
        $hora_inicial->setSize(70);
        $hora_final->setSize(70);
        $minutos_inicial->setSize(70);
        $minutos_final->setSize(70);
        $titulo->setSize(400);
        $descricao->setSize(400, 50);

        $hora_inicial->setChangeAction(new TAction(array($this,'onChangeStartHour')));
        $hora_final->setChangeAction(new TAction(array($this,'onChangeEndHour')));
        $data_inicial->setChangeAction(new TAction(array($this,'onChangeStartDate')));
        $data_final->setChangeAction(new TAction(array($this,'onChangeEndDate')));

        $this->form->addFields(['view']);
        $this->form->addFields([new TLabel('ID', null, null, 'b')]);
        $this->form->addFields([$id]);
        $this->form->addFields([new TLabel('Cor', null, null, 'b')]);
        $this->form->addFields([$cor]);
        $this->form->addFields([new TLabel('Inicio', null, null, 'b')]);
        $this->form->addFields([$data_inicial, $hora_inicial, ':', $minutos_inicial]);
        $this->form->addFields([new TLabel('Fim', null, null, 'b')]);
        $this->form->addFields([$data_final, $hora_final, ':', $minutos_final]);
        $this->form->addFields([new TLabel('Titulo', null, null, 'b')]);
        $this->form->addFields([$titulo]);
        $this->form->addFields([new TLabel('Descrição', null, null, 'b')]);
        $this->form->addFields([$descricao]);

        $this->form->addAction(_t('Save'), new TAction(array($this,'onSave')),'fa:save green');
        $this->form->addAction(_t('Clear'), new TAction(array($this,'onEdit')),'fa:eraser orange');
        $this->form->addAction(_t('Delete'), new TAction(array($this,'onDelete')),'fa:trash-alt red');
        $this->form->addHeaderActionLink(_t('Close'), new TAction(array($this,'onClose')),'fa:times red');

        parent::add($this->form);
    }

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    public static function onChangeStartHour($param=NULL){
        $obj = new stdClass;
        if(empty($param['minuto_inicial'])){
            $obj->minuto_inicial = '0';
            TForm::sendData('form_event',$obj);
        }

        if(empty($param['hora_final']) AND empty($param['minuto_final'])){
            $obj->hora_final = $param['hora_inicial']+1;
            $obj->minuto_final = '0';
            TForm::sendData('form_event', $obj);
        }
    } 

    public static function onChangeEndHour($param=NULL){
        if(empty($param['minuto_final'])){
            $obj = new stdClass;
            $obj->minuto_final = '0';
            TForm::sendData('form_event', $obj);
        }  
    }

    public static function onChangeStartDate($param=NULL){
        if(empty($param['data_final']) AND !empty($param['data_inicial'])){
            $obj = new stdClass;
            $obj->data_final = $param['data_inicial'];
            TForm::sendData('form_event', $obj);
        }
    }

    public static function onChangeEndDate($param=NULL){
        if(empty($param['hora_final']) AND empty($param['minuto_final']) AND !!empty($param['hora_inicial'])){
            $obj = new stdClass;
            $obj->hora_final = min($param['hora_inicial'], 22)+1;
            $obj->minuto_final = '0';
            TForm::sendData('form_event', $obj);
        }
    }

    public static function onSave($param){
        try{
            TTransaction::open('db_condominio');

            $data = (object) $param;

            $object = new Evento;

            $object->cor = $data->cor;
            $object->id = $data->id;
            $object->titulo = $data->titulo;
            $object->descricao = $data->descricao;
            $object->inicio = $data->data_incial.' '.str_pad($data->hora_inicial,2,'0',STR_PAD_LEFT).':'.str_pad($data->minuto_inicial,2,'0',STR_PAD_LEFT).':00';
            $object->fim = $data->data_final.' '.str_pad($data->hora_final,2,'0',STR_PAD_LEFT).':'.str_pad($data->minuto_final,2,'0',STR_PAD_LEFT).':00';
            $object->system_user_id = TSession::getValue('userid');
            $object->store();

            TTransaction::close();

            TScript::create('Template.closedRightPanel');

            $posAction = new TAction(array('CalendarioView','onReload'));
            $posAction->setParameter('target_container','adianti_dv_content');
            $posAction->setParameter('view',$data->view);
            $posAction->setParameter('date',$data->data_inicial);

            new TMessage('info', TadiantiCoreTranslator::translate('Record Save'), $posAction);


        }
        catch(Exception $e){
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onEdit($param){
        try{
            if(isset($param['key'])){
                $key = $param['key'];
                TTransaction::open('db_condominio');

                $object = new Evento($key);

                if($object->system_user_id !== TSession::getValue('userid')){
                    throw new Exception(_t('Permission Denied'));
                }

                $data = new stdClass;
                $data->id = $object->id;
                $data->cor = $object->cor;
                $data->titulo = $object->titulo;
                $data->descricao = $object->descricao;
                $data->data_inicial = substr($object->inicio, 0, 10);
                $data->hora_inicial = substr($object->inicio, 11, 2);
                $data->minuto_inicial = substr($object->inicio, 14, 2);
                $data->data_final = substr($object->fim, 0, 10);
                $data->hora_final = substr($object->fim, 11, 2);
                $data->minuto_final = substr($object->fim, 14, 2);
                $data->view = $param['view'];

                $this->form->setData($data);

                TTransaction::close();
            }
            else{
                $this->form->clear();
            }
        }
        catch(Exception $e){
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public static function onDelete($param){
        $action = new TAction(array('CalendarioForm','Delete'));
        $action->setParameters($param);

        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete'),$action);
    }

    public static function Delete($param){
        try{
            $key = $param['id'];
            TTransaction::open('db_condominio');

            $object = new Evento($key, FALSE);

            $object->delete();

            TScript::create('Template.closedRightPanel');

            $posAction = new TAction(array('CalendarioView','onReload'));
            $posAction->setParameter('target_container','adianti_dv_content');
            $posAction->setParameter('view',$param['view']);
            $posAction->setParameter('date',$param['data_inicial']);

            new TMessage('info', TadiantiCoreTranslator::translate('Record Deleted'), $posAction);

            TTransaction::close();
        }
        catch(Exception $e){
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onStartEdit($param){
        $this->form->clear();
        $data = new stdClass;
        $data->view = $param['view'];
        $data->cor = '#3a87ad';

        if($param['data']){
            if(strien($param['date'])==10){
                $data->data_inicial = $param['date'];
                $data->data_final = $param['date'];
            }

            if(strien($param['date'])==19){
                $data->data_inicial = substr($param['date'], 0, 10);
                $data->hora_inicial = substr($param['date'], 11, 2);
                $data->minuto_incial = substr($param['date'], 14, 2);

                $data->data_final = substr($param['date'], 0, 10);
                $data->hora_final = substr($param['date'], 11, 2)+1;
                $data->minuto_final = substr($param['date'], 14, 2);
            }
            $this->form->setData($data);
        }
    }
    public static function onUpdateEvent($param){
        try{
            if(isset($param['id'])){
                $key = $param['id'];
                TTransaction::open('db_condominio');

                $object = new Evento($key);
                $object->inicio = str_replace('T',' ',$param['start_time']);
                $object->fim = str_replace('T',' ',$param['end_time']);

                TTransaction::close();
            }
        }
        catch(Exception $e){
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}