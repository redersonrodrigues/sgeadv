<?php
use Advogado\Database\Connection;

class RecuperarSenha
{
    // Garante que a tabela de tokens exista
    public static function ensureTable()
    {
        $conn = Connection::open('advogado');
        $sql = "CREATE TABLE IF NOT EXISTS recuperar_senhas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            usuario_email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL,
            created_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $conn->exec($sql);
    }

    // Cria um token e persiste
    public static function createToken($email)
    {
        $conn = Connection::open('advogado');
        $stmt = $conn->prepare('SELECT id, nome, usuario FROM usuarios WHERE usuario = :email LIMIT 1');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return false;
        }

        self::ensureTable();
        $token = bin2hex(random_bytes(16));

        $ins = $conn->prepare('INSERT INTO recuperar_senhas (usuario_id, usuario_email, token, created_at) VALUES (:uid, :email, :token, :created_at)');
        $ins->bindValue(':uid', $user['id']);
        $ins->bindValue(':email', $email);
        $ins->bindValue(':token', $token);
        $ins->bindValue(':created_at', date('Y-m-d H:i:s'));
        $ins->execute();

        return ['token' => $token, 'nome' => $user['nome'], 'id' => $user['id']];
    }

    // Recupera registro pelo token (verifica expiração)
    public static function findByToken($token, $expiry_seconds = 3600)
    {
        $conn = Connection::open('advogado');
        self::ensureTable();
        $stmt = $conn->prepare('SELECT * FROM recuperar_senhas WHERE token = :token AND used = 0 LIMIT 1');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $created = strtotime($row['created_at']);
        if ((time() - $created) > $expiry_seconds) {
            return false;
        }
        return $row;
    }

    public static function consumeToken($token)
    {
        $conn = Connection::open('advogado');
        $stmt = $conn->prepare('UPDATE recuperar_senhas SET used = 1 WHERE token = :token');
        $stmt->bindValue(':token', $token);
        $stmt->execute();
    }

    public static function updatePassword($user_id, $new_password)
    {
        $conn = Connection::open('advogado');
        $stmt = $conn->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
        $stmt->bindValue(':senha', md5($new_password));
        $stmt->bindValue(':id', $user_id);
        $stmt->execute();
    }
}
