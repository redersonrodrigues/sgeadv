<?php
use Advogado\Database\Record;

class Acao extends Record {
    const TABLENAME = 'acao';
    private $tipo_acao;

    public function get_tipo_acao() {
        if (empty($this->tipo_acao)) {
            $this->tipo_acao = new TipoAcao($this->tipo_acao_id);
        }
        return $this->tipo_acao;
    }

    public function get_nome_tipo_acao() {
        if (empty($this->tipo_acao)) {
            $this->tipo_acao = new TipoAcao($this->tipo_acao_id);
        }
        return $this->tipo_acao->nome;
    }
}