<?php

namespace Sprint\Editor\Structure;

class Column
{
    /**
     * @var array|Block[]
     */
    private array $blocks = [];
    private array $params;

    public function __construct(array $params = [])
    {
        $this->params = array_merge(
            [
                'css' => '',
            ], $params
        );
    }

    public function toArray(): array
    {
        return $this->params;
    }

    public function addBlock(Block $block): Column
    {
        $this->blocks[] = $block;
        return $this;
    }

    /**
     * @return array|Block[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
    /**
     * @throws StructureException
     */
    public function getBlockByIndex(int $index): Block
    {
        if (isset($this->blocks[$index])) {
            return $this->blocks[$index];
        }
        throw new StructureException("Block with index=\"$index\" not found");
    }
    public function getCss(): string
    {
        return (string)$this->params['css'];
    }

    public function setCss(string $css): Column
    {
        $this->params['css'] = $css;
        return $this;
    }
}
