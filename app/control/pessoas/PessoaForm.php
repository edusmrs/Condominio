<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Core\AdiantiCoreLoader;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TTransaction;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TFormSeparator;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Wrapper\TDBMultiSearch;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapFormBuilder;

class PessoaForm extends TWindow
{
    protected $form;

    public function __construct($param)
    {
        parent::__construct();
        parent::setSize(0.8, null);
        parent::removePadding();
        parent::removeTitleBar();

        //Criar Form
        $this->form = new BootstrapFormBuilder('form_Pessoa');
        $this->form->setFormTitle('Pessoa');
        $this->form->setProperty('style', 'margin:0;border:0');
        $this->form->setClientValidation(true);


        //Cria campos
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $nome_fantasia = new TEntry('nome_fantasia');
        $tipo = new TCombo('tipo');
        $codigo_nacional = new TEntry('codigo_nacional');
        $codigo_estadual = new TEntry('codigo_estadual');
        $codigo_municipal = new TEntry('codigo_municipal');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $observacao = new TText('observacao');
        $cep = new TEntry('cep');
        $logradouro = new TEntry('logradouro');
        $numero = new TEntry('numero');
        $complemento = new TEntry('complemento');
        $bairro = new TEntry('bairro');

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade_id = new TDBCombo('cidade_id', 'db_condominio', 'Cidade', 'id', 'nome', 'nome', $filter);
        $grupo_id = new TDBUniqueSearch('grupo_id', 'db_condominio', 'Grupo', 'id', 'nome');
        $papel_id = new TDBMultiSearch('papel_id', 'db_condominio', 'Papel', 'id', 'nome');
        $estado_id = new TDBCombo('estado_id', 'db_condominio', 'Estado', 'id', '{nome} {uf}');

        $estado_id->setChangeAction(new TAction([$this, 'onChangeEstado']));
        $cep->setExitAction(new TAction([$this, 'onExitCep']));
        $codigo_nacional->setExitAction(new TAction([$this, 'onExitCNPJ']));

        $cidade_id->enableSearch();
        $estado_id->enableSearch();
        $grupo_id->setMinLength(0);
        $papel_id->setMinLength(0);
        $papel_id->setSize('100%', 60);
        $observacao->setSize('100%', 50);
        $tipo->addItems(['F' => 'Fisica', 'j' => 'Juridica']);

        //Adicionar os campos
        $this->form->addFields([new TLabel('Id')], [$id]);
        $this->form->addFields([new TLabel('Tipo')], [$tipo], [new TLabel('CPF/CPNJ')], [$codigo_nacional]);
        $this->form->addFields([new TLabel('Nome')], [$nome]);
        $this->form->addFields([new TLabel('Nome Fantasia')], [$nome_fantasia]);
        $this->form->addFields([new TLabel('Papel')], [$papel_id], [new TLabel('Grupo')], [$grupo_id]);
        $this->form->addFields([new TLabel('I. E.')], [$codigo_estadual], [new TLabel('I. M.')], [$codigo_municipal]);
        $this->form->addFields([new TLabel('Fone')], [$fone], [new TLabel('Email')], [$email]);
        $this->form->addFields([new TLabel('Observação')], [$observacao]);

        $this->form->addFields([new TFormSeparator('Endereço')]);
        $this->form->addFields([new TLabel('CEP')], [$cep])->layout = ['col-sm-2 control-label', 'col-se-4'];
        $this->form->addFields([new TLabel('Logradouro')], [$logradouro], [new TLabel('Número')], [$numero]);
        $this->form->addFields([new TLabel('Complemento')], [$complemento], [new TLabel('Bairro')], [$bairro]);
        $this->form->addFields([new TLabel('Estado')], [$estado_id], [new TLabel('Cidade')], [$cidade_id]);

        //setMask
        $fone->setMask('(99) 99999-9999');
        $cep->setMask('99.999-999');

        //setSize
        $id->setSize('100%');
        $nome->setSize('100%');
        $nome_fantasia->setSize('100%');
        $tipo->setSize('100%');
        $codigo_nacional->setSize('100%');
        $codigo_estadual->setSize('100%');
        $codigo_municipal->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $observacao->setSize('100%');
        $cep->setSize('100%');
        $logradouro->setSize('100%');
        $numero->setSize('100%');
        $bairro->setSize('100%');
        $cidade_id->setSize('100%');
        $grupo_id->setSize('100%');

        $id->setEditable(FALSE);
        $nome->addValidation('Nome', new TRequiredValidator);
        $nome_fantasia->addValidation('Nome Fantasia', new TRequiredValidator);
        $tipo->addValidation('Tipo', new TRequiredValidator);
        $codigo_nacional->addValidation('Código Nacional', new TRequiredValidator);
        $grupo_id->addValidation('Grupo', new TRequiredValidator);
        $fone->addValidation('Fone', new TRequiredValidator);
        $email->addValidation('Email', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $cep->addValidation('CEP', new TRequiredValidator);
        $logradouro->addValidation('Logradouro', new TRequiredValidator);
        $numero->addValidation('Número', new TRequiredValidator);


        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction([$this, 'onEdit']), 'fa:eraser red');

        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);

