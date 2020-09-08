<?php

namespace Sprint\Editor\Structure;

class Structure
{
    /**
     * @var array|Layout[]
     */
    private $layouts = [];

    public function addLayout($settings = [])
    {
        $this->layouts[] = new Layout($settings);
        return $this;
    }

    /**
     * @param array $params
     *
     * @throws StructureException
     * @return $this
     */
    public function addColumn($params = [])
    {
        $this->getLastLayout()->addColumn(
            (new Column($params))
        );
        return $this;
    }

    /**
     * @param array $params
     *
     * @throws StructureException
     * @return $this
     */
    public function addBlock($params = [])
    {
        if (empty($params['name'])) {
            throw new StructureException('block name empty');
        }

        $this->getLastColumn()->addBlock(
            (new Block($params))
        );
        return $this;
    }

    /**
     * make array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [
            'packname' => '',
            'version'  => 2,
            'blocks'   => [],
            'layouts'  => [],
        ];

        foreach ($this->layouts as $lindex => $layout) {
            $data['layouts'][] = $layout->toArray();
            $columns = $layout->getColumns();
            foreach ($columns as $cindex => $column) {
                $blocks = $column->getBlocks();
                foreach ($blocks as $block) {
                    $data['blocks'][] = $block
                        ->setPosition($lindex, $cindex)
                        ->toArray();
                }
            }
        }

        return $data;
    }

    /**
     * Load structure from array
     *
     * @param array $data
     *
     * @throws StructureException
     * @return Structure
     */
    public function fromArray($data = [])
    {
        $data = array_merge(
            [
                'packname' => '',
                'version'  => 2,
                'blocks'   => [],
                'layouts'  => [],
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
     * @param string $json
     *
     * @throws StructureException
     * @return Structure
     */
    public function fromJson($json = '')
    {
        $arr = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new StructureException(json_last_error_msg());
        }

        return $this->fromArray($arr);
    }

    public function toJson()
    {
        return json_encode(
            $this->toArray()
        );
    }

    /**
     * @throws StructureException
     * @return Layout
     */
    private function getLastLayout()
    {
        if (empty($this->layouts)) {
            throw new StructureException('layouts not found');
        }

        return end($this->layouts);
    }

    /**
     * @throws StructureException
     * @return Column
     */
    private function getLastColumn()
    {
        $layout = $this->getLastLayout();
        $columns = $layout->getColumns();

        if (empty($columns)) {
            throw new StructureException('columns not found');
        }

        return end($columns);
    }
}
