<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantController extends AbstractController
{
    #[Route('/participant/dash', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/dashboard.html.twig');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $user->setRole('CLIENT');

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $form->get('email')->addError(new FormError("Cet email est déjà utilisé. Veuillez en choisir un autre."));
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $user->getPlainPassword());
                $user->setMotDePasse($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('participant/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_edit_user')]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur avec l'ID $id n'existe pas.");
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $user->getPlainPassword();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setMotDePasse($hashedPassword);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_participant');
        }

        return $this->render('participant/edit.html.twig', [
            'editForm' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/participants', name: 'app_parti_admin')]
    public function listParticipants(EntityManagerInterface $entityManager): Response
    {
        $participants = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.role = :role')
            ->setParameter('role', 'CLIENT')
            ->getQuery()
            ->getResult();

        return $this->render('admin/participants.html.twig', [
            'participants' => $participants,
        ]);
    }

    #[Route('/admin/participant/delete/{id}', name: 'app_participant_delete')]
    public function deleteParticipant(User $participant, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($participant);
        $entityManager->flush();

        $this->addFlash('success', 'Participant supprimé avec succès.');
        return $this->redirectToRoute('app_parti_admin');
    }
}