        parent::add($container);
    }

    public function onSave($param)
    {
        try {
            TTransaction::open('db_condominio');
            $this->form->validate();
            $data = $this->form->getData();

            $object = new Pessoa;
            $object->fromArray((array)$data);
            $object->store();

            PessoaPapel::where('pessoa_id', '=', $object->id)->delete();

            if ($data->papel_id) {

                foreach ($data->papel_id as $papel_id) {
                    $pp = new PessoaPapel;
                    $pp->pessoa_id = $object->id;
                    $pp->store;
                }
            }
            $data->id = $object->id;

            $this->form->setData($data);
            TTransaction::close();

            new TMessage('info', AdiantiCoreTranslator::translate('Record Save'));
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $this->form->setData($this->form->getData());
            TTransaction::rollback();
        }
    }
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }

    public function onEdit($param)
    {
        try {
            if (isset($param['key'])) {
                $key = $param['key'];
                TTransaction::open('db_condominio');
                $object = new Pessoa($key);

                $object->papel_id = PessoaPapel::where('pessoa_id', '=', $object->id)->getIndexedArray('papel_id');
                $this->form->setData($object);

                $data = new stdClass;
                $data->estado_id = $object->cidade->estado_id;
                $data->cidade_id = $object->cidade_id;
                TForm::sendData('form_Pessoa', $data);

                TTransaction::close();
            } else {
                $this->form->clear(TRUE);
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public static function onChangeEstado($param)
    {
        try {
            TTransaction::open('db_condominio');
            if (!empty($param['estado_id'])) {
                $criteria = TCriteria::create(['estado_id' => $param['estado_id']]);
                TDBCombo::reloadFromModel('form_Pessoa', 'cidade_id', 'db_condominio', 'Cidade', 'id', '{nome} {(id)}', 'nome', $criteria, TRUE);
            } else {
                TCombo::clearField('form_Pessoa', 'cidade_id');
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    //Autocompleta campos a partir do CNPJ
    public static function onExitCNPJ($param)
    {
        session_write_close();

        try {
            $cnpj = preg_replace('/[^0-9]/', '', $param['codigo_nacional']);
            $url = 'http://receitaws.com.br/v1/cnpj/'.$cnpj;

            $content = @file_get_contents($url);

            if ($content !== false) {
                $cnpj_data = json_decode($content);

                $data = new stdClass;
                if (is_object($cnpj_data) && $cnpj_data->status !== 'ERROR') {
                    $data->tipo = 'j';
                    $data->nome = $cnpj_data->nome;
                    $data->nome_fantasia = !empty($cnpj_data->fantasia) ? $cnpj_data->fantasia : $cnpj_data->nome;

                    if (!empty($param['cep'])) {
                        $data->cep = $cnpj_data->cep;
                        $data->numero = $cnpj_data->numero;
                    }
                    if (!empty($param['fone'])) {
                        $data->fone = $cnpj_data->fone;
                    }
                    if (!empty($param['email'])) {
                        $data->email = $cnpj_data->email;
                    }

                    TForm::sendData('form_Pessoa', $data, false, true);
                } else {
                    $data->nome = '';
                    $data->nome_fantasia = '';
                    $data->cep = '';
                    $data->numero = '';
                    $data->telefone = '';
                    $data->email = '';
                    TForm::sendData('form_Pessoa', $data, false, true);
                }
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    //Autocomplete CEP
    public static function onExitCEP($param)
    {
        session_write_close();

        try {
            $cep = preg_replace('/[^0-9]/', '', $param['cep']);
            $url = 'http://viacep.com.br/ws/'.$cep.'/json/unicode/';

            $content = @file_get_contents($url);
            if ($content !== false) {
                $cep_data = json_decode($content);

                $data = new stdClass;
                if (is_object($cep_data) && empty($cep_data->erro)) {
                    TTransaction::open('db_condominio');
                    $estado = Estado::where('uf', '=', $cep_data->uf)->first();
                    $cidade = Cidade::where('codigo_ibge', '=', $cep_data->codigo_ibge)->first();
                    TTransaction::close();

                    $data->logradoura = $cep_data->logradouro;
                    $data->complemento = $cep_data->complemento;
                    $data->bairro = $cep_data->bairro;
                    $data->estado_id = $estado->id ?? '';
                    $data->cidade_id = $cidade->id ?? '';

                    TForm::sendData('form_Pessoa', $data, false, true);
                } else {
                    $data->logradouro = '';
                    $data->complemento = '';
                    $data->bairro = '';
                    $data->estado_id = '';
                    $data->cidade_id = '';

                    TForm::sendData('form_Pessoa', $data, false, true);
                }
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onClose(){
        parent::closeWindow();
    }
}
