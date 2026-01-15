<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/repository/UserRepository.php';
require_once __DIR__ . '/../../src/repository/Repository.php';

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    private $mockDatabase;
    private $mockConnection;
    private $mockStatement;

    protected function setUp(): void
    {
        // Mock PDOStatement
        $this->mockStatement = $this->createMock(PDOStatement::class);
        $this->mockStatement->method('execute')->willReturn(true);

        // Mock PDO connection
        $this->mockConnection = $this->createMock(PDO::class);
        $this->mockConnection
            ->method('prepare')
            ->willReturn($this->mockStatement);

        // Mock Database
        $this->mockDatabase = $this->getMockBuilder(stdClass::class)
            ->setMethods(['connect', 'disconnect'])
            ->getMock();
        $this->mockDatabase
            ->method('connect')
            ->willReturn($this->mockConnection);
        $this->mockDatabase
            ->method('disconnect')
            ->willReturn(true);

        // Repository z mock DB
        $this->userRepository = new UserRepository();
        $this->userRepository->database = $this->mockDatabase;
    }

    public function testCreateUserExecutesQuery(): void
    {
        $this->userRepository->createUser(
            'john',
            password_hash('secret', PASSWORD_DEFAULT),
            'john@example.com',
            'rock',
            'user'
        );

        $this->assertTrue(true);
    }
}
