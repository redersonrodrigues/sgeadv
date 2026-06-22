<?php
date_default_timezone_set('America/Sao_Paulo');

// Bootstrap do framework local.
require_once 'Lib/Advogado/Core/ClassLoader.php';
$al = new Advogado\Core\ClassLoader;
$al->addNamespace('Advogado', 'Lib/Advogado');
$al->register();

// Bootstrap das classes da aplicação.
require_once 'Lib/Advogado/Core/AppLoader.php';
$al = new Advogado\Core\AppLoader;
$al->addDirectory('App/Control');
$al->addDirectory('App/Model');
$al->register();

// Bootstrap de dependencias externas.
$loader = require 'vendor/autoload.php';
$loader->register();

use Advogado\Session\Session;
use Advogado\Core\TemplateRenderer;
use Advogado\Core\MenuBuilder;
use Advogado\Core\LayoutContextBuilder;

// Inicializa a sessao antes de qualquer leitura de contexto.
$content = '';
new Session;

// Classes publicas que podem ser acessadas sem autenticacao.
$public_classes = array('RecuperarSenhaControl', 'LoginForm');

// Resolve a classe efetiva da requisição.
$class = '';
if (isset($_GET['class']) && is_string($_GET['class']) && (Session::getValue('logged') || in_array($_GET['class'], $public_classes, true))) {
    $class = $_GET['class'];
} else {
    $class = Session::getValue('logged') ? 'HomePage' : 'LoginForm';
}

// Services de renderizacao e navegacao.
$templateRenderer = new TemplateRenderer(__DIR__ . '/App/Templates');
$menuBuilder = new MenuBuilder();
$layoutContextBuilder = new LayoutContextBuilder($menuBuilder);

$layoutVars = array(
    'class' => htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8'),
);
if (Session::getValue('logged')) {
    // O builder devolve o contexto pronto para renderizacao e o valor bruto para a sessao.
    $layoutContext = $layoutContextBuilder->buildAuthenticatedContext($class, array(
        'usuario_id' => Session::getValue('usuario_id'),
        'nome_usuario' => Session::getValue('nome_usuario'),
        'grupo_usuario' => Session::getValue('grupo_usuario'),
    ));

    Session::setValue('grupo_usuario', $layoutContext['grupo_usuario_value']);
    unset($layoutContext['grupo_usuario_value']);

    $layoutVars = array_merge($layoutVars, $layoutContext);
}

// Executa a pagina resolvida e captura somente o HTML do conteudo.
if (!empty($class) && class_exists($class))
{
    try
    {
        $pagina = new $class;
        ob_start();
        $pagina->show();
        $content = ob_get_contents();
        ob_end_clean();
    }
    catch (Exception $e)
    {
        $content = $e->getMessage() . '<br>' . $e->getTraceAsString();
    }
}
else if (!empty($class))
{
    $content = "Class <b>{$class}</b> not found";
}

// Render final: o template renderer decide qual shell usar com base na autenticacao.
$output = $templateRenderer->renderLayout(
    $content,
    $layoutVars,
    (bool) Session::getValue('logged')
);

echo $output;
