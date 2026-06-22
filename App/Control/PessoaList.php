<?php

use Advogado\Control\Action;
use Advogado\Control\Page;
use Advogado\Database\Criteria;
use Advogado\Database\Repository;
use Advogado\Database\Transaction;
use Advogado\Session\Session;
use Advogado\Widgets\Container\VBox;
use Advogado\Widgets\Datagrid\Datagrid;
use Advogado\Widgets\Datagrid\DatagridColumn;
use Advogado\Widgets\Dialog\Message;
    use Advogado\Widgets\Dialog\Question;
    use Advogado\Widgets\Form\Entry;
    use Advogado\Widgets\Form\Form;
    use Advogado\Widgets\Wrapper\DatagridWrapper;
    use Advogado\Widgets\Wrapper\FormWrapper;

    class PessoaList extends Page
{
    private $form;      // formulário de buscas
    private $datagrid;  // listagem
    private $loaded;

    public function __construct()
    {
        parent::__construct();

        // A lista de usuarios somente faz sentido com sessao ativa.
        if (!Session::getValue('logged')) {
            echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
            return;
        }

        
        // instancia um formulário de buscas
        $this->form = new FormWrapper(new Form('form_busca_pessoas'));
        $this->form->setTitle('Busca Pessoas');
        $nome = new Entry('nome');
        $this->form->addField('Nome', $nome, '80%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Novo', new Action(array(new PessoaForm(), 'onEdit')));
        
        // instancia objerto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid());
        
        // instancia as colunas da Datagrid
        $codigo     = new DatagridColumn('id', 'Código', 'center', '10%');
        $nome       = new DatagridColumn('nome','Nome', 'left', '30%');
        $endereco   = new DatagridColumn('endereco', 'Endereço', 'left', '20%');
        $cidade     = new DatagridColumn('nome_cidade', 'Cidade', 'left', '20%');
        $telefone   = new DatagridColumn('telefone', 'Telefone', 'left', '20%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($endereco);
        $this->datagrid->addColumn($cidade);
        $this->datagrid->addColumn($telefone);

        $this->datagrid->addAction('Editar', new Action([new PessoaForm(), 'onEdit']), 'id', 'fa fa-edit fa-lg blue');
        $this->datagrid->addAction('Excluir', new Action([$this, 'onDelete']), 'id', 'fa fa-trash fa-lg red darken-1');

        // monta a página por meio de uma caixa
        $box = new VBox();
        $box->style = 'display:block; margin:20px';
        $box->add($this->form);
        $box->add($this->datagrid);

        parent::add($box);
    }

    /**
     * Carrega a listagem com Criteria e Repository.
     */
    public function onReload()
    {
        try {
            Transaction::open('advogado'); // inicia a transação com o banco de dados .ini
            $repository = new Repository('Pessoa');

            // cria um critério de seleção de dados
            $criteria = new Criteria();
            $criteria->setProperty('order', 'id');

            // obtem os dados do formulário de buscas
            $dados = $this->form->getData();

            // verifica se o usuário preencheu o formulário
            if ($dados->nome) {
                // filtra pelo nome da pessoa
                $criteria->add('nome','like', "%{$dados->nome}%");
            }

            // carrega os dados que satisfazem o critério
            $pessoas = $repository->load($criteria);
            $this->datagrid->clear();
            if ($pessoas) {
                foreach ($pessoas as $pessoa) {
                    // adiciona o objeto à Datagrid
                    $this->datagrid->addItem($pessoa);
                }
            }

            // finaliza a transação
            Transaction::close();

            $this->loaded = true;
        } catch (Exception $e) {
            Transaction::rollback();
            new Message('error', $e->getMessage());
        }
    }

    public function onDelete($param)
    {
        $id = $param['id']; // obtém o parâmetro id
        $action1 = new Action(array($this, 'Delete'));
        $action1->setParameter('id', $id);

        new Question('Tem certeza que deseja excluir o registro?', $action1);
    }

    public function Delete($param) {
        try {
            $id = $param['id'];                     // obtém a chave
            Transaction::open('advogado'); // inicia a transação como banco de dados advogado.ini
            $pessoa = Pessoa::find($id);
            $pessoa->delete();                      // deleta o objeto do banco de dados
            Transaction::close();                   // finaliza a transação
            $this->onReload();                      // carrega a datagrid
            new Message('info', "Registro excluido com sucesso.");
        }
        catch (Exception $e) {
            new Message('error', $e->getMessage());
        }
    }

    public function show()
    {
        // se a listagem ainda não foi carregada
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show(); // TODO: Change the autogenerated stub
    }
}
