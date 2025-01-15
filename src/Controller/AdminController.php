<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route(path: '/', name: 'app_admin')]
    public function admin(
        UserRepository $userRepository
    ): Response
    {
        return $this->render('admin/admin.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route(path: '/user/add', name: 'app_admin_add_user')]
    public function addUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'User added successfully!');
                return $this->redirectToRoute('app_admin');
            } else {
                $this->addFlash('error', 'Password cannot be empty.');
            }
        }

        return $this->render('admin/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route(path: '/user/{id}/edit', name: 'app_admin_edit_user')]
    public function editUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        User $user
    ): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully!');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route(path: '/user/{id}/delete', name: 'app_admin_delete_user')]
    public function deleteUser(
        EntityManagerInterface $entityManager,
        User $user
    ): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User deleted successfully!');

        return $this->redirectToRoute('app_admin');
    }
}
