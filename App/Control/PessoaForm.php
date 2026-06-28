<?php

use Advogado\Control\Action;
use Advogado\Control\Page;
use Advogado\Database\Criteria;
use Advogado\Database\Repository;
use Advogado\Database\Transaction;
use Advogado\Session\Session;
use Advogado\Widgets\Base\Image;
use Advogado\Widgets\Base\Element;
use Advogado\Widgets\Container\HBox;
use Advogado\Widgets\Container\Panel;
use Advogado\Widgets\Container\VBox;
use Advogado\Widgets\Dialog\Message;
use Advogado\Widgets\Form\CheckGroup;
use Advogado\Widgets\Form\Combo;
use Advogado\Widgets\Form\Date;
use Advogado\Widgets\Form\Entry;
use Advogado\Widgets\Form\File;
use Advogado\Widgets\Form\Form;
use Advogado\Widgets\Form\Password;
use Advogado\Widgets\Form\RadioGroup;
use Advogado\Widgets\Form\Text;
use Advogado\Widgets\Wrapper\FormWrapper;

/*
 * Formulario para cadastro de pessoas
 */
class PessoaForm extends Page
{
    private $form;
    private $fotoPreview;
    private $senhaField;
    private $confirmacaoField;
    private $alterarSenhaPanel;

