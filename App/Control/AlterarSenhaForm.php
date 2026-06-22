<?php

use Advogado\Control\Page;
use Advogado\Control\Action;
use Advogado\Database\Transaction;
use Advogado\Session\Session;
use Advogado\Widgets\Form\Form;
use Advogado\Widgets\Form\Password;
use Advogado\Widgets\Wrapper\FormWrapper;
use Advogado\Widgets\Dialog\Message;

class AlterarSenhaForm extends Page
{
    private $form;

    public function __construct()
    {
        parent::__construct();

        // A tela somente faz sentido para usuario autenticado.
        if (!Session::getValue('logged')) {
            echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
            return;
        }

        // O formulario segue a mesma estrutura visual usada no LoginForm.
        $this->form = new FormWrapper(new Form('form_alterar_senha'));
        $this->form->setTitle('Alterar Senha');

        $senhaAtual = new Password('senha_atual');
        $novaSenha = new Password('nova_senha');
        $confirmacao = new Password('confirmacao');

        $senhaAtual->placeholder = 'Digite a senha atual';
        $novaSenha->placeholder = 'Digite a nova senha';
        $confirmacao->placeholder = 'Confirme a nova senha';

        $this->form->addField('Senha atual', $senhaAtual, 200);
        $this->form->addField('Nova senha', $novaSenha, 200);
        $this->form->addField('Confirmar senha', $confirmacao, 200);
        $this->form->addAction('Salvar', new Action(array($this, 'onSalvar')));
        $this->form->addAction('Cancelar', new Action(array($this, 'onCancelar')));

        // O Page base renderiza o que foi adicionado no construtor.
        parent::add($this->form);
    }

    /**
     * Processa a troca de senha.
     * A chamada chega pelo mecanismo de Action do formulario.
     */
    public function onSalvar($param)
    {
        try {
            // Lê os dados postados pelo formulario para validar a troca.
            $data = $this->form->getData();

            $senhaAtual = isset($data->senha_atual) ? trim((string) $data->senha_atual) : '';
            $novaSenha = isset($data->nova_senha) ? trim((string) $data->nova_senha) : '';
            $confirmacao = isset($data->confirmacao) ? trim((string) $data->confirmacao) : '';

            if ($senhaAtual === '' || $novaSenha === '' || $confirmacao === '') {
                new Message('error', 'Preencha a senha atual, a nova senha e a confirmação.');
                return;
            }

            if ($novaSenha !== $confirmacao) {
                new Message('error', 'A nova senha e a confirmação não coincidem.');
                return;
            }

            // Abre a transacao para validar a senha atual e gravar a nova.
            Transaction::open('advogado');
            $user = new Pessoa(Session::getValue('usuario_id'));

            if ($user && $user->validatePassword($senhaAtual)) {
                // Atualiza o hash e persiste o cadastro do usuario logado.
                $user->senha = hash('sha256', $novaSenha);
                $user->store();
                Transaction::close();

                new Message('info', 'Senha alterada com sucesso.');
                return;
            }

            Transaction::close();
            new Message('error', 'Senha atual inválida.');
        } catch (Exception $e) {
            // Em erro, desfaz a transacao e devolve a mensagem ao usuario.
            Transaction::rollback();
            new Message('error', $e->getMessage());
        }
    }

    /**
     * Volta para a pagina inicial do sistema.
     */
    public function onCancelar($param)
    {
        echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
    }
}
