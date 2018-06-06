<?php

namespace App\Generator;

use App\Context\ClassContext;
use App\Context\PropertyContext;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class ClassGenerator
{

    /**
     * @param ClassContext $data
     *
     * @return string
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Exception
     */
    public static function generate(ClassContext $data)
    {
        $generator = new \Zend\Code\Generator\ClassGenerator($data->getNormalizedName(), $data->getNamespace());
        $generator->addUse('JMS\Serializer\Annotation', 'JMS');
        foreach ($data->getProperties() as $property) {
            $generator->addPropertyFromGenerator(
                PropertyGenerator::fromArray(
                    [
                        'name'             => $property->getNormalizedName(),
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
                        'name'       => 'get'.ucfirst($property->getNormalizedName()),
                        'parameters' => [],
                        'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                        'body'       => sprintf('return $this->%s;', $property->getNormalizedName()),
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
     *
     * @return array
     *
     * @throws \Exception
     */
    private static function parseTags(PropertyContext $property): array
    {
        $tags = [
            ['name' => 'var', 'description' => $property->getAnnotatedType()],
            ['name' => sprintf('JMS\\Type("%s")', $property->getType())],
        ];
        if ($property->getName() !== $property->getNormalizedName() || preg_match('/[A-Z]/', $property->getName())) {
            $tags[] = ['name' => sprintf('JMS\\SerializedName("%s")', $property->getName())];
        }

        return $tags;
    }
}