    public function __construct()
    {
        parent::__construct();

        Auth::requirePermission('usuario.gerenciar');

        $this->form = new FormWrapper(new Form('form_pessoas'));
        $this->form->setTitle('Cadastro de Usuario');

        $this->fotoPreview = new Image('App/Images/Fotos/Default/default.png');
        $this->fotoPreview->style = 'width:160px;height:160px;object-fit:cover;border-radius:8px;border:1px solid #ddd;margin:0 0 15px 0;display:block;';

        $codigo = new Entry('id');
        $nome = new Entry('nome');
        $endereco = new Entry('endereco');
        $bairro = new Entry('bairro');
        $cidade = new Combo('cidade_id');
        $telefone = new Entry('telefone');
        $email = new Entry('email');
        $tipo = new RadioGroup('tipo');
        $tipo->id = 'pessoa_tipo';
        $cpf = new Entry('cpf');
        $cnpj = new Entry('cnpj');
        $cnpj->id = 'pessoa_cnpj';
        $this->senhaField = new Password('senha');
        $this->confirmacaoField = new Password('confirmacao');
        $foto = new File('foto');
        $foto->accept = 'image/*';
        $oab = new Entry('oab');
        $oab->id = 'pessoa_oab';
        $oabUf = new Entry('oab_uf');
        $oabUf->id = 'pessoa_oab_uf';
        $cargo = new Entry('cargo');
        $salario = new Entry('salario');
        $ativo = new RadioGroup('ativo');
        $contratadoEm = new Date('contratado_em');
        $demitidoEm = new Date('demitido_em');
        $observacoes = new Text('observacoes');
        $grupo = new CheckGroup('ids_grupos');
        $especialidade = new CheckGroup('ids_especialidades');
        $especialidade->id = 'pessoa_especialidades';

        Transaction::open('advogado');
        $cidades = Cidade::all();
        $items = array();
        foreach ($cidades as $obj_cidade) {
            $items[$obj_cidade->id] = $obj_cidade->nome;
        }
        $cidade->addItems($items);

        $grupos = Grupo::all();
        $items = array();
        foreach ($grupos as $obj_grupo) {
            $items[$obj_grupo->id] = $obj_grupo->nome;
        }
        $grupo->addItems($items);

        $especialidades = Especialidade::all();
        $items = array();
        foreach ($especialidades as $obj_especialidade) {
            $items[$obj_especialidade->id] = $obj_especialidade->nome;
        }
        $especialidade->addItems($items);

        Transaction::close();

        $nome->placeholder = 'Nome Completo';
        $endereco->placeholder = 'Endereco';
        $bairro->placeholder = 'Bairro';
        $telefone->placeholder = 'Telefone';
        $email->placeholder = 'E-mail';
        $cpf->placeholder = 'CPF sem pontuacao';
        $cnpj->placeholder = 'CNPJ sem pontuacao';
        $this->senhaField->id = 'pessoa_senha';
        $this->confirmacaoField->id = 'pessoa_confirmacao';
        $this->senhaField->placeholder = 'Senha de acesso';
        $this->confirmacaoField->placeholder = 'Confirme a senha';
        $oab->placeholder = 'OAB';
        $oabUf->placeholder = 'UF';
        $cargo->placeholder = 'Cargo';
        $salario->placeholder = 'Salario';
        $observacoes->placeholder = 'Observacoes';

        $this->form->addField('Codigo', $codigo, '20%');
        $this->form->addField('Nome', $nome, '80%');
        $this->form->addField('E-mail', $email, '80%');
        $this->form->addField('Telefone', $telefone, '40%');
        $this->form->addField('Tipo', $tipo, '40%');
        $this->form->addField('CPF', $cpf, '40%');
        $this->form->addField('CNPJ', $cnpj, '40%');

        $this->form->addField('Endereco', $endereco, '80%');
        $this->form->addField('Bairro', $bairro, '80%');
        $this->form->addField('Cidade', $cidade, '70%');

        $this->form->addField('Cargo', $cargo, '70%');
        $this->form->addField('Salario', $salario, '40%');
        $this->form->addField('OAB', $oab, '40%');
        $this->form->addField('UF OAB', $oabUf, '20%');

        $this->form->addField('Contratado em', $contratadoEm, '40%');
        $this->form->addField('Demitido em', $demitidoEm, '40%');

        $isEditMode = !empty($_GET['id']);

        $this->form->addField('Senha', $this->senhaField, '40%');
        $this->form->addField('Confirmar Senha', $this->confirmacaoField, '40%');

        $this->form->addField('Ativo', $ativo, '70%');
        $this->form->addField('Foto', $foto, '80%');
        $this->form->addField('Grupos', $grupo, '80%');
        $this->form->addField('Especialidades', $especialidade, '80%');
        $this->form->addField('Observacoes', $observacoes, '80%');

        $tipo->addItems(array(
            'F' => 'Fisica',
            'J' => 'Juridica'
        ));
        $ativo->addItems(array(
            '1' => 'ativo',
            '0' => 'Inativo'
        ));

        $codigo->setEditable(FALSE);
        $grupo->setLayout('horizontal');
        $ativo->setLayout('horizontal');
        $tipo->setLayout('horizontal');
        $especialidade->setLayout('horizontal');
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        $this->form->addAction('Cancelar', new Action(array($this, 'onCancel')));

        $painel_foto = new Panel();
        $painel_foto->style = 'margin:10px; flex: 1; display: flex; justify-content: center; align-items: flex-start;';
        $painel_foto->add($this->fotoPreview);

        $painel_form = new Panel();
        $painel_form->style = 'margin:10px; flex: 3;';
        $painel_form->add($this->form);

        if ($isEditMode) {
            $this->alterarSenhaPanel = new Panel();
            $this->alterarSenhaPanel->style = 'margin:10px 10px 0 10px;';

            $botaoAlterarSenha = new Element('button');
            $botaoAlterarSenha->type = 'button';
            $botaoAlterarSenha->class = 'btn btn-warning btn-sm';
            $botaoAlterarSenha->id = 'btn_alterar_senha';
            $botaoAlterarSenha->add('Alterar senha');
            $botaoAlterarSenha->onclick = "
                var senha = document.getElementById('pessoa_senha');
                var confirmacao = document.getElementById('pessoa_confirmacao');
                var botao = document.getElementById('btn_alterar_senha');
                
                if (senha && senha.parentNode && senha.parentNode.parentNode) {
                    senha.parentNode.parentNode.style.display = 'block';
                    senha.focus();
                }
                if (confirmacao && confirmacao.parentNode && confirmacao.parentNode.parentNode) {
                    confirmacao.parentNode.parentNode.style.display = 'block';
                }
                if (botao) {
                    botao.style.display = 'none';
                }
                
                return false;
            ";

            $this->alterarSenhaPanel->add($botaoAlterarSenha);
            $painel_form->add($this->alterarSenhaPanel);

            $script = new Element('script');
            $script->add("
                document.addEventListener('DOMContentLoaded', function () {
                    var senha = document.getElementById('pessoa_senha');
                    var confirmacao = document.getElementById('pessoa_confirmacao');
                    if (senha && senha.parentNode && senha.parentNode.parentNode) {
                        senha.parentNode.parentNode.style.display = 'none';
                    }
                    if (confirmacao && confirmacao.parentNode && confirmacao.parentNode.parentNode) {
                        confirmacao.parentNode.parentNode.style.display = 'none';
                    }
                });
            ");
            parent::add($script);
        }

        // Script para visibilidade condicional dos campos
        $scriptVisibilidade = new Element('script');
        $scriptVisibilidade->add("
            function toggleCamposAdvogado() {
                var tipoRadios = document.getElementsByName('tipo');
                var tipoSelecionado = '';
                
                for (var i = 0; i < tipoRadios.length; i++) {
                    if (tipoRadios[i].checked) {
                        tipoSelecionado = tipoRadios[i].value;
                        break;
                    }
                }
                
                // Campos para advogado (OAB, OAB UF, Especialidades)
                var oab = document.getElementById('pessoa_oab');
                var oabUf = document.getElementById('pessoa_oab_uf');
                var especialidades = document.getElementById('pessoa_especialidades');
                
                // Campo para pessoa jurídica (CNPJ)
                var cnpj = document.getElementById('pessoa_cnpj');
                
                // Mostrar/ocultar CNPJ se tipo = J (Jurídica)
                if (cnpj && cnpj.parentNode && cnpj.parentNode.parentNode) {
                    cnpj.parentNode.parentNode.style.display = (tipoSelecionado === 'J') ? 'block' : 'none';
                }
                
                // Mostrar/ocultar OAB e OAB UF se tipo = F (Física/Advogado)
                if (oab && oab.parentNode && oab.parentNode.parentNode) {
                    oab.parentNode.parentNode.style.display = (tipoSelecionado === 'F') ? 'block' : 'none';
                }
                if (oabUf && oabUf.parentNode && oabUf.parentNode.parentNode) {
                    oabUf.parentNode.parentNode.style.display = (tipoSelecionado === 'F') ? 'block' : 'none';
                }
                
                // Mostrar/ocultar Especialidades APENAS se tipo = F (Física/Advogado)
                if (especialidades) {
                    var container = especialidades;
                    while (container && !container.classList.contains('form-group') && container !== document.body) {
                        container = container.parentNode;
                    }
                    if (container) {
                        container.style.display = (tipoSelecionado === 'F') ? 'block' : 'none';
                    } else if (especialidades.parentNode && especialidades.parentNode.parentNode) {
                        especialidades.parentNode.parentNode.style.display = (tipoSelecionado === 'F') ? 'block' : 'none';
                    }
                }
            }
            
            document.addEventListener('DOMContentLoaded', function () {
                // Ocultar especialidades por padrão
                var especialidades = document.getElementById('pessoa_especialidades');
                if (especialidades) {
                    var container = especialidades;
                    while (container && !container.classList.contains('form-group') && container !== document.body) {
                        container = container.parentNode;
                    }
                    if (container) {
                        container.style.display = 'none';
                    } else if (especialidades.parentNode && especialidades.parentNode.parentNode) {
                        especialidades.parentNode.parentNode.style.display = 'none';
                    }
                }
                
                // Aplicar visibilidade inicial baseado no tipo selecionado
                toggleCamposAdvogado();
                
                // Adicionar listeners aos radiobuttons de tipo
                var tipoRadios = document.getElementsByName('tipo');
                for (var i = 0; i < tipoRadios.length; i++) {
                    tipoRadios[i].addEventListener('change', toggleCamposAdvogado);
                }
            });
        ");
        parent::add($scriptVisibilidade);

        $box = new HBox();
        $box->style = 'display: flex; align-items: stretch; width:80%';
        $box->add($painel_foto);
        $box->add($painel_form);
        parent::add($box);
    }

    public function onSave($param)
    {
        try {
            Transaction::open('advogado');

            $dados = $this->form->getData();
            $this->form->setData($dados);

            $isEditMode = !empty($dados->id);

            $senha = isset($dados->senha) ? trim((string) $dados->senha) : '';
            $confirmacao = isset($dados->confirmacao) ? trim((string) $dados->confirmacao) : '';

            $tmpFile = isset($_FILES['foto']['tmp_name']) ? $_FILES['foto']['tmp_name'] : '';
            $originalName = isset($_FILES['foto']['name']) ? $_FILES['foto']['name'] : '';
            $fotoEnviada = (!empty($tmpFile) && file_exists($tmpFile));

            if (!$fotoEnviada) {
                unset($dados->foto);
            }

            unset($dados->confirmacao);

            // Armazenar dados de relacionamento antes de remover
            $ids_grupos = !empty($dados->ids_grupos) ? $dados->ids_grupos : array();
            $ids_especialidades = !empty($dados->ids_especialidades) ? $dados->ids_especialidades : array();

            // Remover campos virtuais que não existem na tabela pessoa
            unset($dados->ids_grupos);
            unset($dados->ids_especialidades);

            // Garantir que ativo tem um valor válido
            if (empty($dados->ativo) || ($dados->ativo !== '0' && $dados->ativo !== 1)) {
                $dados->ativo = '1'; // Padrão: ativo
            }

            // Validar e normalizar cidade_id - deve ser um inteiro válido ou null
            if (empty($dados->cidade_id) || !is_numeric($dados->cidade_id)) {
                $dados->cidade_id = NULL;
            } else {
                $dados->cidade_id = (int) $dados->cidade_id;
            }

            $pessoa = new Pessoa();
            $pessoa->fromArray((array) $dados);

            if ($fotoEnviada) {
                $pessoa->saveFotoUpload($tmpFile, $originalName);
            }

            if ($senha === '') {
                if (!$isEditMode) {
                    new Message('error', 'Informe a senha de acesso.');
                    return;
                }
            } elseif ($senha !== $confirmacao) {
                new Message('error', 'A senha e a confirmacao nao coincidem.');
                return;
            }

            if ($senha !== '') {
                $pessoa->senha = password_hash($senha, PASSWORD_DEFAULT);
            } elseif (!empty($dados->id)) {
                $existente = Pessoa::find($dados->id);
                if ($existente) {
                    $pessoa->senha = $existente->senha;
                }
            }

            $pessoa->store();

            $pessoa->delGrupos();
            if (!empty($ids_grupos)) {
                foreach ($ids_grupos as $id_grupo) {
                    $pessoa->addGrupo(new Grupo($id_grupo));
                }
            }

            $pessoa->delEspecialidade();
            if (!empty($ids_especialidades)) {
                foreach ($ids_especialidades as $id_especialidade) {
                    $pessoa->addEspecialidade(new Especialidade($id_especialidade));
                }
            }

            Transaction::close();
            new Message('info', 'Dados armazenados com sucesso');

            echo "<script language='JavaScript'> window.location = 'index.php?class=PessoaList'; </script>";
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onEdit($param)
    {
        try {
            if (isset($param['id'])) {
                $id = $param['id'];
                Transaction::open('advogado');
                $pessoa = Pessoa::find($id);
                if ($pessoa) {
                    $this->fotoPreview->src = $pessoa->getFotoDataUri();
                    $pessoa->senha = '';
                    $pessoa->ids_grupos = $pessoa->getIdsGrupos();
                    $pessoa->ids_especialidades = $pessoa->getIdsEspecialidades();
                    $this->form->setData($pessoa);
                } else {
                    $this->fotoPreview->src = 'App/Images/Fotos/Default/default.png';
                }
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onCancel($param)
    {
        echo "<script language='JavaScript'> window.location = 'index.php?class=PessoaList'; </script>";
    }
}
