<?php
namespace Advogado\Widgets\Container;

use Advogado\Widgets\Base\Element;

/**
 * Caixa vertical
 * @author Réderson A. Rodrigues
 */
class VBox extends Element
{
    /**
     * Método construtor
     */
    public function __construct()
    {
        parent::__construct('div');
        $this->{'style'} = 'display: inline-block';
    }
    
    /**
     * Adiciona um elemento filho
     * @param $child Objeto filho
     */
    public function add($child)
    {
        $wrapper = new Element('div');
        $wrapper->{'style'} = 'clear:both';
        $wrapper->add($child);
        parent::add($wrapper);
        return $wrapper;
    }
}
