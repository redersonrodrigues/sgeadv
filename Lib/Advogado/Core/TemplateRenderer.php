<?php
namespace Advogado\Core;

use Exception;

class TemplateRenderer
{
    private $baseDir;
    private $partialsDir;

    public function __construct($baseDir = 'App/Templates', $partialsDir = '_partials')
    {
        // Define o diretorio base dos templates reutilizaveis.
        $this->baseDir = rtrim($baseDir, '/\\');
        $this->partialsDir = trim($partialsDir, '/\\');
    }

    public function render($template, array $vars = array())
    {
        // Le o arquivo HTML e substitui os marcadores pelo contexto recebido.
        $html = $this->read($template);
        return $this->replace($html, $vars);
    }

    public function renderLayout($content, array $vars = array(), $authenticated = false)
    {
        // A area autenticada usa o shell completo; a publicacao usa apenas o login.
        $template = $authenticated ? 'template.html' : 'login.html';

        // O conteudo da pagina vai sempre para o placeholder central do template.
        $vars['content'] = $content;

        if ($authenticated) {
            // Cada partial recebe o mesmo contexto para montar a interface final.
            $vars['head']       = $this->renderPartial('head.html', $vars);
            $vars['sidebar']    = $this->renderPartial('sidebar.html', $vars);
            $vars['topbar']     = $this->renderPartial('topbar.html', $vars);
            $vars['footer']     = $this->renderPartial('footer.html', $vars);
        }

        // Retorna o HTML final ja pronto para envio ao navegador.
        return $this->render($template, $vars);
    }

    private function renderPartial($template, array $vars = array())
    {
        // Partials vivem em App/Templates/_partials para manter o layout organizado.
        $path = $this->partialsDir . DIRECTORY_SEPARATOR . ltrim($template, '/\\');
        return $this->replace($this->read($path), $vars);
    }

    private function read($file)
    {
        // Resolve o caminho absoluto do recurso dentro da base configurada.
        $path = $this->baseDir . DIRECTORY_SEPARATOR . ltrim($file, '/\\');
        if (!file_exists($path)) {
            throw new Exception("Template not found: {$file}");
        }

        // O arquivo e lido como texto puro para manter os templates simples.
        return file_get_contents($path);
    }

    private function replace($html, array $vars)
    {
        // Substitui cada placeholder {chave} pelo valor já preparado pelo PHP.
        foreach ($vars as $name => $value) {
            $html = str_replace('{' . $name . '}', $value, $html);
        }

        // Devolve o HTML final sem outras regras escondidas.
        return $html;
    }
}
