<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Service\BrevoMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReclamationController extends AbstractController
{
    #[Route('/reclamation', name: 'app_reclamation')]
    public function index(): Response
    {
        return $this->render('reclamation/index.html.twig', [
            'controller_name' => 'ReclamationController',
        ]);
    }

    #[Route('/user/{id}/reclamations', name: 'app_user_reclamations')]
    public function getUserReclamations(User $user): Response
    {
        $reclamations = $user->getReclamations();

        return $this->render('reclamation/listuser.html.twig', [
            'reclamations' => $reclamations,
            'user' => $user,
        ]);
    }

    #[Route('/reclamation/new', name: 'app_reclamation_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
    
        $reclamation->setUser($this->getUser()); 
        $reclamation->setStatus('PENDING');
    
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $newFilename = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('uploads_directory'), $newFilename);
                $reclamation->setImageUrl('/uploads/' . $newFilename);
            }
    
            $em->persist($reclamation);
            $em->flush();
    
            $this->addFlash('success', 'Votre réclamation a été soumise avec succès.');
            return $this->redirectToRoute('app_user_reclamations', [
                'id' => $this->getUser()->getId()
            ]);
        }
    
        return $this->render('reclamation/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
    

    #[Route('/admin/reclamations', name: 'app_rec_admin')]
    public function listReclamations(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager->getRepository(Reclamation::class)->findAll();

        return $this->render('admin/reclamations.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/admin/reclamations/repondre/{id}', name: 'app_rec_reply')]
    public function replyReclamation(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, BrevoMailerService $brevoMailerService): Response
    {
        $form = $this->createFormBuilder($reclamation)
            ->add('response', TextareaType::class, [
                'label' => 'Réponse',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer la réponse',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setStatus('REPLIED');
            $entityManager->flush();

            $user = $reclamation->getUser();
            if ($user) {
                $email = $user->getEmail();
                $subject = 'Réponse à votre réclamation';
                $message = 'Bonjour, votre réclamation a reçu une réponse : ' . $reclamation->getResponse();
                $brevoMailerService->sendOtpEmail($email, $message);
            }

            $this->addFlash('success', 'Réponse envoyée avec succès.');
            return $this->redirectToRoute('app_rec_admin');
        }

        return $this->render('admin/reclamation_reply.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/reclamations/delete/{id}', name: 'app_rec_delete', methods: ['POST'])]
    public function deleteReclamation(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_rec_admin');
    }

    #[Route('/admin/delete/reclamations/delete/{id}', name: 'rec_delete', methods: ['POST'])]
    public function deleteReclamationa(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_participant');
    }

    #[Route('/admin/reclamations/edit/{id}', name: 'app_rec_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation non trouvée.');
        }

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation modifiée avec succès.');
            return $this->redirectToRoute('app_participant');
        }

        return $this->render('reclamation/edit.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }
}
