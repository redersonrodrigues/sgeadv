<?php

    use Advogado\Database\Criteria;
    use Advogado\Database\Record;
    use Advogado\Database\Repository;

    class Movimentacao extends Record
    {
        const TABLENAME = 'movimentacao';
        private $cliente;

        public function get_cliente() {
            $clientes = array();
            $criteria = new Criteria();
            $criteria->add('pessoa_id', '=', $this->id);
            $criteria->add('grupo_id', '=', 4, 'and','');

            $repo = new Repository('PessoaGrupo');
            $vinculos = $repo->load($criteria);

            if ($vinculos) {
                foreach ($vinculos as $vinculo) {
                    $clientes[] = new Pessoa($vinculo->pessoa_id);
                }
            }
            return $clientes;
        }

        public static function getByPessoa($pessoa_id)
        {
            $criteria = new Criteria();
            $criteria->add('status', '<>', 'Paga');
            $criteria->add('status', '<>', 'Cancelada', 'or');
            $criteria->add('pessoa_id', '=', $pessoa_id);

            $repo = new Repository('Movimentacao');
            return $repo->load($criteria);
        }

    }