<?php

namespace Sprint\Editor\Structure;

class Column
{
    /**
     * @var array|Block[]
     */
    private $blocks = [];
    private $params = [];

    public function __construct($params = [])
    {
        $this->params = array_merge(
            [
                'css' => '',
            ], $params
        );
    }

    public function toArray()
    {
        return $this->params;
    }

    /**
     * @param Block $block
     *
     * @return $this
     */
    public function addBlock(Block $block)
    {
        $this->blocks[] = $block;
        return $this;
    }

    /**
     * @return array|Block[]
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->params['css'];
    }

    /**
     * @param string $css
     *
     * @return Column
     */
    public function setCss($css)
    {
        $this->params['css'] = $css;
        return $this;
    }
}
