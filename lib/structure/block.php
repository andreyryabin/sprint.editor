<?php

namespace Sprint\Editor\Structure;

class Block
{
    private array $params;
    private const NO_DATA_KEYS = [
        'name',
        'settings',
        'layout'
    ];

    /**
     * @throws StructureException
     */
    public function __construct(array $params = [])
    {
        $this->params = array_merge(
            [
                'name' => '',
            ], $params
        );

        if (empty($this->params['name'])) {
            throw new StructureException("Block name empty");
        }
    }

    public function toArray(): array
    {
        return $this->params;
    }

    public function getName(): string
    {
        return (string)$this->params['name'];
    }

    public function getSettings(): array
    {
        return (array)($this->params['settings'] ?? []);
    }

    public function setName(string $key): Block
    {
        $this->params['name'] = $key;
        return $this;
    }

    public function getData(): array
    {
        return array_filter($this->params, function ($key) {
            return !in_array($key, self::NO_DATA_KEYS);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Set assoc array with name=>value
     *
     * @throws StructureException
     */
    public function setData(array $data): Block
    {
        foreach ($data as $key => $value) {
            $this->setDataByKey($key, $value);
        }
        return $this;
    }

    /**
     * @throws StructureException
     */
    public function getDataByKey(string $key, $default = null)
    {
        if (in_array($key, self::NO_DATA_KEYS)) {
            throw new StructureException("Bad data key=\"$key\" for block");
        }

        return $this->params[$key] ?? $default;
    }


    /**
     * @throws StructureException
     */
    public function setDataByKey(string $key, $value)
    {
        if (in_array($key, self::NO_DATA_KEYS)) {
            throw new StructureException("Bad data key=\"$key\" for block");
        }

        $this->params[$key] = $value;
    }


}
