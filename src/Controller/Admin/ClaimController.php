<?php

namespace App\Controller\Admin;

use App\Template\Annotation\MenuItem;
use App\Entity\Claim\Claim;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Claim controller.
 *
 * @Route("/admin/claim")
 */
class ClaimController extends AbstractController
{
    /**
     * Lists all claims.
     *
     * @MenuItem(title="Declaraties")
     * @Route("/", name="admin_claim_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $claims = $em->getRepository(Claim::class)->findBy([], ['createdAt' => 'ASC']);

        return $this->render('admin/claim/index.html.twig', [
            'claims' => $claims,
        ]);
    }
}
