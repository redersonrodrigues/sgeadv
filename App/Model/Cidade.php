<?php
use Advogado\Database\Record;

class Cidade extends Record {
    const TABLENAME = 'cidade';
    private $estado;

    public function get_estado() {
        if (empty($this->estado)) {
            $this->estado = new Estado($this->estado_id);
        }
        return $this->estado;
    }

    public function get_nome_estado(){
        if (empty($this->estado_id)) {
            return NULL;
        }
        if (empty($this->estado)) {
            $this->estado =  new Estado($this->estado_id);
        }
        return $this->estado->nome;
    }
}
