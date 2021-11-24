<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Util\THyperLink;
use Adianti\Widget\Util\TTextDisplay;
use Adianti\Wrapper\BootstrapFormBuilder;

class PessoaFormView extends TPage{
    protected $form;
    
    public function __construct($param)
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_PessoaView');
        $this->form->setFormTitle('Pessoa');
        $this->form->setColumnClasses(2, ['col-se-3','col-sn-9']);

        $dropdown = new TDropDown('Opções','fa:th');
        $dropdown->addAction('Imprimir', new TAction([$this, 'onPrint'], ['key'=>$param['key'],'static'=>'1']),'far:file-pdf red');
        $dropdown->addAction('Gerar Etiqueta', new TAction([$this, 'onGerarEtiqueta'], ['key'=>$param['key'],'static'=>'1']),'far:envelope purple');
        $dropdown->addAction('Editar', new TAction(['PessoaForm', 'onEdit'], ['key'=>$param['key'],'static'=>'1']),'far:edit blue');
        $dropdown->addAction('Fechar', new TAction([$this, 'onClose'], ['key'=>$param['key'],'static'=>'1']),'fa:times red');

        $this->form->addHeaderWidget($dropdown);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        parent::add($container);
    }

    public function onEdit($param){
        try{
            TTransaction::open('db_condominio');
            $master_object = new Pessoa($param['key']);

            $label_id = new TLabel('Id','#333333','12px','');
            $label_nome_fantasia = new TLabel('Fantasia','#333333','12px','');
            $label_codigo_nacional = new TLabel('CPF/CPNJ','#333333','12px','');
            $label_fone = new TLabel('Fone','#333333','12px','');
            $label_email = new TLabel('Email','#333333','12px','');
            $label_cidade = new TLabel('Local','#333333','12px','');
            $label_created_at = new TLabel('Criado em','#333333','12px','');
            $label_updated_at = new TLabel('Atualizado em','#333333','12px','');

            $text_id = new TTextDisplay($master_object->id,'#333333','12px','');
            $text_nome_fantasia = new TTextDisplay($master_object->nome_fantasia,'#333333','12px','');
            $text_codigo_nacional = new TTextDisplay($master_object->codigo_nacional,'#333333','12px','');
            $text_fone = new THyperLink('<i class="fa fa-phone-square-alt"></i> '.$master_object->fone,'callto: '.$master_object->fone,'#007bff','12px','');
            $text_email = new THyperLink('<i class="fa fa-envelope"></i> '.$master_object->email,'https://mail.google.com/u/0/?view=cm&fs=1$to='.$master_object->email.'$tf=1','#007bff','12px','');
            $link_maps = 'https://www.google.com/maps/place/'.  $master_object->logradouro.','.
                                                                $master_object->numero.','.
                                                                $master_object->bairro.','.
                                                                $master_object->cidade->nome.'+'.
                                                                $master_object->cidade->estado->uf;
            $text_cidade = new THyperLink('<i class="fa fa-map-marker-alt"></i> Link para Google Maps',$link_maps,'#007bff','12px','');
            $text_created_at = new TTextDisplay(TDateTime::convertToMask($master_object->created_at,'yyyy-mm-dd hh:ii:ss','dd/mm/yyyy hh:ii:ss'),'#333333','12px','');
            $text_updated_at = new TTextDisplay(TDateTime::convertToMask($master_object->updated_at,'yyyy-mm-dd hh:ii:ss','dd/mm/yyyy hh:ii:ss'),'#333333','12px','');
            
        }
        catch(Exception $e){
            new TMessage('error', $e->getMessage());
        }
    }
}