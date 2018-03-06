<?php

namespace App\Generator;

use App\Context\ClassContext;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

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
//            if (strpos($property->getType(), '\\') !== false) {
//                $generator->addUse($property->getUseType());
//            }
            $tags = [
                ['name' => 'var', 'description' => $property->getAnnotatedType()],
                ['name' => sprintf('JMS\\Type("%s")', $property->getType())],
            ];
            if (strtolower($property->getName()) !== $property->getName()) {
                $tags[] = ['name' => sprintf('JMS\\SerializedName("%s")', $property->getName())];
            }

            $generator->addPropertyFromGenerator(
                PropertyGenerator::fromArray(
                    [
                        'name'             => $property->getName(),
                        'const'            => false,
                        'omitdefaultvalue' => true,
                        'flags'            => 0,
                        'visibility'       => PropertyGenerator::VISIBILITY_PRIVATE,
                        'docblock'         => DocBlockGenerator::fromArray(['tags' => $tags]),
                    ]
                )
            );
            $generator->addMethodFromGenerator(
                MethodGenerator::fromArray(
                    [
                        'name'       => 'get'.ucfirst($property->getName()),
                        'parameters' => [],
                        'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                        'body'       => sprintf('return $this->%s;', $property->getName()),
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
}
