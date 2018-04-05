<?php

namespace App\Normalizer;

/**
 * Class NameNormalizer
 *
 * @package App\Normalizer
 */
class NameNormalizer
{

    private const PREG_VALID_PHP_LABEL = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';

    /**^
     * @param string $name
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function normalizePropertyName(string $name): string
    {
        preg_match_all('/_/', $name, $matches, PREG_OFFSET_CAPTURE, 1);
        foreach ($matches[0] as $match) {
            $position = $match[1];
            $name[$position + 1] = strtoupper($name[$position + 1]);
        }
        $name = str_replace('_', '', $name);
        $name[0] = strtolower($name[0]);

        if (!preg_match(self::PREG_VALID_PHP_LABEL, $name)) {
            throw new \Exception(sprintf('Property name %s is not a valid PHP label',
              $name));
        }

        return $name;
    }
}
