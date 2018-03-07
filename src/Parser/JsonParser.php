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

    /**
     * @param string $className
     * @param string $namespace
     * @param string $json
     * @return array
     */
    public function parse(string $className, string $namespace, string $json): array
    {
        $this->classes = [];
        $data = json_decode($json);

        if (\is_array($data)) {
            $this->parseClass($className, $namespace, $data[0]);

            return $this->classes;
        }
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
                $key = $this->ensureUniqueKey($key);
                $this->parseClass($key, $namespace, $value);
                $value = $namespace.'\\'.ucfirst($key);
                continue;
            }
            // Detect array of a class
            if (\is_array($value)) {
                if (!\count($value)) {
                    continue;
                }
                if (!\is_object($value[0])) {
                    continue;
                }
                $key = rtrim($key, 's');
                $key = $this->ensureUniqueKey($key);
                $this->parseClass($key, $namespace, $value[0]);
                $value = $namespace.'\\'.ucfirst($key);
                $value = sprintf('array<%s>', $value);
                continue;
            }
        }
        unset($value);
        // Parse a single class
        $class = new ClassContext($name, $namespace);
        foreach ($data as $key => &$value) {
            $value = $this->getType($value, $namespace);
            $class->addProperty(new PropertyContext($key, $value));
        }
        unset($value);
        $this->classes[$class->getName()] = $class;
    }

    /**
     * @param $value
     * @param string $namespace
     * @return string
     */
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

    /**
     * @param string $key
     * @return string
     */
    protected function ensureUniqueKey(string $key): string
    {
        if (array_key_exists($key, $this->classes)) {
            $key .= sha1(uniqid('_', true));
        }

        return $key;
    }
}