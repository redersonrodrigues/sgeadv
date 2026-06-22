<?php

use Advogado\Control\Page;
use Advogado\Database\Criteria;
use Advogado\Database\Repository;
use Advogado\Database\Transaction;
use Advogado\Session\Session;

class PerfilPage extends Page
{
    public function show()
    {
        // O perfil so pode ser visto com usuario autenticado.
        if (!Session::getValue('logged')) {
            echo "<script language='JavaScript'> window.location = 'index.php'; </script>";
            return;
        }

        // Fallbacks para manter a tela funcional se a consulta falhar.
        $nome = '';
        $email = '';
        $telefone = '';
        $cpf = '';
        $cnpj = '';
        $oab = '';
        $oabUf = '';
        $valorHora = '';
        $cargo = '';
        $salario = '';
        $contratadoEm = '';
        $demitidoEm = '';
        $endereco = '';
        $bairro = '';
        $cidade = '';
        $observacoes = '';
        $grupoNomes = array();
        $foto = 'App/Templates/assets/img/default-avatar.png';

        try {
            // O Repository depende de transacao ativa.
            Transaction::open('advogado');

            // Busca apenas a pessoa logada.
            $criteria = new Criteria();
            $criteria->add('id', '=', (int) Session::getValue('usuario_id'));

            $repository = new Repository('Pessoa');
            $users = $repository->load($criteria);
            $user = ($users && isset($users[0])) ? $users[0] : null;

            if ($user) {
                // Carrega os dados basicos diretamente do registro da pessoa.
                $nome = (string) ($user->nome ?? '');
                $email = (string) ($user->email ?? '');
                $telefone = (string) ($user->telefone ?? '');
                $cpf = (string) ($user->cpf ?? '');
                $cnpj = (string) ($user->cnpj ?? '');
                $oab = (string) ($user->oab ?? '');
                $oabUf = (string) ($user->oab_uf ?? '');
                $valorHora = (string) ($user->valor_hora ?? '');
                $cargo = (string) ($user->cargo ?? '');
                $salario = (string) ($user->salario ?? '');
                $contratadoEm = (string) ($user->contratado_em ?? '');
                $demitidoEm = (string) ($user->demitido_em ?? '');
                $endereco = (string) ($user->endereco ?? '');
                $bairro = (string) ($user->bairro ?? '');
                $cidade = (string) ($user->get_nome_cidade() ?? '');
                $observacoes = (string) ($user->observacoes ?? '');
                $foto = $user->getFotoDataUri();

                // Usa os grupos reais do usuario para decidir quais blocos exibir.
                $grupos = $user->getGrupos();
                if ($grupos) {
                    foreach ($grupos as $grupo) {
                        if (isset($grupo->nome)) {
                            $grupoNomes[] = $grupo->nome;
                        }
                    }
                }
            }

            Transaction::close();
        } catch (Exception $e) {
            Transaction::rollback();
        }

        // Normaliza os nomes dos grupos para o controle de exibicao.
        $gruposNormalizados = array_map('strtolower', $grupoNomes);

        // Escape dos campos antes da montagem do HTML.
        $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $telefone = htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8');
        $cpf = htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8');
        $cnpj = htmlspecialchars($cnpj, ENT_QUOTES, 'UTF-8');
        $oab = htmlspecialchars($oab, ENT_QUOTES, 'UTF-8');
        $oabUf = htmlspecialchars($oabUf, ENT_QUOTES, 'UTF-8');
        $valorHora = htmlspecialchars($valorHora, ENT_QUOTES, 'UTF-8');
        $cargo = htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8');
        $salario = htmlspecialchars($salario, ENT_QUOTES, 'UTF-8');
        $contratadoEm = htmlspecialchars($contratadoEm, ENT_QUOTES, 'UTF-8');
        $demitidoEm = htmlspecialchars($demitidoEm, ENT_QUOTES, 'UTF-8');
        $endereco = htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8');
        $bairro = htmlspecialchars($bairro, ENT_QUOTES, 'UTF-8');
        $cidade = htmlspecialchars($cidade, ENT_QUOTES, 'UTF-8');
        $observacoes = htmlspecialchars($observacoes, ENT_QUOTES, 'UTF-8');
        $foto = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');

        // Indica o grupo principal da tela para orientar o usuario.
        $grupoPrincipal = !empty($grupoNomes) ? implode(', ', $grupoNomes) : 'Usuario';
        $grupoPrincipal = htmlspecialchars($grupoPrincipal, ENT_QUOTES, 'UTF-8');

        // Define quais blocos adicionais entram conforme o grupo.
        $eAdvogado = in_array('advogado', $gruposNormalizados, true);
        $eCliente = in_array('cliente', $gruposNormalizados, true);
        $eColaborador = in_array('administrador', $gruposNormalizados, true)
            || in_array('secretaria', $gruposNormalizados, true)
            || in_array('recepcionista', $gruposNormalizados, true)
            || in_array('tesoureiro', $gruposNormalizados, true);

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
                    <tr><th style="width:220px;">Nome</th><td>' . $nome . '</td></tr>
                    <tr><th>E-mail</th><td>' . $email . '</td></tr>
                    <tr><th>Telefone</th><td>' . $telefone . '</td></tr>
                </table>
        ';

        if ($eAdvogado) {
            // Bloco especifico para advogado.
            echo '
                <h4 style="margin-top:24px;">Dados do advogado</h4>
                <table class="table table-striped">
                    <tr><th style="width:220px;">CPF</th><td>' . ($cpf !== '' ? $cpf : '-') . '</td></tr>
                    <tr><th>OAB</th><td>' . ($oab !== '' ? $oab . ($oabUf !== '' ? '/' . $oabUf : '') : '-') . '</td></tr>
                    <tr><th>Valor hora</th><td>' . ($valorHora !== '' ? $valorHora : '-') . '</td></tr>
                    <tr><th>Cidade</th><td>' . ($cidade !== '' ? $cidade : '-') . '</td></tr>
                    <tr><th>Observações</th><td>' . ($observacoes !== '' ? $observacoes : '-') . '</td></tr>
                </table>
            ';
        }

        if ($eCliente) {
            // Bloco especifico para cliente.
            echo '
                <h4 style="margin-top:24px;">Dados do cliente</h4>
                <table class="table table-striped">
                    <tr><th style="width:220px;">CPF</th><td>' . ($cpf !== '' ? $cpf : '-') . '</td></tr>
                    <tr><th>CNPJ</th><td>' . ($cnpj !== '' ? $cnpj : '-') . '</td></tr>
                    <tr><th>Endereco</th><td>' . ($endereco !== '' ? $endereco : '-') . '</td></tr>
                    <tr><th>Bairro</th><td>' . ($bairro !== '' ? $bairro : '-') . '</td></tr>
                    <tr><th>Cidade</th><td>' . ($cidade !== '' ? $cidade : '-') . '</td></tr>
                </table>
            ';
        }

        if ($eColaborador && !$eAdvogado && !$eCliente) {
            // Bloco geral para colaborador, recepcionista, tesoureiro e perfis similares.
            echo '
                <h4 style="margin-top:24px;">Dados funcionais</h4>
                <table class="table table-striped">
                    <tr><th style="width:220px;">Cargo</th><td>' . ($cargo !== '' ? $cargo : '-') . '</td></tr>
                    <tr><th>Salário</th><td>' . ($salario !== '' ? $salario : '-') . '</td></tr>
                    <tr><th>Contratado em</th><td>' . ($contratadoEm !== '' ? $contratadoEm : '-') . '</td></tr>
                    <tr><th>Demitido em</th><td>' . ($demitidoEm !== '' ? $demitidoEm : '-') . '</td></tr>
                    <tr><th>Cidade</th><td>' . ($cidade !== '' ? $cidade : '-') . '</td></tr>
                </table>
            ';
        }

        if (!$eAdvogado && !$eCliente && !$eColaborador) {
            // Fallback para grupos ainda nao mapeados em regra especifica.
            echo '
                <h4 style="margin-top:24px;">Dados adicionais</h4>
                <table class="table table-striped">
                    <tr><th style="width:220px;">CPF</th><td>' . ($cpf !== '' ? $cpf : '-') . '</td></tr>
                    <tr><th>CNPJ</th><td>' . ($cnpj !== '' ? $cnpj : '-') . '</td></tr>
                    <tr><th>Endereco</th><td>' . ($endereco !== '' ? $endereco : '-') . '</td></tr>
                    <tr><th>Bairro</th><td>' . ($bairro !== '' ? $bairro : '-') . '</td></tr>
                    <tr><th>Cidade</th><td>' . ($cidade !== '' ? $cidade : '-') . '</td></tr>
                    <tr><th>Observações</th><td>' . ($observacoes !== '' ? $observacoes : '-') . '</td></tr>
                </table>
            ';
        }

        echo '
            </div>
        ';
    }
}
