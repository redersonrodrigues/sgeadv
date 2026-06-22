<?php
namespace Advogado\Core;

use Advogado\Database\Transaction;
use Exception;

class LayoutContextBuilder
{
    private $menuBuilder;

    public function __construct(MenuBuilder $menuBuilder)
    {
        // Recebe o builder de navegacao para nao duplicar regra de menu aqui.
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * Monta o contexto usado no layout autenticado.
     * O index.php fornece apenas os dados brutos da sessao; aqui ficam
     * a revalidacao do grupo e a montagem dos blocos de navegacao.
     */
    public function buildAuthenticatedContext($currentClass, array $session = array())
    {
        // Parte do estado guardado na sessao e prepara a interface autenticada.
        $grupoUsuario = (string) ($session['grupo_usuario'] ?? '');
        $nomeUsuario = htmlspecialchars((string) ($session['nome_usuario'] ?? ''), ENT_QUOTES, 'UTF-8');
        $usuarioId = $session['usuario_id'] ?? null;

        // Recarrega o grupo no banco para manter a interface sincronizada com o estado real.
        if (!empty($usuarioId)) {
            try {
                // A abertura da transacao garante consistencia ao consultar o usuario logado.
                Transaction::open('advogado');

                // Carrega o usuario atual para reconstruir os grupos permitidos.
                $usuario = new \Pessoa($usuarioId);
                $grupos = $usuario ? $usuario->getGrupos() : array();

                if ($grupos) {
                    $nomes = array();

                    // Junta os nomes dos grupos para exibir um texto unico no layout.
                    foreach ($grupos as $grupo) {
                        if (isset($grupo->nome)) {
                            $nomes[] = $grupo->nome;
                        }
                    }

                    if ($nomes) {
                        $grupoUsuario = implode(', ', $nomes);
                    }
                }

                // Fecha a leitura do banco depois de montar o contexto.
                Transaction::close();
            } catch (Exception $e) {
                // Em erro, desfaz a transacao e preserva o grupo vindo da sessao.
                Transaction::rollback();
            }
        }

        // Retorna o valor bruto para a sessao e o valor escapado para o template.
        return array(
            'grupo_usuario_value' => $grupoUsuario,
            'grupo_usuario' => htmlspecialchars($grupoUsuario, ENT_QUOTES, 'UTF-8'),
            'nome_usuario' => $nomeUsuario,
            // O menu lateral depende da pagina atual para marcar o item ativo.
            'sidebar_menu' => $this->menuBuilder->buildSidebar($currentClass),
            // O menu superior permanece fixo nesta fase da aplicacao.
            'topbar_menu' => $this->menuBuilder->buildTopbar(),
        );
    }
}
