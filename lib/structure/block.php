<?php

namespace Sprint\Editor\Structure;

class Block
{
    private $params = [];

    public function __construct($params = [])
    {
        $this->params = array_merge(
            [
                'name'   => '',
                'layout' => '0,0',
            ], $params
        );
    }

    public function toArray()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->params['name'];
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->params['layout'];
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->params['name'] = $name;
        return $this;
    }

    /**
     * @param $layoutIndex int
     * @param $columnIndex int
     *
     * @return $this
     */
    public function setPosition($layoutIndex, $columnIndex)
    {
        $layoutIndex = (int)$layoutIndex;
        $columnIndex = (int)$columnIndex;

        $this->params['layout'] = $layoutIndex . ',' . $columnIndex;
        return $this;
    }
}
