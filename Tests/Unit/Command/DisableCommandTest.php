<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Tests\Unit\Command;

use FriendsOfTYPO3\Crowdin\Command\DisableCommand;
use FriendsOfTYPO3\Crowdin\Setup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DisableCommand::class)]
final class DisableCommandTest extends TestCase
{
    #[Test]
    public function setUp(): void
    {
        $setupMock = $this->createMock(Setup::class);
        $setupMock
            ->expects($this->once())
            ->method('disable');

        $command = new DisableCommand($setupMock);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('[OK] Crowdin disabled', $commandTester->getDisplay());
    }
}
