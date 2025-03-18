<?php

namespace Sprint\Editor\Structure;

class Layout
{
    /**
     * @var array|Column[]
     */
    private array $columns = [];
    private array $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function toArray(): array
    {
        $layout = [
            'settings' => $this->settings,
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
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array|Block[]
     */
    public function getBlocks(): array
    {
        $blocks = [];
        foreach ($this->getColumns() as $column) {
            array_push($blocks, ...$column->getBlocks());
        }
        return $blocks;
    }

    public function addColumn(Column $column): Layout
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * @throws StructureException
     */
    public function getLastColumn(): Column
    {
        if (empty($this->columns)) {
            throw new StructureException("Last column not found");
        }

        return end($this->columns);
    }

    /**
     * @throws StructureException
     */
    public function getColumnByIndex(int $index): Column
    {
        if (isset($this->columns[$index])) {
            return $this->columns[$index];
        }
        throw new StructureException("Column with index=\"$index\" not found");
    }
}
