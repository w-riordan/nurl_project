<?php
/**
 * Created by PhpStorm.
 * User: wayne
 * Date: 13/04/17
 * Time: 16:47
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Tag;
use AppBundle\Entity\User;
use ClassesWithParents\D;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use AppBundle\Entity\UserType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadInitialData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null){
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        //Create User Types
        $standUserType = $this->createUserType("Standard");
        $modUserType = $this->createUserType("Moderator");
        $adminUserType = $this->createUserType("Administrator");

        $manager->persist($standUserType);
        $manager->persist($modUserType);
        $manager->persist($adminUserType);

        //Create Users
        $about = "This user represents all unregistered users";
        $anonUser = $this->createUser("Anonymous","thisusershouldnotbeloggedin",$about,$standUserType);
        $about = "I am the site admistrator.";
        $adminUser = $this->createUser("admin","admin",$about,$adminUserType);
        $about = "I am a moderator.";
        $modUser = $this->createUser("moderator","password",$about,$modUserType);
        $about = "I am a standard User.";
        $stdUser = $this->createUser("user249","password",$about,$standUserType);

        $manager->persist($anonUser);
        $manager->persist($adminUser);
        $manager->persist($modUser);
        $manager->persist($stdUser);

        //Create Initial Tags
        $webTag = $this->createTag("Web Dev", 1500);
        $javaTag = $this->createTag("Java Dev", 1000);
        $gameTag = $this->createTag("Game Dev", 2200);

        $manager->persist($webTag);
        $manager->persist($javaTag);
        $manager->persist($gameTag);


        $manager->flush();
    }

    public function createUserType($name){
        $userType = new UserType();
        $userType->setType($name);

        return $userType;
    }

    public function createUser($name, $pass, $about,$type){
        $user = new User();
        $encoder =$this->container->get("security.password_encoder");
        $newPass = $encoder->encodePassword($user,$pass);
        $user->setUsername($name)
            ->setPassword($newPass)
            ->setAbout($about)
            ->setFrozen(false)
            ->setProfilepic(null)
            ->setJoindate(new \DateTime())
            ->setUsertype($type);

        return $user;
    }

    public function createTag($name, $rating){
        $tag = new Tag();
        $tag->setName($name)->setRating($rating);
        return $tag;
    }
}