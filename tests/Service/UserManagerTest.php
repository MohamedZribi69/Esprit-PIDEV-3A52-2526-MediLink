<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    public function testUserValide(): void
    {
        $user = new User();
        $user->setEmail('test@medilink.tn');
        $user->setFullName('Jean Dupont');
        $user->setPassword('hashed');
        $user->setStatus(User::STATUS_ACTIVE);

        $manager = new UserManager();
        $this->assertTrue($manager->validate($user));
    }

    public function testUserEmailInvalideRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'email doit être valide.');

        $user = new User();
        $user->setEmail('email_invalide');
        $user->setFullName('Test');
        $user->setPassword('hashed');
        $user->setStatus(User::STATUS_ACTIVE);

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserStatutInvalideRejete(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le statut doit être ACTIVE ou DISABLED.');

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setFullName('Test');
        $user->setPassword('hashed');
        $user->setStatus('INCONNU');

        $manager = new UserManager();
        $manager->validate($user);
    }
}
