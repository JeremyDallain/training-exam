<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Article;
use Bezhanov\Faker\Provider\Commerce;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));

        $admin = new User();
        $hash = $this->encoder->encodePassword($admin, 'password');
        $admin->setEmail("admin@gmail.com")
            ->setPassword($hash)
            ->setRoles(['ROLE_ADMIN'])
            ->setFullName('Administrator');               
        $manager->persist($admin);


        for ($u=1; $u <= 10; $u++) { 
            
            $user = new User();
            $user->setEmail("user$u@gmail.com")
                ->setFullName($faker->name())
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->encoder->encodePassword($user, "password"));
            
            $manager->persist($user);
                        
            for ($a=0; $a < mt_rand(2, 4); $a++) { 
                
                $article = new Article();
                $article->setTitle($faker->department())
                    ->setContent($faker->paragraphs(20, true))
                    ->setCreatedAt($faker->dateTimeBetween('-1 month', 'now'))
                    ->setUser($user);
                
                $manager->persist($article);
            } 
        }
        $manager->flush();    
    }
}
