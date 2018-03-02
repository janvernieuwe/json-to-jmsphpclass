<?php

namespace App\Command;

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
//        $namespace = $io->ask('namespace');
//        $className = $io->ask('class name');
//        $json = $io->ask('json content');
        $namespace = 'App';
        $className = 'User';
        $json = ' {
    "id": 1,
    "name": "Leanne Graham",
    "username": "Bret",
    "email": "Sincere@april.biz",
    "address": {
      "street": "Kulas Light",
      "suite": "Apt. 556",
      "city": "Gwenborough",
      "zipcode": "92998-3874",
      "geo": {
        "lat": "-37.3159",
        "lng": "81.1496"
      }
    },
    "phone": "1-770-736-8031 x56442",
    "website": "hildegard.org",
    "company": {
      "name": "Romaguera-Crona",
      "catchPhrase": "Multi-layered client-server neural-net",
      "bs": "harness real-time e-markets"
    }
  }';

        $parser = new JsonParser();
        $data = $parser->parse(
            $className,
            $namespace,
            $json
        );

        foreach ($data as $class) {
            $io->writeln(ClassGenerator::generate($class));
        }
    }
}