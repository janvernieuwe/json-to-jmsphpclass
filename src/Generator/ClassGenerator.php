<?php

namespace App\Generator;

use App\Context\ClassContext;
use App\Context\PropertyContext;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use App\Normalizer\NameNormalizer;

class ClassGenerator
{
    /**
     * @param ClassContext $data
     * @return string
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public static function generate(ClassContext $data)
    {
        $generator = new \Zend\Code\Generator\ClassGenerator(ucfirst($data->getName()), $data->getNamespace());
        $generator->addUse('JMS\Serializer\Annotation', 'JMS');
        foreach ($data->getProperties() as $property) {
            $generator->addPropertyFromGenerator(
                PropertyGenerator::fromArray(
                    [
                        'name'             => NameNormalizer::normalizePropertyName(NameNormalizer::normalizePropertyName($property->getName())),
                        'const'            => false,
                        'omitdefaultvalue' => true,
                        'flags'            => 0,
                        'visibility'       => PropertyGenerator::VISIBILITY_PRIVATE,
                        'docblock'         => DocBlockGenerator::fromArray(['tags' => self::parseTags($property)]),
                    ]
                )
            );
            $generator->addMethodFromGenerator(
                MethodGenerator::fromArray(
                    [
                        'name'       => 'get'.ucfirst(NameNormalizer::normalizePropertyName($property->getName())),
                        'parameters' => [],
                        'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                        'body'       => sprintf('return $this->%s;', NameNormalizer::normalizePropertyName($property->getName())),
                        'returntype' => $property->getReturnType(),
                        'docblock'   => DocBlockGenerator::fromArray(
                            [
                                'tags' => [
                                    [
                                        'name'        => 'return',
                                        'description' => $property->getAnnotatedType(),
                                    ],
                                ],
                            ]
                        ),
                    ]
                )
            );
        }
        $fgen = new FileGenerator();
        $fgen->setClass($generator);

        return $fgen->generate();
    }

    /**
     * @param PropertyContext $property
     * @return array
     */
    private static function parseTags(PropertyContext $property): array
    {
        $tags = [
            ['name' => 'var', 'description' => $property->getAnnotatedType()],
            ['name' => sprintf('JMS\\Type("%s")', $property->getType())],
        ];
         if ($property->getName() !== NameNormalizer::normalizePropertyName($property->getName())) {
            $tags[] = ['name' => sprintf('JMS\\SerializedName("%s")', $property->getName())];
         }

        return $tags;
    }
}
