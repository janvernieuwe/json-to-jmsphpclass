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
    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('json:php:generate')
            ->setDescription('Converts json into jms serializable classes')
            ->setHelp('Convert json to php classes');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Input
        $io = new SymfonyStyle($input, $output);
        $namespace = $io->ask('namespace', 'App');
        $dest = $io->ask('destination to put files in', 'src');
        $className = $io->ask('class name');
        $inputFile = $io->ask('input file', 'source.json');
        $json = file_get_contents($inputFile);

        // Parse the json into contexts
        $io->section('Parse json');
        $parser = new JsonParser();
        $data = $parser->parse($className, $namespace, $json);
        if (!is_dir($dest) && !mkdir($dest, 0777, true) && !is_dir($dest)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dest));
        }
        $io->success('Json parsed');

        // Generate classes from the contexts
        $io->section('Generate classes');
        /** @var ClassContext $class */
        foreach ($data as $class) {
            file_put_contents($dest.DIRECTORY_SEPARATOR.$class->getFilePath(), ClassGenerator::generate($class));
            $io->writeln(sprintf('Generated class %s', $class));
        }
        $io->success('All classes have been generated');
    }
}