<?php

use Advogado\Database\Record;
use Advogado\Database\Transaction;

class PessoaEspecialidade extends Record
{
    const TABLENAME = 'pessoa_especialidade';

    public function store()
    {
        if (empty($this->data['pessoa_id']) || empty($this->data['especialidade_id'])) {
            throw new Exception('pessoa_id e especialidade_id sao obrigatorios.');
        }

        $conn = Transaction::get();
        if (!$conn) {
            throw new Exception('Nao ha transacao ativa!!');
        }

        $sql = 'INSERT INTO ' . self::TABLENAME . ' (pessoa_id, especialidade_id) VALUES (' .
               (int) $this->data['pessoa_id'] . ', ' . (int) $this->data['especialidade_id'] . ')';

        Transaction::log($sql);
        return $conn->exec($sql);
    }

    public function delete($id = NULL)
    {
        if (empty($this->data['pessoa_id']) || empty($this->data['especialidade_id'])) {
            throw new Exception('pessoa_id e especialidade_id sao obrigatórios.');
        }

        $conn = Transaction::get();
        if (!$conn) {
            throw new Exception('Nao ha transação ativa!!');
        }

        $sql = 'DELETE FROM ' . self::TABLENAME .
               ' WHERE pessoa_id=' . (int) $this->data['pessoa_id'] .
               ' AND especialidade_id=' . (int) $this->data['especialidade_id'];

        Transaction::log($sql);
        return $conn->exec($sql);
    }
}
