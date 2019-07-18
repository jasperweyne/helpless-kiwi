<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Claim\Claim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;

/**
 * Claim controller.
 *
 * @Route("/admin/claim", name="admin_claim_")
 */
class ClaimController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }
    
    /**
     * Lists all claims.
     *
     * @MenuItem(title="Declaraties", menu="admin")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $claims = $em->getRepository(Claim::class)->findAll();

        return $this->render('admin/claim/index.html.twig', [
            'claims' => $claims,
        ]);
    }

    /**
     * Creates a new claim entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $claim = new Claim();

        $form = $this->createForm('App\Form\Claim\ClaimType', $claim);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $claim->setAuthor($user);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($claim);
            $em->flush();

            return $this->redirectToRoute('admin_claim_show', ['id' => $claim->getId()]);
        }

        return $this->render('admin/claim/new.html.twig', [
            'claim' => $claim,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a claim entity.
     *
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Claim $claim)
    {
        $em = $this->getDoctrine()->getManager();

        $createdAt = $this->events->findOneBy($claim, EntityNewEvent::class);

        return $this->render('admin/claim/show.html.twig', [
            'createdAt' => $createdAt,
            'claim' => $claim,
        ]);
    }
}
