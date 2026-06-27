<?php
namespace Advogado\Core;

class MenuBuilder
{
    public function buildSidebar($currentClass)
    {
        $currentClass = (string) $currentClass;
        $items = array();

        $items[] = $this->renderItem('HomePage', $currentClass, 'index.php', 'fa fa-home', 'Inicio');
        $items[] = $this->renderItem('PerfilPage', $currentClass, 'index.php?class=PerfilPage', 'fa fa-user', 'Perfil');

        if (class_exists('\Auth') && \Auth::temPermissao('usuario.gerenciar')) {
            $items[] = $this->renderItem(
                array('PessoaList', 'PessoaForm'),
                $currentClass,
                'index.php?class=PessoaList',
                'fa fa-user-circle-o',
                'Usuarios'
            );
        }

        if (class_exists('\Auth') && \Auth::temPermissao('grupo.gerenciar')) {
            $items[] = $this->renderItem(
                    'GrupoFormList',
                $currentClass,
                'index.php?class=GrupoFormList',
                'fa fa-group',
                'Grupos'
            );
        }

        if (class_exists('\Auth') && \Auth::temPermissao('cidades.visualizar')) {
            $items[] = $this->renderItem(
                'CidadesFormList',
                $currentClass,
                'index.php?class=CidadesFormList',
                'fa fa-map-marker',
                'Cidades'
            );
        }

        if (class_exists('\Auth') && \Auth::temPermissao('especialidade.visualizar')) {
            $items[] = $this->renderItem(
                'EspecialidadesFormList',
                $currentClass,
                'index.php?class=EspecialidadesFormList',
                'fa fa-gavel',
                'Especialidades'
            );
        }

        $items[] = $this->renderItem('AlterarSenhaForm', $currentClass, 'index.php?class=AlterarSenhaForm', 'fa fa-key', 'Alterar Senha');
        $items[] = '<li class="divider"></li>';
        $items[] = '
            <li>
                <a href="index.php?class=LoginForm&method=onLogout">
                    <i class="fa fa-sign-out"></i>
                    <p>Sair</p>
                </a>
            </li>
        ';

        return implode("\n", $items);
    }

    public function buildTopbar()
    {
        return '
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-user"></i>
                    <p>Dados</p>
                    <b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="index.php?class=PerfilPage">Perfil</a></li>
                    <li><a href="index.php?class=AlterarFotoForm">Alterar Foto</a></li>
                    <li><a href="index.php?class=AlterarSenhaForm">Alterar Senha</a></li>
                </ul>
            </li>
            <li>
                <a href="index.php?class=LoginForm&method=onLogout">
                    <i class="fa fa-sign-out"></i>
                    <p>Sair</p>
                </a>
            </li>
        ';
    }

    private function renderItem($matchClass, $currentClass, $href, $icon, $label)
    {
        $classes = is_array($matchClass) ? $matchClass : array($matchClass);
        $active = in_array($currentClass, $classes, true) ? 'active' : '';

        return '
            <li class="' . $active . '">
                <a href="' . $href . '">
                    <i class="' . $icon . '"></i>
                    <p>' . $label . '</p>
                </a>
            </li>
        ';
    }
}
