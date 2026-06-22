<?php

use Advogado\Control\Page;
use Advogado\Database\Transaction;
use Advogado\Session\Session;

class PerfilPage extends Page
{
    public function show()
    {
        Auth::requireLogin();

        $nome = '';
        $email = '';
        $telefone = '';
        $cpf = '';
        $cnpj = '';
        $rg = '';
        $sexo = '';
        $dtNascimento = '';
        $oab = '';
        $oabUf = '';
        $cargo = '';
        $salario = '';
        $contratadoEm = '';
        $demitidoEm = '';
        $endereco = '';
        $bairro = '';
        $cidade = '';
        $observacoes = '';
        $grupoNomes = array();
        $especialidades = array();
        $foto = 'App/Templates/assets/img/default-avatar.png';

        try {
            Transaction::open('advogado');

            $usuarioId = (int) Session::getValue('usuario_id');
            $usuario = $usuarioId ? new Pessoa($usuarioId) : null;

            if ($usuario) {
                $nome = (string) ($usuario->nome ?? '');
                $email = (string) ($usuario->email ?? '');
                $telefone = (string) ($usuario->telefone ?? '');
                $cpf = (string) ($usuario->cpf ?? '');
                $cnpj = (string) ($usuario->cnpj ?? '');
                $rg = (string) ($usuario->rg ?? '');
                $sexo = (string) ($usuario->sexo ?? '');
                $dtNascimento = (string) ($usuario->dt_nascimento ?? '');
                $oab = (string) ($usuario->oab ?? '');
                $oabUf = (string) ($usuario->oab_uf ?? '');
                $cargo = (string) ($usuario->cargo ?? '');
                $salario = (string) ($usuario->salario ?? '');
                $contratadoEm = (string) ($usuario->contratado_em ?? '');
                $demitidoEm = (string) ($usuario->demitido_em ?? '');
                $endereco = (string) ($usuario->endereco ?? '');
                $bairro = (string) ($usuario->bairro ?? '');
                $cidade = (string) ($usuario->get_nome_cidade() ?? '');
                $observacoes = (string) ($usuario->observacoes ?? '');
                $foto = $usuario->getFotoDataUri();

                foreach ((array) $usuario->getGrupos() as $grupo) {
                    if (isset($grupo->nome)) {
                        $grupoNomes[] = $grupo->nome;
                    }
                }

                foreach ((array) $usuario->getEspecialidades() as $especialidade) {
                    if (isset($especialidade->nome)) {
                        $especialidades[] = $especialidade->nome;
                    }
                }
            }

            Transaction::close();
        } catch (Exception $e) {
            Transaction::rollback();
        }

        $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $telefone = htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8');
        $cpf = htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8');
        $cnpj = htmlspecialchars($cnpj, ENT_QUOTES, 'UTF-8');
        $rg = htmlspecialchars($rg, ENT_QUOTES, 'UTF-8');
        $sexo = htmlspecialchars($sexo, ENT_QUOTES, 'UTF-8');
        $dtNascimento = htmlspecialchars($dtNascimento, ENT_QUOTES, 'UTF-8');
        $oab = htmlspecialchars($oab, ENT_QUOTES, 'UTF-8');
        $oabUf = htmlspecialchars($oabUf, ENT_QUOTES, 'UTF-8');
        $cargo = htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8');
        $salario = htmlspecialchars($salario, ENT_QUOTES, 'UTF-8');
        $contratadoEm = htmlspecialchars($contratadoEm, ENT_QUOTES, 'UTF-8');
        $demitidoEm = htmlspecialchars($demitidoEm, ENT_QUOTES, 'UTF-8');
        $endereco = htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8');
        $bairro = htmlspecialchars($bairro, ENT_QUOTES, 'UTF-8');
        $cidade = htmlspecialchars($cidade, ENT_QUOTES, 'UTF-8');
        $observacoes = htmlspecialchars($observacoes, ENT_QUOTES, 'UTF-8');
        $foto = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');

        $grupoPrincipal = htmlspecialchars($grupoNomes ? implode(', ', $grupoNomes) : 'Usuario', ENT_QUOTES, 'UTF-8');
        $especialidadesTexto = htmlspecialchars($especialidades ? implode(', ', $especialidades) : '-', ENT_QUOTES, 'UTF-8');

        echo '
            <div style="padding:24px 8px; max-width:1080px;">
                <div style="display:flex; gap:24px; align-items:center; flex-wrap:wrap; margin-bottom:24px;">
                    <img src="' . $foto . '" alt="Foto do usuario" style="width:160px; height:160px; object-fit:cover; border-radius:50%; border:4px solid #7e57c2; background:#fff;">
                    <div>
                        <h2 style="margin:0 0 6px 0;">Perfil do usuario</h2>
                        <p style="margin:0; color:#666;">' . $grupoPrincipal . '</p>
                    </div>
                </div>

                <table class="table table-striped">
                    <tr><th style="width:220px;">Nome</th><td>' . ($nome !== '' ? $nome : '-') . '</td></tr>
                    <tr><th>E-mail</th><td>' . ($email !== '' ? $email : '-') . '</td></tr>
                    <tr><th>Telefone</th><td>' . ($telefone !== '' ? $telefone : '-') . '</td></tr>
                    <tr><th>Endereco</th><td>' . ($endereco !== '' ? $endereco : '-') . '</td></tr>
                    <tr><th>Bairro</th><td>' . ($bairro !== '' ? $bairro : '-') . '</td></tr>
                    <tr><th>Cidade</th><td>' . ($cidade !== '' ? $cidade : '-') . '</td></tr>
                    <tr><th>Grupo(s)</th><td>' . $grupoPrincipal . '</td></tr>
                </table>

                <h4 style="margin-top:24px;">Dados pessoais</h4>
                <table class="table table-striped">
                    <tr><th style="width:220px;">CPF</th><td>' . ($cpf !== '' ? $cpf : '-') . '</td></tr>
                    <tr><th>RG</th><td>' . ($rg !== '' ? $rg : '-') . '</td></tr>
                    <tr><th>Sexo</th><td>' . ($sexo !== '' ? $sexo : '-') . '</td></tr>
                    <tr><th>Data de nascimento</th><td>' . ($dtNascimento !== '' ? $dtNascimento : '-') . '</td></tr>
                </table>

                <h4 style="margin-top:24px;">Dados profissionais</h4>
                <table class="table table-striped">
                    <tr><th style="width:220px;">CNPJ</th><td>' . ($cnpj !== '' ? $cnpj : '-') . '</td></tr>
                    <tr><th>OAB</th><td>' . ($oab !== '' ? $oab . ($oabUf !== '' ? '/' . $oabUf : '') : '-') . '</td></tr>
                    <tr><th>Cargo</th><td>' . ($cargo !== '' ? $cargo : '-') . '</td></tr>
                    <tr><th>Salario</th><td>' . ($salario !== '' ? $salario : '-') . '</td></tr>
                    <tr><th>Contratado em</th><td>' . ($contratadoEm !== '' ? $contratadoEm : '-') . '</td></tr>
                    <tr><th>Demitido em</th><td>' . ($demitidoEm !== '' ? $demitidoEm : '-') . '</td></tr>
                    <tr><th>Especialidades</th><td>' . $especialidadesTexto . '</td></tr>
                    <tr><th>Observacoes</th><td>' . ($observacoes !== '' ? $observacoes : '-') . '</td></tr>
                </table>
            </div>
        ';
    }
}
