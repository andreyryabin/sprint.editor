<?php

namespace Sprint\Editor\Structure;

class Layout
{
    /**
     * @var array|Column[]
     */
    private $columns = [];
    private $settings = [];

    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    public function toArray()
    {
        $layout = [
            'settings'=> $this->settings,
            'columns' => [],
        ];

        foreach ($this->columns as $column) {
            $layout['columns'][] = $column->toArray();
        }
        return $layout;
    }

    /**
     * @return array|Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Column $column
     *
     * @return Layout
     */
    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
        return $this;
    }
}
