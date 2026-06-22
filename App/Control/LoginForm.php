<?php
use Advogado\Control\Page;
use Advogado\Control\Action;
use Advogado\Database\Transaction;
use Advogado\Widgets\Form\Form;
use Advogado\Widgets\Form\Entry;
use Advogado\Widgets\Form\Password;
use Advogado\Widgets\Wrapper\FormWrapper;
use Advogado\Widgets\Container\Panel;
use Advogado\Widgets\Dialog\Message;

use Advogado\Database\Connection;
use Advogado\Session\Session;

/**
 * Formulário de Login
 */
class LoginForm extends Page
{
    private $form;
    
    /**
     * Construtor da página
     */
    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper(new Form('form_login'));
        $this->form->setTitle('Login');
        
        $usuario = new Entry('usuario');
        $senha   = new Password('senha');
        
        $usuario->placeholder = 'Digite seu usuário';
        $senha->placeholder   = 'Digite sua senha';
        
        $this->form->addField('Usuário', $usuario, 200);
        $this->form->addField('Senha', $senha, 200);
        $this->form->addAction('Entrar', new Action(array($this, 'onLogin')));
        
        parent::add($this->form);
    }
    
    /**
     * Login
     */
    public function onLogin($param)
    {
        try
        {
            $data    = $this->form->getData();
            Transaction::open('advogado');
            $user = Pessoa::findByLogin($data->usuario);

            // Verifica se encontrou algum usuário (o first() do Repository manual)
            if ($user instanceof Pessoa) {
                if ($user->validatePassword($data->senha)) {
                    $grupos = $user->getGrupos();
                    $grupo_nomes = array();
                    if ($grupos) {
                        foreach ($grupos as $grupo) {
                            if (isset($grupo->nome)) {
                                $grupo_nomes[] = $grupo->nome;
                            }
                        }
                    }

                    Session::setValue('logged', TRUE);
                    Session::setValue('usuario_id', $user->id);
                    Session::setValue('nome_usuario', $user->nome);
                    Session::setValue('grupo_usuario', $grupo_nomes ? implode(', ', $grupo_nomes) : '');

                    // ACL: Carrega as permissões na sessão
                    Session::setValue('user_permissions', $user->getPermissoes());
                    Transaction::close();

                    echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
                } else {
                    Transaction::close();
                    new Message('error', 'Credenciais inválidas.');
                }
            } else {
                Transaction::close();
                new Message('error', 'Usuário não encontrado.');
            }
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
    }

    /**
     * Recuperar Senha
     */
    public function onRecuperarSenha($param)
    {
        // Redireciona para a página de recuperação (index_sem_login gerencia carregamento de classes)
        echo "<script language='JavaScript'> window.location = 'index.php?class=RecuperarSenhaControl'; </script>";
    }
    
    /**
     * Logout
     */
    public function onLogout($param)
    {
        Session::freeSession();
        echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
    }
}
