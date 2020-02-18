<?php


namespace Sprint\Editor\Structure;


class Layout
{
    /**
     * @var array|Column[]
     */
    private $columns = [];
    private $params = [];

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function toArray()
    {
        $layout = [
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
     * @return Layout
     */
    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
        return $this;
    }

}
