<?php

namespace App\Generator;

use App\Context\ClassContext;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class ClassGenerator
{
    public static function generate(ClassContext $data)
    {
        $generator = new \Zend\Code\Generator\ClassGenerator(ucfirst($data->getName()), $data->getNamespace());
        $generator->addUse('JMS\Serializer\Annotation', 'JMS');
        foreach ($data->getProperties() as $property) {
            if (strpos($property->getType(), '\\') !== false) {
                $generator->addUse($property->getType());
            }
            $propertyGenerator = PropertyGenerator::fromArray(
                [
                    'name'             => $property->getName(),
                    'const'            => false,
                    'omitdefaultvalue' => true,
                    'flags'            => 0,
                    'visibility'       => PropertyGenerator::VISIBILITY_PRIVATE,
                    'docblock'         => DocBlockGenerator::fromArray(
                        [
                            'tags' => [
                                ['name' => 'var', 'description' => $property->getType()],
                                ['name' => sprintf('JMS\\SerializedName("%s")', $property->getName())],
                                ['name' => sprintf('JMS\\Type("%s")', $property->getType())],
                            ],
                        ]
                    ),
                ]
            );

            $generator->addPropertyFromGenerator($propertyGenerator);
            $generator->addMethodFromGenerator(
                MethodGenerator::fromArray(
                    [
                        'name'       => 'get'.ucfirst($property->getName()),
                        'parameters' => [],
                        'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                        'body'       => sprintf('return $this->%s;', $property->getName()),
                        'returntype' => $property->getType(),
                        'docblock'   => DocBlockGenerator::fromArray(
                            [
                                'tags' => [
                                    [
                                        'name'        => 'return',
                                        'description' => $property->getType(),
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
}
