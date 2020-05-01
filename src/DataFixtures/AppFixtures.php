<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($u = 0 ; $u < 10 ; $u++) {
            $user = new User();

            $chrono = 1;

            $hash = $this->encoder->encodePassword($user, 'password');

            $user
                ->setFirstname($faker->firstName)
                ->setLastname($faker->lastName)
                ->setEmail($faker->email)
                ->setPassword($hash)
                ->setCompany($faker->company)
                ->setAddress($faker->address)
                ->setPostalCode($faker->postcode)
                ->setCity($faker->city)
                ->setNumTVA($faker->randomDigit)
                ->setPhone($faker->phoneNumber)
            ;
            $manager->persist($user);

            for ($i = 0 ; $i < 30; $i++) {
                $customer = new Customer();
                $customer
                    ->setFirstname($faker->firstName())
                    ->setLastname($faker->lastName())
                    ->setCompany($faker->company())
                    ->setEmail($faker->email)
                    ->setUser($user)
                    ->setAddress($faker->address)
                    ->setPostalCode($faker->postcode)
                    ->setCity($faker->city)
                ;
                $manager->persist($customer);

                for ($k = 0 ; $k < mt_rand(5, 20); $k++ ) {
                    $invoice = new Invoice();
                    $invoice
                        ->setAmount($faker->randomFloat(2, 250, 5000))
                        ->setSentAt($faker->dateTimeBetween('- 6 months'))
                        ->setStatus($faker->randomElement(['SENT', 'PAID', 'CANCELLED']))
                        ->setCustomer($customer)
                        ->setChrono($chrono)
                        ->setFee($faker->randomFloat(2,150, 200))
                    ;
                    $chrono++;

                    $manager->persist($invoice);
                }
            }
        }
        $admin = new User();
        $admin->setFirstname('fabien')
            ->setLastname('labedade')
            ->setNumTVA(0)
            ->setCompany('WCS')
            ->setEmail('fabien.labedade@gmail.com')
            ->setAddress('1 allée quiétude')
            ->setPhone('0662434908')
            ->setCity('Merignac')
            ->setPostalCode('33700')
            ->setPassword($this->encoder->encodePassword($admin, 'password'))
            ->setRoles([User::ROLE_ADMIN]);
        $manager->persist($admin);

        $manager->flush();
    }
}
