<?php

use Advogado\Database\Record;
use Advogado\Database\Transaction;

class PessoaGrupo extends Record
{
    const TABLENAME = 'pessoa_grupo';

    public function store()
    {
        if (empty($this->data['pessoa_id']) || empty($this->data['grupo_id'])) {
            throw new Exception('pessoa_id e grupo_id sao obrigatorios.');
        }

        $conn = Transaction::get();
        if (!$conn) {
            throw new Exception('Nao ha transacao ativa!!');
        }

        $sql = 'INSERT INTO ' . self::TABLENAME . ' (pessoa_id, grupo_id) VALUES (' .
               (int) $this->data['pessoa_id'] . ', ' . (int) $this->data['grupo_id'] . ')';

        Transaction::log($sql);
        return $conn->exec($sql);
    }

    public function delete($id = NULL)
    {
        if (empty($this->data['pessoa_id']) || empty($this->data['grupo_id'])) {
            throw new Exception('pessoa_id e grupo_id sao obrigatorios.');
        }

        $conn = Transaction::get();
        if (!$conn) {
            throw new Exception('Nao ha transacao ativa!!');
        }

        $sql = 'DELETE FROM ' . self::TABLENAME .
               ' WHERE pessoa_id=' . (int) $this->data['pessoa_id'] .
               ' AND grupo_id=' . (int) $this->data['grupo_id'];

        Transaction::log($sql);
        return $conn->exec($sql);
    }
}
