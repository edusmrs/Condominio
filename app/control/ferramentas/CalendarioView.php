<?php
/**
 * CalendarioView
 *
 * @version    1.0
 * @package    erphouse
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CalendarioView extends TPage
{
    private $fc;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $options = ['register_state' => 'false'];
        
        $this->fc = new TFullCalendar(date('Y-m-d'), 'month');
        $this->fc->setReloadAction(new TAction(array($this, 'getEvents')));
        $this->fc->setDayClickAction(new TAction(array('CalendarioForm', 'onStartEdit'), $options));
        $this->fc->setEventClickAction(new TAction(array('CalendarioForm', 'onEdit'), $options));
        $this->fc->setEventUpdateAction(new TAction(array('CalendarioForm', 'onUpdateEvent'), $options));
        
        $this->fc->setOption('businessHours', [ [ 'dow' => [ 1, 2, 3, 4, 5 ], 'start' => '08:00', 'end' => '18:00' ]]);
        parent::add( $this->fc );
    }
    
    /**
     * Output events as an json
     */
    public static function getEvents($param=NULL)
    {
        $return = array();
        try
        {
            TTransaction::open('db_condominio');
            
            $events = Evento::where('inicio', '>=', $param['start'])
                            ->where('fim',   '<=', $param['end'])
                            ->where('system_user_id', '=', TSession::getValue('userid'))->load();
            
            if ($events)
            {
                foreach ($events as $event)
                {
                    $event_array = $event->toArray();
                    $event_array['start'] = str_replace( ' ', 'T', $event_array['inicio']);
                    $event_array['end']   = str_replace( ' ', 'T', $event_array['fim']);
                    $event_array['color'] = $event_array['cor'];
                    
                    $popover_content = $event->render("<b>Título</b>: {titulo} <br> <b>Descrição</b>: {descricao}");
                    $event_array['title'] = TFullCalendar::renderPopover($event_array['titulo'], 'Evento', $popover_content);
                    
                    $return[] = $event_array;
                }
            }
            TTransaction::close();
            echo json_encode($return);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Reconfigure the callendar
     */
    public function onReload($param = null)
    {
        if (isset($param['view']))
        {
            $this->fc->setCurrentView($param['view']);
        }
        
        if (isset($param['date']))
        {
            $this->fc->setCurrentDate($param['date']);
        }
    }
}
