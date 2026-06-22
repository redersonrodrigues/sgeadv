<?php

use Advogado\Control\Action;
use Advogado\Control\Page;
use Advogado\Database\Criteria;
use Advogado\Database\Repository;
use Advogado\Database\Transaction;
use Advogado\Session\Session;
use Advogado\Widgets\Base\Image;
use Advogado\Widgets\Container\VBox;
use Advogado\Widgets\Dialog\Message;
use Advogado\Widgets\Form\File;
use Advogado\Widgets\Form\Form;
use Advogado\Widgets\Wrapper\FormWrapper;

class AlterarFotoForm extends Page
{
    private $form;
    private $box;

    public function __construct()
    {
        parent::__construct();

        // A alteracao de foto e exclusiva do usuario autenticado.
        Auth::requireLogin();

        $this->box = new VBox();
        parent::add($this->box);

        $usuario = $this->loadUsuarioAtual();
        $fotoAtual = $usuario ? $usuario->getFotoDataUri() : 'App/Images/Fotos/Default/default.png';

        // Mostra um preview da foto atual antes do formulario.
        $preview = new VBox();
        $fotoPreview = new Image($fotoAtual);
        $fotoPreview->style = 'width:180px;height:180px;object-fit:cover;border-radius:50%;border:4px solid #7e57c2;margin-bottom:18px;';
        $preview->add($fotoPreview);
        $this->box->add($preview);

        // Formulario no mesmo padrao visual dos demais formularios.
        $this->form = new FormWrapper(new Form('form_alterar_foto'));
        $this->form->setTitle('Alterar Foto');

        $foto = new File('foto');
        $foto->accept = 'image/*';
        $this->form->addField('Foto', $foto, 320);
        $this->form->addAction('Salvar', new Action(array($this, 'onSalvar')));
        $this->form->addAction('Cancelar', new Action(array($this, 'onCancelar')));

        $this->box->add($this->form);
    }

    /**
     * Carrega o usuario logado usando Criteria e Repository.
     */
    private function loadUsuarioAtual()
    {
        try {
            Transaction::open('advogado');

            $criteria = new Criteria();
            $criteria->add('id', '=', (int) Session::getValue('usuario_id'));

            $repository = new Repository('Pessoa');
            $users = $repository->load($criteria);
            $user = ($users && isset($users[0])) ? $users[0] : null;

            Transaction::close();
            return $user;
        } catch (Exception $e) {
            Transaction::rollback();
            return null;
        }
    }

    public function onSalvar($param)
    {
        try {
            // Lê o arquivo enviado pelo formulario.
            $data = $this->form->getData();
            $tmpFile = isset($data->foto) ? $data->foto : '';
            $originalName = isset($_FILES['foto']['name']) ? $_FILES['foto']['name'] : '';

            if (empty($tmpFile) || !file_exists($tmpFile)) {
                new Message('error', 'Selecione uma foto para enviar.');
                return;
            }

            Transaction::open('advogado');

            $criteria = new Criteria();
            $criteria->add('id', '=', (int) Session::getValue('usuario_id'));

            $repository = new Repository('Pessoa');
            $users = $repository->load($criteria);
            $user = ($users && isset($users[0])) ? $users[0] : null;

            if (!$user) {
                Transaction::rollback();
                new Message('error', 'Usuario nao encontrado.');
                return;
            }

            // Atualiza o BLOB e grava a imagem fisica no diretorio do CPF.
            $user->saveFotoUpload($tmpFile, $originalName);
            $user->store();
            Transaction::close();

            new Message('info', 'Foto atualizada com sucesso.');
            echo "<script language='JavaScript'> window.location = 'index.php?class=PerfilPage'; </script>";
        } catch (Exception $e) {
            Transaction::rollback();
            new Message('error', $e->getMessage());
        }
    }

    public function onCancelar($param)
    {
        echo "<script language='JavaScript'> window.location = 'index.php?class=PerfilPage'; </script>";
    }
}
