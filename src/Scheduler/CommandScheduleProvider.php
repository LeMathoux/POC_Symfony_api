<?php

namespace App\Scheduler;

use App\Scheduler\Task\RunSymfonyCommandTask;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule(
    name: 'command_schedule_provider',
)]
final class CommandScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private KernelInterface $kernel,
    ) {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();
        
        $schedule->add(RecurringMessage::cron('0 9 * * MON', new RunSymfonyCommandTask('app:send-upcoming-games-email',$this->kernel)));

        $schedule->stateful($this->cache);

        return $schedule;
    }
}
