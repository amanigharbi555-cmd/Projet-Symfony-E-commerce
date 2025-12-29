<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/utilisateurs', name: 'admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $usersRepository->findBy([], ['firstname' => 'asc']);
        return $this->render('admin/users/index.html.twig', compact('users'));
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        
        // Ajouter un champ pour le rôle (non mappé car roles est un tableau dans l'entité)
        $form->add('roles', ChoiceType::class, [
            'choices' => [
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ],
            'multiple' => false,
            'expanded' => false,
            'label' => 'Rôle',
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'mapped' => false,
            'data' => 'ROLE_USER'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Définir le rôle
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            // Activer directement le compte
            $user->setIsVerified(true);

            // Les champs address, zipcode et city sont optionnels et nullable dans la base
            // Pas besoin de les définir explicitement si ils sont vides

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/add.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Users $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Créer un formulaire personnalisé pour l'édition
        $form = $this->createFormBuilder($user)
            ->add('lastname', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('firstname', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Prénom',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'E-mail',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => false,
                'expanded' => false,
                'label' => 'Rôle',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'mapped' => false,
                'data' => !empty($user->getRoles()) ? $user->getRoles()[0] : 'ROLE_USER'
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Laisser vide pour ne pas changer'
                ],
                'label' => 'Nouveau mot de passe (optionnel)',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Compte vérifié',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check-input']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le rôle
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            // Mettre à jour le mot de passe si fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', [
            'userForm' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Users $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Empêcher la suppression de l'utilisateur actuellement connecté
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Vérifier si l'utilisateur a des commandes
        if (!$user->getOrders()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer cet utilisateur car il a des commandes associées.');
            return $this->redirectToRoute('admin_users_index');
        }

        // Supprimer l'utilisateur
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');

        return $this->redirectToRoute('admin_users_index');
    }
}