<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetMockEmailsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:reset-mock-emails')
            ->setDescription('Reset mocked emails');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('mail_sender')->resetMockedEmails();
    }
}
