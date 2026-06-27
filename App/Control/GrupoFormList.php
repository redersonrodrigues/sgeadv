<?php
use Advogado\Control\Page;
use Advogado\Control\Action;
use Advogado\Widgets\Form\Form;
use Advogado\Widgets\Form\Entry;
use Advogado\Widgets\Form\Combo;
use Advogado\Widgets\Container\VBox;
use Advogado\Widgets\Datagrid\Datagrid;
use Advogado\Widgets\Datagrid\DatagridColumn;

use Advogado\Database\Transaction;

use Advogado\Traits\DeleteTrait;
use Advogado\Traits\ReloadTrait;
use Advogado\Traits\SaveTrait;
use Advogado\Traits\EditTrait;

use Advogado\Widgets\Wrapper\DatagridWrapper;
use Advogado\Widgets\Wrapper\FormWrapper;
use Advogado\Widgets\Container\Panel;

/**
 * Cadastro de cidades
 */
class GrupoFormList extends Page
{
    private $form;
    private $datagrid;
    private $loaded;
    private $connection;
    private $activeRecord;
    
    use EditTrait;
    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }
    use SaveTrait {
        onSave as onSaveTrait;
    }
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        Auth::requirePermission('grupo.visualizar');

        $this->connection   = 'advogado';
        $this->activeRecord = 'Grupo';

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_grupos'));
        $this->form->setTitle('Grupos');

        // cria os campos do formulário
        $codigo    = new Entry('id');
        $nome = new Entry('nome');

        $codigo->setEditable(FALSE);

        $this->form->addField('Código', $codigo, '30%');
        $this->form->addField('Grupo', $nome, '40%');

        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Limpar', new Action(array($this, 'onEdit')));

        // instancia a Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $codigo   = new DatagridColumn('id',     'Código', 'center', '10%');
        $nome     = new DatagridColumn('nome',   'Grupo',   'left', '50%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);

        $this->datagrid->addAction( 'Editar',  new Action([$this, 'onEdit']),   'id', 'fa fa-edit fa-lg blue');
        $this->datagrid->addAction( 'Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash fa-lg red');

        // monta a página através de uma tabela
        $box = new VBox;
        $box->style = 'display:block';
        $box->add($this->form);
        $box->add($this->datagrid);

        parent::add($box);
    }

    /**
     * Salva os dados
     */
    public function onSave()
    {
        $this->onSaveTrait();
        $this->onReload();
    }

    /**
     * Carrega os dados
     */
    public function onReload()
    {
        $this->onReloadTrait();
        $this->loaded = true;
    }

    /**
     * exibe a página
     */
    public function show()
    {
        // se a listagem ainda não foi carregada
        if (!$this->loaded)
        {
            $this->onReload();
        }
        parent::show();
    }
}
