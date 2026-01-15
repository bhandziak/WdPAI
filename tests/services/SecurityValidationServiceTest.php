<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/services/SecurityValidationService.php';
require_once __DIR__ . '/../../src/repository/UserRepository.php';

class SecurityValidationServiceTest extends TestCase
{
    private UserRepository $userRepository;
    private SecurityValidationService $service;

    protected function setUp(): void
    {
        // mock 
        $this->userRepository = $this->createMock(UserRepository::class);

        // injection
        $this->service = new SecurityValidationService($this->userRepository);
    }

    public function testValidateLoginReturnsUserWhenDataIsCorrect(): void
    {
        $user = [
            'id' => 1,
            'username' => 'john',
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'role' => 'user'
        ];

        $this->userRepository
            ->method('getUserByName')
            ->willReturn($user);

        $result = $this->service->validateLogin([
            'username' => 'john',
            'password' => 'secret'
        ]);

        $this->assertSame($user, $result);
    }
}
