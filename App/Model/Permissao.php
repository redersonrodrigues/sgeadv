<?php

    use Advogado\Database\Record;

    class Permissao extends Record
    {
        const TABLENAME = 'permissao';
        const PRIMARYKEY = 'id';
        const IDPOLICY = 'serial';
    }