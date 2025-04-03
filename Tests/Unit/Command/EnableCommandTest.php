<?php

declare(strict_types=1);


namespace FriendsOfTYPO3\Crowdin\Tests\Unit\Command;

use FriendsOfTYPO3\Crowdin\Command\EnableCommand;
use FriendsOfTYPO3\Crowdin\Setup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(EnableCommand::class)]
final class EnableCommandTest extends TestCase
{
    #[Test]
    public function setUp(): void
    {
        $setupMock = self::createMock(Setup::class);
        $setupMock
            ->expects(self::once())
            ->method('enable');

        $command = new EnableCommand($setupMock);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('[OK] Crowdin enabled', $commandTester->getDisplay());
    }
}
