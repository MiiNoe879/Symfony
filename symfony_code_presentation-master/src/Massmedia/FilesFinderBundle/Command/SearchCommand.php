<?php

namespace Massmedia\FilesFinderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('files_finder:search')
            ->setDescription('Search files by content')
            ->addArgument(
                'dir',
                InputArgument::REQUIRED,
                'Absolute path to files'
            )
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'Text to search'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('dir');
        $text = $input->getArgument('text');

        $files = $this->getContainer()->get('massmedia.files_finder')->search($dir, $text);

        if($files) {
            foreach($files as $file) {
                $output->writeln($file);
            }
        } else {
            $output->writeln('No files founded');
        }
    }
}