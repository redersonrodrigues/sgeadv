<?php

use Advogado\Control\Page;
use Advogado\Database\Transaction;
use Advogado\Session\Session;

class HomePage extends Page
{
    public function show()
    {
        // Esta tela e exclusiva de usuario autenticado.
        Auth::requireLogin();

        // Valores de fallback vindos da sessao para evitar tela incompleta.
        $nome = (string) (Session::getValue('nome_usuario') ?? '');
        $grupo = (string) (Session::getValue('grupo_usuario') ?? 'Usuário');
        $foto = 'App/Templates/assets/img/default-avatar.png';

        try {
            // Recarrega os dados do usuario para exibir informacoes atualizadas.
            Transaction::open('advogado');
            $user = new Pessoa(Session::getValue('usuario_id'));
            if ($user) {
                // Se houver dados novos no banco, eles substituem os valores da sessao.
                $nome = (string) ($user->nome ?? $nome);
                $foto = $user->getFotoDataUri();
            }
            // Fecha a leitura quando a consulta termina sem erro.
            Transaction::close();
        } catch (Exception $e) {
            // Mantem os valores de fallback se houver falha na leitura.
            Transaction::rollback();
        }

        // Escapa os valores antes de imprimir HTML.
        $nome = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $grupo = htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8');
        $foto = htmlspecialchars($foto, ENT_QUOTES, 'UTF-8');

        // Conteudo de boas-vindas com foto, logo e grupo atual.
        echo '
            <div style="padding:24px 8px; max-width:1100px;">
                <div style="display:flex; gap:28px; align-items:center; flex-wrap:wrap;">
                    <div>
                        <img src="' . $foto . '" alt="Foto do usuário" style="width:180px; height:180px; object-fit:cover; border-radius:50%; border:4px solid #7e57c2; background:#fff;">
                    </div>
                    <div>
                        <img src="App/Templates/assets/img/procopio_logo.png" alt="Logo da empresa" style="max-width:260px; height:auto; margin-bottom:18px;">
                        <h2 style="margin:0 0 8px 0;">Bem-vindo(a), Sr(a). ' . $nome . '</h2>
                        <p style="margin:0 0 10px 0;">Seu acesso foi autenticado com sucesso. Esta é sua Área de trabalho no sistema.</p>
                        <p style="margin:0;">Grupo atual: ' . $grupo . '</p>
                    </div>
                </div>
            </div>
        ';
    }
}
