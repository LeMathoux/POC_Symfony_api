<?php

namespace App\Scheduler\Handler;

use App\Scheduler\Task\RunSymfonyCommandTask;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RunSymfonyTaskHnadler
{
    public function __invoke(RunSymfonyCommandTask $task){
        $task();
    }
}