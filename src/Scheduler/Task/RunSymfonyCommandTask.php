<?php

namespace App\Scheduler\Task;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class RunSymfonyCommandTask
{
    public function __construct(private string $commandName, private KernelInterface $kernel)
    {
    }

    public function __invoke(): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => $this->commandName,
        ]);
        $application->run($input, new ConsoleOutput());
    }
}