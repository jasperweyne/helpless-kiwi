<?php

namespace App\Controller\Admin;

use App\Entity\Activity\Activity;
use App\Entity\Security\LocalAccount;
use App\Repository\ActivityRepository;
use App\Repository\GroupRepository;
use App\Template\Attribute\MenuItem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Activity controller.
 */
#[Route("/admin", name: "admin_")]
class AdminController extends AbstractController
{
    /**
     * Lists all activities.
     */
    #[MenuItem(title: "Overzicht", menu: "admin", activeCriteria: "admin_index", order: -1)]
    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(ActivityRepository $activitiesRepo, GroupRepository $groupsRepo): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $activities = $activitiesRepo->findBy([], ['start' => 'DESC']);
        } else {
            $user = $this->getUser();
            assert($user instanceof LocalAccount);
            $groups = $groupsRepo->findSubGroupsForPerson($user);
            $activities = $activitiesRepo->findAuthor($groups);
        }

        // Only retain current and future activities
        $activities = (new ArrayCollection($activities))->filter(fn (Activity $activity) => $activity->getEnd() > new \DateTime("now"));

        return $this->render('admin/index.html.twig', [
            'activities' => $activities,
        ]);
    }
}
