<?php


namespace App\Parser;


use App\Context\ClassContext;
use App\Context\PropertyContext;

class JsonParser
{
    /**
     * Array of arrays that need to become classes
     * @var array
     */
    private $classes = [];

    public function parse(string $className, string $namespace, string $json): array
    {
        $this->classes = [];
        $data = json_decode($json, JSON_OBJECT_AS_ARRAY);
        $this->parseClass($className, $namespace, $data);

        return $this->classes;
    }

    private function parseClass(string $name, string $namespace, array $data): void
    {
        foreach ($data as $key => &$value) {
            if (\is_array($value)) {
                $this->parseClass($key, $namespace, $value);
                $value = $namespace. '\\'.ucfirst($key);
                continue;
            }
        }
        unset($value);
        $class = new ClassContext($name, 'App');
        foreach ($data as $key => &$value) {
            if (strpos($value, $namespace) !== 0) {
                $value = \gettype($value);
            }
            $class->addProperty(new PropertyContext($key, $value));
        }
        unset($value);
        $this->classes[] = $class;
    }
}