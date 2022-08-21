<?php

namespace App\DataFixtures;

use App\Entity\ClientUser;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientUserFixtures extends BaseFixtures
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function loadData(ObjectManager $manager)
    {
        $this->create(ClientUser::class, function (ClientUser $user) use ($manager) {
            $user
                ->setfirstName('admin')
                ->setEmail('admin@admin.ru')
                ->setPassword($this->passwordEncoder->encodePassword($user, '123456'))
                ->setRoles(['ROLE_ADMIN']);
            ;
        });
        
        $manager->flush();
    }
}
