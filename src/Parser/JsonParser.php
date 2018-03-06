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
        $data = json_decode($json);
        $this->parseClass($className, $namespace, $data);

        return $this->classes;
    }

    /**
     * @param string $name
     * @param string $namespace
     * @param \stdClass $data
     */
    private function parseClass(string $name, string $namespace, \stdClass $data): void
    {
        foreach ($data as $key => &$value) {
            // Recursively add sub classes
            if (\is_object($value)) {
                $this->parseClass($key, $namespace, $value);
                $value = $namespace.'\\'.ucfirst($key);
                continue;
            }
            // Detect array of a class
            if (\is_array($value)) {
                if (!\count($value)) {
                    continue;
                }
                $key = rtrim($key, 's');
                $this->parseClass($key, $namespace, $value[0]);
                $value = $namespace.'\\'.ucfirst($key);
                $value = sprintf('array<%s>', $value);
                continue;
            }
        }
        unset($value);
        // Parse a single class
        $class = new ClassContext($name, 'App');
        foreach ($data as $key => &$value) {
            $value = $this->getType($value, $namespace);
            $class->addProperty(new PropertyContext($key, $value));
        }
        unset($value);
        $this->classes[] = $class;
    }

    protected function getType($value, string $namespace): string
    {
        if (\is_string($value) && strpos($value, $namespace) === 0) {
            return $value;
        }
        if (\is_string($value) && stripos($value, 'array<') === 0) {
            return $value;
        }
        $value = \gettype($value);
        if ($value === 'integer') {
            $value = 'int';
        }

        return $value;
    }
}