<?php

namespace App\Context;

class PropertyContext
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * PropertyContext constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getReturnType(): string
    {
        if (strpos($this->type, 'array<') === 0) {
            return 'array';
        }

        return $this->type;
    }

    /**
     * @return string
     */
    private function getUseType(): string
    {
        return str_replace(['array<', '>'], '', $this->type);
    }

    /**
     * @return string
     */
    public function getAnnotatedType(): string
    {
        if (strpos($this->type, 'array<') === 0) {
            return '\\'.$this->getUseType().'[]';
        }
        if (strpos($this->type, '\\') !== false) {
            return '\\'.$this->type;
        }

        return $this->type;
    }
}
