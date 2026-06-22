<?php
namespace Advogado\Core;

class MenuBuilder
{
    /**
     * Monta a barra lateral da area autenticada.
     * O parametro $currentClass define qual item fica ativo.
     */
    public function buildSidebar($currentClass)
    {
        $currentClass = (string) $currentClass;

        return '
            <li class="'.($currentClass === 'HomePage' ? 'active' : '').'">
                <a href="index.php">
                    <i class="fa fa-home"></i>
                    <p>Início</p>
                </a>
            </li>
            <li class="'.($currentClass === 'PerfilPage' ? 'active' : '').'">
                <a href="index.php?class=PerfilPage">
                    <i class="fa fa-user"></i>
                    <p>Perfil</p>
                </a>
            </li>
            <li class="'.(($currentClass === 'PessoaList' || $currentClass === 'PessoaForm') ? 'active' : '').'">
                <a href="index.php?class=PessoaList">
                    <i class="fa fa-users"></i>
                    <p>Usuários</p>
                </a>
            </li>
            <li class="'.($currentClass === 'CidadesFormList' ? 'active' : '').'">
                <a href="index.php?class=CidadesFormList">
                    <i class="fa fa-map-marker"></i>
                    <p>Cidades</p>
                </a>
            </li>
            <li class="'.($currentClass === 'EspecialidadesFormList' ? 'active' : '').'">
                <a href="index.php?class=EspecialidadesFormList">
                    <i class="fa fa-gavel"></i>
                    <p>Especialidades</p>
                </a>
            </li> 
            <li class="'.($currentClass === 'AlterarSenhaForm' ? 'active' : '').'">
                <a href="index.php?class=AlterarSenhaForm">
                    <i class="fa fa-key"></i>
                    <p>Alterar Senha</p>
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="index.php?class=LoginForm&method=onLogout">
                    <i class="fa fa-sign-out"></i>
                    <p>Sair</p>
                </a>
            </li>
        ';
    }

    /**
     * Monta o menu superior com o dropdown Dados.
     */
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
}
