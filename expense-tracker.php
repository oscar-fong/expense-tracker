#!/usr/bin/php
<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

// ->addArgument('username', InputArgument::REQUIRED)
$application = new Application;
$application->register('add')
  ->setDescription('Add an expense')
  ->addOption(
    'description',
    'd',
    InputOption::VALUE_REQUIRED,
    'Description of the product bought',
  )
  ->addOption(
    'amount',
    'a',
    InputOption::VALUE_REQUIRED,
    'How much did it cost',
  )
  ->setCode(function (InputInterface $input, OutputInterface $output): int {
    $description = $input->getOption('description');
    $amount = $input->getOption('amount');
    if ($description && $amount) {
      $output->writeln($amount);
    }
    return Command::SUCCESS;
    //$output->writeln()

    //return $output->getFormatter()
  });

//$application->register('')


$application->run();
