<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\Forum;
use App\Entity\Thread;
use App\Entity\Message;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    
	private $encoder;
	private $projectDir;


	public function __construct(UserPasswordEncoderInterface $encoder, ParameterBagInterface $parameterBag)
	{
		$this->encoder = $encoder;
        $this->projectDir = $parameterBag->get('project_dir');
	}

	public function load(ObjectManager $manager)
	{

		
		$user = new User();
		$email = $_ENV['ADMIN_EMAIL_INITIAL'];
		$user->setEmail($email);
		$user->setUsername('Admin');
		$user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setEnabled(true);
        $plainPassword = $_ENV['ADMIN_PASS_INITIAL'];
        $encoded = $this->encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $manager->persist($user);



		$manager->flush();
	}
}
