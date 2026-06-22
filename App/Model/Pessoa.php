<?php

    use Advogado\Database\Criteria;
    use Advogado\Database\Record;
    use Advogado\Database\Repository;
    use Advogado\Database\Transaction;

    class Pessoa extends Record
    {
        const TABLENAME = 'pessoa';
        private $cidade;

        public function getCpfLimpo() {
            return preg_replace('/\D/', '', (string) ($this->cpf ?? ''));
        }

        public function get_cidade() {
            if (empty($this->cidade_id)) {
                return NULL;
            }
            if (empty($this->cidade))
                $this->cidade = new Cidade($this->cidade_id);
            return $this->cidade;
            }

        public function get_nome_cidade() {
            if (empty($this->cidade_id)) {
                return NULL;
            }
            if (empty($this->cidade))
                $this->cidade = new Cidade($this->cidade_id);
            return $this->cidade->nome;
            }

        public function addGrupo(Grupo $grupo) {
            $pg = new PessoaGrupo;
            $pg->grupo_id   = $grupo->id;
            $pg->pessoa_id  = $this->id;
            $pg->store();
        }

        public function delGrupos() {
            $criteria = new Criteria();
            $criteria->add('pessoa_id', '=', $this->id);

            $repo = new Repository('PessoaGrupo');
            return $repo->delete($criteria);
        }

        public function addEspecialidade(Especialidade $especialidade) {
            $pe = new PessoaEspecialidade;
            $pe->especialidade_id   = $especialidade->id;
            $pe->pessoa_id  = $this->id;
            $pe->store();
        }

        public function delEspecialidade() {
            $criteria = new Criteria();
            $criteria->add('pessoa_id', '=', $this->id);

            $repo = new Repository('PessoaEspecialidade');
            return $repo->delete($criteria);
        }

        public function delete($id = NULL)
        {
            $this->delGrupos();
            $this->delEspecialidade();
            return parent::delete($id);
        }

        public function getGrupos() {
            $grupos = array();
            $criteria = new Criteria();
            $criteria->add('pessoa_id', '=', $this->id);

            $repo = new Repository('PessoaGrupo');
            $viculos = $repo->load($criteria);

            if ($viculos) {
                foreach ($viculos as $viculo) {
                    $grupos[] = new Grupo($viculo->grupo_id);
                }
            }
            return $grupos;
        }

        public function getEspecialidades() {
            $especialidades = array();
            $criteria = new Criteria();
            $criteria->add('pessoa_id', '=', $this->id);

            $repo = new Repository('PessoaEspecialidade');
            $viculos = $repo->load($criteria);

            if ($viculos) {
                foreach ($viculos as $viculo) {
                    $especialidades[] = new Especialidade($viculo->especialidade_id);
                }
            }
            return $especialidades;
        }

        public function getIdsGrupos() {
            $grupos_ids = array();
            $grupos = $this->getGrupos();

            if ($grupos) {
                foreach ($grupos as $grupo) {
                    $grupos_ids[] = $grupo->id;
                }
            }
            return $grupos_ids;
        }

        public function getIdsEspecialidades() {
            $especialidades_ids = array();
            $especialidades = $this->getEspecialidades();

            if ($especialidades) {
                foreach ($especialidades as $especialidade) {
                    $especialidades_ids[] = $especialidade->id;
                }
            }
            return $especialidades_ids;
        }

        public function getNomeGrupos() {
            $nomes = array();
            $grupos = $this->getGrupos();

            if ($grupos) {
                foreach ($grupos as $grupo) {
                    if (isset($grupo->nome)) {
                        $nomes[] = $grupo->nome;
                    }
                }
            }

            return implode(', ', $nomes);
        }

        public function get_nome_grupos() {
            return $this->getNomeGrupos();
        }

        public function getNomeEspecialidades() {
            $nomes = array();
            $especialidades = $this->getEspecialidades();

            if ($especialidades) {
                foreach ($especialidades as $especialidade) {
                    if (isset($especialidade->nome)) {
                        $nomes[] = $especialidade->nome;
                    }
                }
            }

            return implode(', ', $nomes);
        }

        public function get_nome_especialidades() {
            return $this->getNomeEspecialidades();
        }

        public function getMovimentacoesEmAberto() {
            return Movimentacao::getByPessoa($this->id);
        }

        public static function findByLogin($login)
        {
            $login = trim((string) $login);
            if ($login === '') {
                return null;
            }

            $criteria = new Criteria();
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $criteria->add('email', '=', $login);
            } else {
                $cpf = preg_replace('/\D/', '', $login);
                $criteria->add('cpf', '=', $cpf);
                $criteria->add('email', '=', $login, 'or');
            }

            $repository = new Repository('Pessoa');
            $users = $repository->load($criteria);
            if ($users && count($users) > 0) {
                return $users[0];
            }

            return null;
        }

        public function validatePassword($senha)
        {
            $senha = (string) $senha;
            if (empty($this->senha)) {
                return false;
            }

            if (password_verify($senha, $this->senha)) {
                return true;
            }

            $passwordHash = hash('sha256', $senha);
            return hash_equals((string) $this->senha, $passwordHash);
        }

        public function getPermissoes()
        {
            $permissoes = array();
            $grupos = $this->getGrupos();

            if (!$grupos) {
                return $permissoes;
            }

            foreach ($grupos as $grupo) {
                if (isset($grupo->nome)) {
                    $permissoes = array_merge($permissoes, Auth::permissoesDoGrupo($grupo->nome));
                }
            }

            return array_values(array_unique($permissoes));
        }

        public function getFotoDataUri()
        {
            if (empty($this->foto)) {
                return $this->getFotoUrl();
            }

            $mime = 'image/jpeg';
            if (function_exists('getimagesizefromstring')) {
                $info = @getimagesizefromstring($this->foto);
                if (is_array($info) && !empty($info['mime'])) {
                    $mime = $info['mime'];
                }
            }

            return 'data:' . $mime . ';base64,' . base64_encode($this->foto);
        }

        public function getFotoUrl()
        {
            $cpf = $this->getCpfLimpo();
            if (empty($cpf)) {
                return 'App/Images/Fotos/Default/default.png';
            }

            $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'Fotos';
            $dir = $baseDir . DIRECTORY_SEPARATOR . $cpf;

            if (!is_dir($dir)) {
                return 'App/Images/Fotos/Default/default.png';
            }

            $files = glob($dir . DIRECTORY_SEPARATOR . $cpf . '.*');
            if ($files && isset($files[0])) {
                return 'App/Images/Fotos/' . $cpf . '/' . basename($files[0]);
            }

            return 'App/Images/Fotos/Default/default.png';
        }

        public function getFotoArquivo()
        {
            $cpf = $this->getCpfLimpo();
            if (empty($cpf)) {
                return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'Fotos' . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'default.png';
            }

            $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'Fotos';
            $dir = $baseDir . DIRECTORY_SEPARATOR . $cpf;

            if (!is_dir($dir)) {
                return $baseDir . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'default.png';
            }

            $files = glob($dir . DIRECTORY_SEPARATOR . $cpf . '.*');
            if ($files && isset($files[0])) {
                return $files[0];
            }

            return $baseDir . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'default.png';
        }

        public function get_foto_resumo()
        {
            $foto = htmlspecialchars($this->getFotoDataUri(), ENT_QUOTES, 'UTF-8');
            return '<img src="' . $foto . '" alt="Foto" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">';
        }

        public function saveFotoUpload($tmpFile, $originalName = '')
        {
            if (empty($tmpFile) || !file_exists($tmpFile)) {
                return false;
            }

            $cpf = $this->getCpfLimpo();
            if (empty($cpf)) {
                throw new Exception('CPF e obrigatorio para salvar a foto.');
            }

            $bytes = file_get_contents($tmpFile);
            if ($bytes === false) {
                throw new Exception('Nao foi possivel ler a foto enviada.');
            }

            $mime = 'image/jpeg';
            $ext = 'jpg';

            if (function_exists('getimagesizefromstring')) {
                $info = @getimagesizefromstring($bytes);
                if (is_array($info) && !empty($info['mime'])) {
                    $mime = $info['mime'];
                }
            }

            switch ($mime) {
                case 'image/png':
                    $ext = 'png';
                    break;
                case 'image/gif':
                    $ext = 'gif';
                    break;
                case 'image/webp':
                    $ext = 'webp';
                    break;
                default:
                    if (!empty($originalName)) {
                        $pathInfo = pathinfo($originalName);
                        if (!empty($pathInfo['extension'])) {
                            $ext = strtolower($pathInfo['extension']);
                        }
                    }
                    break;
            }

            $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR . 'Fotos';
            $dir = $baseDir . DIRECTORY_SEPARATOR . $cpf;

            if (!is_dir($dir)) {
                if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                    throw new Exception('Nao foi possivel criar o diretorio da foto.');
                }
            }

            foreach (glob($dir . DIRECTORY_SEPARATOR . $cpf . '.*') as $arquivoAntigo) {
                @unlink($arquivoAntigo);
            }

            $destino = $dir . DIRECTORY_SEPARATOR . $cpf . '.' . $ext;
            if (is_uploaded_file($tmpFile)) {
                if (!move_uploaded_file($tmpFile, $destino)) {
                    throw new Exception('Nao foi possivel gravar a foto no diretorio.');
                }
            } else {
                if (!copy($tmpFile, $destino)) {
                    throw new Exception('Nao foi possivel gravar a foto no diretorio.');
                }
            }

            $this->foto = $bytes;
            return $destino;
        }
    }

