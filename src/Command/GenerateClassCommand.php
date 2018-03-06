<?php

namespace App\Command;

use App\Context\ClassContext;
use App\Generator\ClassGenerator;
use App\Parser\JsonParser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateClassCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('json:php:generate')
            ->setDescription('Converts json into jms serializable classes')
            ->setHelp('Convert json to php classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $namespace = $io->ask('namespace');
        $dest = $io->ask('destination to put files in', 'src');
        $className = $io->ask('class name');
        $json = $io->ask('json content');

        $parser = new JsonParser();
        $data = $parser->parse(
            $className,
            $namespace,
            $json
        );

        if (!is_dir($dest) && !mkdir($dest, 0777, true) && !is_dir($dest)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dest));
        }
        /** @var ClassContext $class */
        foreach ($data as $class) {
            file_put_contents($dest.DIRECTORY_SEPARATOR.$class->getFilePath(), ClassGenerator::generate($class));
            $io->writeln(sprintf('Generated class %s', $class));
        }
    }
}