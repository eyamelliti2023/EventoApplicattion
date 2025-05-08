<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{

    private UserPasswordHasherInterface $passwordEncoder;
    public function __construct( UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/admin', name: 'dashadmin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
        ]);
    }

    #[Route('/cree', name: 'user_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouvel utilisateur
        $user = new User();

        // Définir les données de l'utilisateur
        $user->setEmail('admin@example.com')  // Définir un email
             ->setNom('Admin')  // Nom de l'utilisateur
             ->setPrenom('Admin')  // Prénom de l'utilisateur
             ->setTelephone(1234567890)  // Téléphone de l'utilisateur
             ->setRoles(['ROLE_ADMIN'])  // Définir le rôle "admin"
             ->setPassword($this->passwordEncoder->hashPassword($user, 'admin')) // Assurez-vous de définir un mot de passe sécurisé
             ;
        // Sauvegarder l'utilisateur dans la base de données
        // EntityManager is now injected via the method parameter
        $entityManager->persist($user);
        $entityManager->flush();

        // Retourner une réponse ou rediriger
        return $this->redirectToRoute('app_login'); // Rediriger vers la page de login après création
    }
}
