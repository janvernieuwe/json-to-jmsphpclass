<?php


namespace App\Context;


class ClassContext
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var PropertyContext[]
     */
    private $properties;

    /**
     * ClassContext constructor.
     * @param string $name
     * @param string $namespace
     * @param PropertyContext[] $properties
     */
    public function __construct(string $name, string $namespace, array $properties = [])
    {
        $this->name = ucfirst($name);
        $this->namespace = $namespace;
        $this->properties = $properties;
    }

    public function __toString()
    {
        return $this->name;
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
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return PropertyContext[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param PropertyContext $property
     * @return ClassContext
     */
    public function addProperty(PropertyContext $property): self
    {
        $this->properties[] = $property;

        return $this;
    }

    public function getFilePath()
    {
        return $this->name.'.php';
    }
}