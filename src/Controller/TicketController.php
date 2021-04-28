<?php

namespace App\Controller;

use App\Entity\Reply;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Form\TicketFormType;
use App\Form\ReplyFormType;

class TicketController extends AbstractController
{
    #[Route('/ticket/{slug}', name: 'ticket')]
    public function index($slug, Request $request): Response
    {
        $ticket = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->find($slug);

        $reply = new Reply();
        $form = $this->createForm(ReplyFormType::class, $reply);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $reply->setIdUser($this->getUser());
            $reply->setIdTicket($ticket);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reply);
            $entityManager->flush();

            return $this->redirectToRoute('ticket', ['slug' => $slug]);
        }

        return $this->render('ticket/index.html.twig', [
            'controller_name' => 'TicketController',
            'ticket' => $ticket,
            'user' => $this->getUser(),
            'newReplyForm' => $form->createView(),
        ]);
    }

    #[Route('/close/{slug}', name: 'ticket_close')]
    public function close($slug, Request $request): Response
    {
        $ticket = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->find($slug);

        $ticket->setClose(1);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($ticket);
        $entityManager->flush();

        return $this->redirectToRoute('user');
    }

    #[Route('/edit/{slug}', name: 'ticket_edit')]
    public function edit($slug, Request $request): Response
    {
        $ticket = $this->getDoctrine()
            ->getRepository(Ticket::class)
            ->find($slug);

        $form = $this->createForm(TicketFormType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $ticket->setIdUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket', ['slug' => $slug]);
        }

        return $this->render('user/newTicket.html.twig', [
            'newTicketForm' => $form->createView(),
        ]);
    }
}
