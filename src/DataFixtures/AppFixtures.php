<?php

namespace App\DataFixtures;


use DateTime;
use App\Entity\Film;
use App\Entity\User;
use App\Entity\Vote;
use App\Entity\Membre;
use App\Entity\Semaine;
use App\Entity\Proposition;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
        private $userPasswordHasher;
        
        public function __construct(UserPasswordHasherInterface $userPasswordHasher)
        {
            $this->userPasswordHasher = $userPasswordHasher;
        }
    
        
        public function load(ObjectManager $manager): void
        {
            // Création d'un user "normal"
            $user = new User();
            $user->setEmail("a@a.fr");
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
            $manager->persist($user);
            
            // Création d'un user admin
            $userAdmin = new User();
            $userAdmin->setEmail("b@b.fr");
            $userAdmin->setRoles(["ROLE_ADMIN"]);
            $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
            $manager->persist($userAdmin);

                // Création des auteurs.
            /*$listMembre = [];
            for ($i = 0; $i < 10; $i++) {
                // Création de l'auteur lui-même.
                $membre = new Membre();
                $membre->setPrenom("Prenom " . $i);
                $membre->setNom("Nom " . $i);
                $membre->setMail("mail " . $i);
                $membre->setMdp("mdp " . $i);
                $manager->persist($membre);

                // On sauvegarde l'auteur créé dans un tableau.
                $listMembre[] = $membre;
            }

            for ($i = 0; $i < 20; $i++) {
                $film = new Film();
                $film->setTitre("Titre " . $i);
                $film->setDate(new \DateTime('2022-01-01'));
                $film->setSortiefilm(2022);
                $film->setImdb("imdb " . $i);
                $film->setResume("Resumé du film ". $i);
                //$film->setMembre($listMembre[array_rand($listMembre)]);
                $manager->persist($film);
            }*/

            
            //Création utilisateur Jessy
            $membre_jessy = new Membre();
            $membre_jessy->setPrenom("Jessy");
            $membre_jessy->setNom("Jessy");
            $membre_jessy->setMail("a@a.fr");
            $membre_jessy->setMdp("Toto");
            $manager->persist($membre_jessy);

            //Création utilisateur Coco
            $membre_coco = new Membre();
            $membre_coco->setPrenom("Coco");
            $membre_coco->setNom("Coco");
            $membre_coco->setMail("a@a.fr");
            $membre_coco->setMdp("Toto");
            $manager->persist($membre_coco);

            //Création utilisateur robin
            $membre_robin = new Membre();
            $membre_robin->setPrenom("Robin");
            $membre_robin->setNom("Robin");
            $membre_robin->setMail("a@a.fr");
            $membre_robin->setMdp("Toto");
            $manager->persist($membre_robin);

            //Création film Thor
            $film_thor = new Film();
            $film_thor->setTitre("Thor");
            $film_thor->setDate(new \DateTime('2022-01-01'));
            $film_thor->setSortiefilm("2020");
            $film_thor->setImdb("https://www.imdb.com/title/tt10648342/");
            $manager->persist($film_thor);


            //Création film Iron Man
            $film_iron_man = new Film();
            $film_iron_man->setTitre("Iron_man");
            $film_iron_man->setDate(new \DateTime('2023-01-30'));
            $film_iron_man->setSortiefilm("2014");
            $film_iron_man->setImdb("https://www.imdb.com/title/tt10648342/");
            $manager->persist($film_iron_man);

            //Création film Iron Man
            $film_hulk = new Film();
            $film_hulk->setTitre("Hulk");
            $film_hulk->setDate(new \DateTime('2023-01-30'));
            $film_hulk->setSortiefilm("2014");
            $film_hulk->setImdb("https://www.imdb.com/title/tt10648342/");
            $manager->persist($film_hulk);


            // // Création d'une Semaine 1 
            // for ($i = 0; $i < 20; $i++) {
            //     $semaine = new Semaine();
            //     $semaine->setJour(new \DateTime('2023-01-30'));
            //     $semaine->setProposeur("Prenom" . $i);
            //     $semaine->setPropositionTermine(false);
            //     $semaine->setTheme("Marvel");
            //     $manager->persist($semaine);
            // }
            
            //Création d'une Semaine 2
            $semaine2 = new Semaine();
            $semaine2->setJour(new \DateTime('2023-02-30'));
            $semaine2->setProposeur("Jessy");
            $semaine2->setPropositionTermine(false);
            $semaine2->setTheme("DC");
            $manager->persist($semaine2);

            // //Création d'une Semaine 3
            // $semaine3 = new Semaine();
            // $semaine3->setJour(new \DateTime('2023-03-30'));
            // $semaine3->setProposeur("Robin");
            // $semaine3->setPropositionTermine(false);
            // $semaine3->setTheme("NoTheme");
            // $manager->persist($semaine3);



            // //Création d'une première proposition
            // for ($i = 0; $i < 20; $i++) {
            //     $proposition = new Proposition();
            //     $proposition->setScore(36);
            //     $proposition->setSemaine($semaine);
            //     $proposition->setFilm($film);
            //     $manager->persist($proposition);
            // }



            //Création d'une deuxième proposition
            $proposition1 = new Proposition();
            $proposition1->setScore(36);
            $proposition1->setSemaine($semaine2);
            $proposition1->setFilm($film_thor);
            $manager->persist($proposition1);

            //Création d'une deuxième proposition
            $proposition2 = new Proposition();
            $proposition2->setScore(36);
            $proposition2->setSemaine($semaine2);
            $proposition2->setFilm($film_iron_man);
            $manager->persist($proposition2);

            //Création d'une deuxième proposition
            $proposition3 = new Proposition();
            $proposition3->setScore(36);
            $proposition3->setSemaine($semaine2);
            $proposition3->setFilm($film_hulk);
            $manager->persist($proposition3);



            // //Création d'un vote
            // $vote = new Vote();
            // $vote->setVote(1);
            // $vote->setMembre($membre_jessy);
            // $vote->setSemaine($semaine2);
            // $vote->setProposition($proposition);
            // $manager->persist($vote);

            // //Création d'un autre vote
            // // $vote2 = new Vote();
            // // $vote2->setVote(2);
            // // $vote2->setMembre($membre_jessy);
            // // $vote2->setSemaine($semaine2);
            // // $vote2->setProposition($proposition2);
            // // $manager->persist($vote2);



            
            // $product = new Product();
            // $manager->persist($product);
            $manager->flush();
        }
}
