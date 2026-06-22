<?php

use Advogado\Database\Transaction;
use Advogado\Session\Session;

class Auth
{
    private const GROUP_PERMISSIONS = array(
        'admin' => array('*'),
        'advogado' => array(
            'cliente.visualizar',
            'processo.visualizar',
            'processo.cadastrar',
            'processo.editar',
            'andamento.registrar',
            'documento.anexar',
            'documento.visualizar',
            'audiencia.agendar',
            'audiencia.visualizar',
            'tarefa.visualizar',
            'tarefa.cadastrar',
            'tarefa.editar',
        ),
        'assistente' => array(
            'cliente.visualizar',
            'cliente.cadastrar',
            'cliente.editar',
            'processo.visualizar',
            'documento.anexar',
            'documento.visualizar',
            'audiencia.visualizar',
            'tarefa.visualizar',
            'tarefa.cadastrar',
            'tarefa.editar',
        ),
        'financeiro' => array(
            'movimentacao.visualizar',
            'movimentacao.cadastrar',
            'movimentacao.editar',
        ),
        'estagiario' => array(
            'cliente.visualizar',
            'processo.visualizar',
            'documento.visualizar',
            'audiencia.visualizar',
            'tarefa.visualizar',
        ),
    );

    public static function login($login, $senha)
    {
        $login = trim((string) $login);
        $senha = (string) $senha;

        if ($login === '' || $senha === '') {
            return false;
        }

        $usuario = self::findUserByLogin($login);
        if (!($usuario instanceof Pessoa)) {
            return false;
        }

        if (!self::validatePassword($usuario, $senha)) {
            return false;
        }

        self::setSession($usuario);
        return $usuario;
    }

    public static function logout()
    {
        Session::freeSession();
    }

    public static function estaLogado()
    {
        return (bool) Session::getValue('logged');
    }

    public static function requireLogin()
    {
        if (!self::estaLogado()) {
            echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
            exit;
        }
    }

    public static function requirePermission($permissao)
    {
        self::requireLogin();

        if (!self::temPermissao($permissao)) {
            http_response_code(403);
            echo 'Acesso negado';
            exit;
        }
    }

    public static function getUsuario()
    {
        if (!self::estaLogado()) {
            return null;
        }

        $usuarioId = Session::getValue('usuario_id');
        if (empty($usuarioId)) {
            return null;
        }

        return Pessoa::find($usuarioId);
    }

    public static function getGrupos()
    {
        $grupos = Session::getValue('grupos_usuario');
        return is_array($grupos) ? $grupos : array();
    }

    public static function temGrupo($grupo)
    {
        $grupo = strtolower(trim((string) $grupo));
        foreach (self::getGrupos() as $grupoUsuario) {
            if (strtolower(trim((string) $grupoUsuario)) === $grupo) {
                return true;
            }
        }
        return false;
    }

    public static function permissoes()
    {
        $permissoes = Session::getValue('user_permissions');
        return is_array($permissoes) ? $permissoes : array();
    }

    public static function temPermissao($permissao)
    {
        $permissao = trim((string) $permissao);
        if ($permissao === '') {
            return false;
        }

        if (self::temGrupo('admin')) {
            return true;
        }

        return in_array($permissao, self::permissoes(), true);
    }

    public static function permissoesPorGrupos(array $grupos)
    {
        $permissoes = array();

        foreach ($grupos as $grupo) {
            $grupo = strtolower(trim((string) $grupo));
            if ($grupo === '' || !isset(self::GROUP_PERMISSIONS[$grupo])) {
                continue;
            }

            foreach (self::GROUP_PERMISSIONS[$grupo] as $permissao) {
                $permissoes[$permissao] = $permissao;
            }
        }

        return array_values($permissoes);
    }

    public static function permissoesDoGrupo($grupo)
    {
        $grupo = strtolower(trim((string) $grupo));
        if ($grupo === '' || !isset(self::GROUP_PERMISSIONS[$grupo])) {
            return array();
        }

        return self::GROUP_PERMISSIONS[$grupo];
    }

    public static function setSession(Pessoa $usuario)
    {
        $grupos = $usuario->getGrupos();
        $grupoNomes = array();

        foreach ($grupos as $grupo) {
            if (isset($grupo->nome)) {
                $grupoNomes[] = $grupo->nome;
            }
        }

        $permissoes = self::permissoesPorGrupos($grupoNomes);

        Session::setValue('logged', true);
        Session::setValue('usuario_id', $usuario->id);
        Session::setValue('nome_usuario', $usuario->nome);
        Session::setValue('grupo_usuario', $grupoNomes ? implode(', ', $grupoNomes) : '');
        Session::setValue('grupos_usuario', $grupoNomes);
        Session::setValue('user_permissions', $permissoes);
    }

    private static function findUserByLogin($login)
    {
        if ($conn = Transaction::get()) {
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $sql = 'SELECT * FROM pessoa WHERE email = :email LIMIT 1';
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':email', $login);
            } else {
                $cpf = preg_replace('/\D/', '', $login);
                $sql = 'SELECT * FROM pessoa WHERE cpf = :cpf LIMIT 1';
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':cpf', $cpf);
            }
            $stmt->execute();

            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($data) {
                $usuario = new Pessoa;
                $usuario->fromArray($data);
                return $usuario;
            }
        }

        return false;
    }

    private static function validatePassword(Pessoa $usuario, $senha)
    {
        if (empty($usuario->senha)) {
            return false;
        }

        if (password_verify($senha, $usuario->senha)) {
            return true;
        }

        $legacyHash = hash('sha256', $senha);
        return hash_equals((string) $usuario->senha, $legacyHash);
    }
}
