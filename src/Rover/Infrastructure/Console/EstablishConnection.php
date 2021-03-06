<?php

declare(strict_types=1);

namespace App\Rover\Infrastructure\Console;

use App\Rover\Infrastructure\Controller\GetPlanetDetailsController;
use App\Rover\Infrastructure\Controller\GetRoverPositionController;
use App\Rover\Infrastructure\Controller\SendRoverCommandsController;
use App\Rover\Infrastructure\Controller\SetRoverInitialPositionController;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EstablishConnection extends Command
{
    private const AVAILABLE_OPTIONS = [
        '- Press 1 to check planet details',
        '- Press 2 to check Rover position',
        '- Press 3 to send commands to Rover',
        '- Press 4 to finish connection'
    ];

    private bool $isSystemRunning = false;

    public function __construct(
        private LoggerInterface $logger,
        private GetPlanetDetailsController $getPlanetDetails,
        private GetRoverPositionController $getRoverPosition,
        private SendRoverCommandsController $sendRoverCommands,
        private SetRoverInitialPositionController $setRoverInitialPosition,
    )
    {
        parent::__construct();
    }

    protected static $defaultName = 'app:establish-connection';
    protected static $defaultDescription = 'Establishes a connection with mars rovers';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->printIntro();
        $this->isSystemRunning = true;
        $this->printAvailableOptions();

        do {
            $selectedOption = readline('Select an option: ');
            $this->processSelectedOption($selectedOption);
        } while ($this->isSystemRunning);

        return Command::SUCCESS;
    }

    private function processSelectedOption(string $selectedOption): void
    {
        match ($selectedOption) {
            '1' => $this->printPlanetDetails(),
            '2' => $this->printRoverPosition(),
            '3' => $this->sendRoverCommands(),
            '4' => $this->shutDownConnection(),
            default => $this->printAvailableOptions(),
        };
    }

    private function printIntro(): void
    {
        $this->logger->notice('_  _ ____ ____ ____    ____ ____ _  _ ____ ____    _  _ _ ____ ____ _ ____ _  _');
        $this->logger->notice('|\/| |__| |__/ [__     |__/ |  | |  | |___ |__/    |\/| | [__  [__  | |  | |\ |');
        $this->logger->notice('|  | |  | |  \ ___]    |  \ |__|  \/  |___ |  \    |  | | ___] ___] | |__| | \|');
        $this->logger->notice('_  _ ____ ____ ____    ____ ____ _  _ ____ ____    _  _ _ ____ ____ _ ____ _  _');
        $this->logger->notice('Initializing...');
        $this->logger->notice('Connection established successfully!');
        $this->printPlanetDetails();
        $this->setRoverInitialPosition();
    }

    private function printAvailableOptions(): void
    {
        $this->logger->notice('Available options: ');
        foreach (self::AVAILABLE_OPTIONS as $option) {
            $this->logger->notice($option);
        }
        $this->logger->notice('');
    }

    private function printPlanetDetails(): void
    {
        $planetDetails = ($this->getPlanetDetails)();
        $this->logger->notice('Planet information:');
        $this->logger->notice($planetDetails->toString());
    }

    private function printRoverPosition(): void
    {
        $roverPosition = ($this->getRoverPosition)();
        $this->logger->notice(
            sprintf('Current Rover coordinates are: %s', $roverPosition->toString())
        );
    }

    private function sendRoverCommands(): void
    {
        $commands = readline('Enter commands - [Forward: f, Left: l, Right: r]: ');

        ($this->sendRoverCommands)(str_split($commands));
        $this->printRoverPosition();
    }

    private function setRoverInitialPosition(): void
    {
        $this->logger->notice('Set Rover initial position');
        $xAxis = (int)readline('X position: ') ?: 0;
        $yAxis = (int)readline('Y position: ') ?: 0;

        ($this->setRoverInitialPosition)($xAxis, $yAxis);
        $this->printRoverPosition();
    }

    private function shutDownConnection(): void
    {
        $this->logger->notice('Shutting down system...');
        $this->logger->notice('Bye bye, see you next time ;)');
        $this->isSystemRunning = false;
    }
}