<?php

use Advogado\Control\Page;
use Advogado\Control\Action;
use Advogado\Database\Transaction;
use Advogado\Widgets\Form\Form;
use Advogado\Widgets\Form\Entry;
use Advogado\Widgets\Form\Password;
use Advogado\Widgets\Wrapper\FormWrapper;
use Advogado\Widgets\Dialog\Message;

class LoginForm extends Page
{
    private $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper(new Form('form_login'));
        $this->form->setTitle('Login');

        $usuario = new Entry('usuario');
        $senha   = new Password('senha');

        $usuario->placeholder = 'Digite seu usuario';
        $senha->placeholder   = 'Digite sua senha';

        $this->form->addField('Usuario', $usuario, 200);
        $this->form->addField('Senha', $senha, 200);
        $this->form->addAction('Entrar', new Action(array($this, 'onLogin')));

        parent::add($this->form);
    }

    public function onLogin($param)
    {
        try
        {
            $data = $this->form->getData();
            Transaction::open('advogado');

            $user = Auth::login($data->usuario, $data->senha);

            Transaction::close();

            if ($user instanceof Pessoa) {
                echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
            } else {
                new Message('error', 'Credenciais invalidas.');
            }
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onRecuperarSenha($param)
    {
        echo "<script language='JavaScript'> window.location = 'index.php?class=RecuperarSenhaControl'; </script>";
    }

    public function onLogout($param)
    {
        Auth::logout();
        echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
    }
}
