<?php

    use Advogado\Control\Action;
    use Advogado\Control\Page;
    use Advogado\Widgets\Container\VBox;
    use Advogado\Widgets\Dialog\Message;
    use Advogado\Widgets\Form\Entry;
    use Advogado\Widgets\Form\Form;
    use Advogado\Widgets\Form\Password;
    use Advogado\Widgets\Wrapper\FormWrapper;

    require_once __DIR__ . '/../Model/RecuperarSenha.php';
    require_once __DIR__ . '/../Services/MailService.php';

class RecuperarSenhaControl extends Page
{
    private $form;      // formulário de busca
    private $resetForm; // formulário de redefinição
    private $box;

    public function __construct()
    {
        parent::__construct();

        // caixa principal onde os formulários serão adicionados
        $this->box = new VBox();
        $this->box->style = 'display:block';
        parent::add($this->box);

        // decide se mostra o formulário de e-mail ou o de reset (baseado em GET)
        $method = isset($_GET['method']) ? $_GET['method'] : null;
        $token  = isset($_GET['token']) ? $_GET['token'] : null;
        $showEmailForm = !$token;

        if ($showEmailForm) {
            // Instancia um Formulário para solicitar o e-mail
            $this->form             = new FormWrapper(new Form('form_recuperar_senha'));
            $this->form->setTitle('Recuperar Senha');

            $email                  = new Entry('email');
            $email->placeholder     = 'Digite seu e-mail cadastrado';
            $this->form->addField('E-mail',  $email, '100%');
            $this->form->addAction('Enviar', new Action(array($this, 'onEnviar')));
            $this->form->addAction('Voltar', new Action(array($this, 'onVoltar')));

            $this->box->add($this->form);
        }
        else {
            // Se há token e a requisição não irá executar reset() automaticamente (ex.: quando method não está definido),
            // monta o form de reset uma única vez. Se method='reset' ou method='onReset', o método reset/onReset será
            // chamado posteriormente por Page::show(), então não constrói o form aqui para evitar duplicação.
            if ($token && $method !== 'reset' && $method !== 'onReset') {
                $this->buildResetForm($token);
            }
        }
    }

    public function onVoltar($param)
    {
        echo "<script language='JavaScript'> window.location = 'index.php?class=LoginForm'; </script>";
    }

    public function onEnviar($param)
    {
        try {
            $data = $this->form->getData();
            $email = trim($data->email);

            if (empty($email)) {
                new Message('error', 'Informe o e-mail.');
                return;
            }

            $res = RecuperarSenha::createToken($email);
            if (!$res) {
                new Message('error', 'E-mail não encontrado.');
                return;
            }

            // Monta link de recuperação
            $token = $res['token'];
            $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']);
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $link = $host . $path . '/index.php?class=RecuperarSenhaControl&method=reset&token=' . urlencode($token);

            $body = "<p>Olá {$res['nome']},</p>".
                    "<p>Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para criar uma nova senha (válido por 1 hora):</p>".
                    "<p><a href=\"{$link}\">Redefinir minha senha</a></p>".
                    "<p>Se você não solicitou, ignore esta mensagem.</p>";

            MailService::send($email, $res['nome'], 'Recuperação de senha', $body);

            new Message('info', 'Enviamos um e-mail com instruções para redefinir sua senha.');
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
        }
    }

    // Construir formulário de reset e adicionar à página
    private function buildResetForm($token)
    {
        $this->resetForm = new FormWrapper(new Form('form_reset'));
        $this->resetForm->setTitle('Definir nova senha');

        $senha = new Password('senha');
        $senha2 = new Password('senha2');

        $senha->placeholder = 'Nova senha';
        $senha2->placeholder = 'Repita a nova senha';

        $this->resetForm->addField('Senha', $senha, 200);
        $this->resetForm->addField('Confirmação', $senha2, 200);

        $action = new Action(array($this, 'onReset'));
        if ($token) {
            $action->setParameter('token', $token);
        }
        $this->resetForm->addAction('Salvar', $action);

        $this->box->add($this->resetForm);
    }

    // Método chamado pela Action quando ?method=reset&token=...
    public function reset($param)
    {
        $token = isset($_GET['token']) ? $_GET['token'] : (isset($param['token']) ? $param['token'] : null);
        if (!$token) {
            new Message('error', 'Token inválido.');
            return;
        }

        $row = RecuperarSenha::findByToken($token);
        if (!$row) {
            new Message('error', 'Token inválido ou expirado.');
            return;
        }

        // monta e exibe form de reset
        $this->buildResetForm($token);
    }

    public function onReset($param)
    {
        try {
            $data = $_POST; // form data
            $token = isset($param['token']) ? $param['token'] : (isset($_GET['token']) ? $_GET['token'] : null);
            $senha = isset($data['senha']) ? trim($data['senha']) : '';
            $senha2 = isset($data['senha2']) ? trim($data['senha2']) : '';

            if (!$token) {
                // garante que o form de reset seja mostrado
                $this->buildResetForm($token);
                new Message('error', 'Token ausente.');
                return;
            }

            if (empty($senha) || empty($senha2)) {
                $this->buildResetForm($token);
                new Message('error', 'Informe a nova senha e sua confirmação.');
                return;
            }

            if ($senha !== $senha2) {
                $this->buildResetForm($token);
                new Message('error', 'As senhas não coincidem.');
                return;
            }

            $row = RecuperarSenha::findByToken($token);
            if (!$row) {
                $this->buildResetForm($token);
                new Message('error', 'Token inválido ou expirado.');
                return;
            }

            RecuperarSenha::updatePassword($row['usuario_id'], $senha);
            RecuperarSenha::consumeToken($token);

            new Message('info', 'Senha atualizada com sucesso.');
            echo "<script language='JavaScript'> window.location = 'index.php?class=LoginForm'; </script>";
        } catch (Exception $e) {
            $this->buildResetForm($token ?? null);
            new Message('error', $e->getMessage());
        }
    }
}
