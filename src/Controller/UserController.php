<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TicketFormType;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function index(): Response
    {
        $myTickets = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->findAll();
            // ->findByidUser($this->getUser()->getId());

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'myTickets' => $myTickets,
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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('user');
        }

        return $this->render('user/newTicket.html.twig', [
            'newTicketForm' => $form->createView(),
        ]);
    }
}
