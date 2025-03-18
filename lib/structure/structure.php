<?php

namespace Sprint\Editor\Structure;

class Structure
{
    /**
     * @var array|Layout[]
     */
    private array $layouts = [];

    public function addLayout($settings = []): Structure
    {
        $this->layouts[] = new Layout($settings);
        return $this;
    }

    /**
     * @throws StructureException
     */
    public function addColumn($params = []): Structure
    {
        $this->getLastLayout()->addColumn(
            (new Column($params))
        );
        return $this;
    }

    /**
     * @throws StructureException
     */
    public function addBlock(array $params = []): Structure
    {
        $this->getLastLayout()
            ->getLastColumn()
            ->addBlock(
                (new Block($params))
            );

        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'packname' => '',
            'version' => 2,
            'blocks' => [],
            'layouts' => [],
        ];

        foreach ($this->layouts as $lindex => $layout) {
            $data['layouts'][] = $layout->toArray();
            $columns = $layout->getColumns();
            foreach ($columns as $cindex => $column) {
                $blocks = $column->getBlocks();
                foreach ($blocks as $block) {
                    $data['blocks'][] = array_merge(
                        $block->toArray(),
                        ['layout' => $lindex . ',' . $cindex]
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Load structure from array
     * @throws StructureException
     */
    public function fromArray(array $data = []): Structure
    {
        $data = array_merge(
            [
                'packname' => '',
                'version' => 2,
                'blocks' => [],
                'layouts' => [],
            ], $data
        );

        $this->layouts = [];

        foreach ($data['layouts'] as $lindex => $layoutArray) {
            $this->addLayout();
            foreach ($layoutArray['columns'] as $cindex => $columnArray) {
                $this->addColumn($columnArray);
                $position = $lindex . ',' . $cindex;
                foreach ($data['blocks'] as $blockArray) {
                    if ($blockArray['layout'] == $position) {
                        $this->addBlock($blockArray);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @throws StructureException
     */
    public function fromJson(string $json = ''): Structure
    {
        $arr = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new StructureException(json_last_error_msg());
        }

        return $this->fromArray($arr);
    }

    public function toJson(): string
    {
        return json_encode(
            $this->toArray()
        );
    }

    /**
     * @return array|Layout[]
     */
    public function getLayouts(): array
    {
        return $this->layouts;
    }

    /**
     * @return array|Block[]
     */
    public function getBlocks(): array
    {
        $blocks = [];
        foreach ($this->getLayouts() as $layout) {
            array_push($blocks, ...$layout->getBlocks());
        }
        return $blocks;
    }

    /**
     * @throws StructureException
     */
    public function getLastLayout(): Layout
    {
        if (empty($this->layouts)) {
            throw new StructureException("Last layout not found");
        }

        return end($this->layouts);
    }

    /**
     * @throws StructureException
     */
    public function getLayoutByIndex(int $index): Layout
    {
        if (isset($this->layouts[$index])) {
            return $this->layouts[$index];
        }
        throw new StructureException("Layout with index=\"$index\" not found");
    }
}
