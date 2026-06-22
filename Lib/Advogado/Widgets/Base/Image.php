<?php
namespace Advogado\Widgets\Base;

/**
 * Representa uma imagem
 * @author Réderson A. Rodrigues
 */
class Image extends Element
{
    private $source; // localização da imagem
    
    /**
     * Instancia uma imagem
     * @param $source = localização da imagem
     */
    public function __construct($source)
    {
        parent::__construct('img');
        
        // atribui a localização da imagem
        $this->src = $source;
        $this->border = 0;
    }
}
