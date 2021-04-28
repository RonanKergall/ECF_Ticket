<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TicketFormType;
use App\Form\UserFormType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Category;
use App\Form\CategoryFormType;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function index(): Response
    {
        $tickets = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->findAll();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'allTickets' => $tickets,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/newTicket', name: 'user_newTicket')]
    public function newTicket(Request $request): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticket->setIdUser($this->getUser());
            $ticket->setClose(0);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('user');
        }

        return $this->render('user/newTicket.html.twig', [
            'newTicketForm' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/myTickets', name: 'user_myTickets')]
    public function myTickets(): Response
    {
        $myTickets = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->findByUser($this->getUser()->getId());

        return $this->render('user/myTickets.html.twig', [
            'controller_name' => 'UserController',
            'myTickets' => $myTickets,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/admin', name: 'user_admin')]
    public function admin(): Response
    {
        $allUsers = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        $allCategories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('user/admin.html.twig', [
            'controller_name' => 'UserController',
            'user' => $this->getUser(),
            'allUsers' => $allUsers,
            'allCategories' => $allCategories,
        ]);
    }

    #[Route('/admin/user_edit/{slug}', name: 'user_admin_userEdit')]
    public function user_edit($slug, Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($slug);

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_admin');
        }

        return $this->render('user/editUser.html.twig', [
            'newUserForm' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/admin/user_new', name: 'user_admin_userNew')]
    public function user_new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();

        $form = $this->createForm(UserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_admin');
        }

        return $this->render('user/editUser.html.twig', [
            'newUserForm' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/admin/category_edit/{slug}', name: 'user_admin_categoryEdit')]
    public function category_edit($slug, Request $request): Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->find($slug);

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('user_admin');
        }

        return $this->render('user/editCategory.html.twig', [
            'newCategoryForm' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/admin/category_new', name: 'user_admin_categoryNew')]
    public function category_new(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('user_admin');
        }

        return $this->render('user/editCategory.html.twig', [
            'newCategoryForm' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/admin/category_delete/{slug}', name: 'user_admin_categoryDelete')]
    public function category_delete($slug, Request $request): Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->find($slug);

        $someTickets = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->findBy(array('idCategory' => $category->getId()));

        $newCategory = $this->getDoctrine()
            ->getRepository(Category::class)
            ->find(0);

        foreach ($someTickets as $ticket){
            $ticket->setIdCategory($newCategory);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();
        

        return $this->redirectToRoute('user_admin');
    }
}
