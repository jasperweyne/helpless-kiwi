<?php

namespace App\Controller\Activity;

use App\Template\Annotation\MenuItem;
use App\Entity\Activity\Activity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Activity\PriceOption;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Activity\Registration;

/**
 * Activity controller.
 *
 * @Route("/", name="activity_")
 */
class ActivityController extends AbstractController
{
    /**
     * Lists all activities.
     *
     * @MenuItem(title="Activiteiten")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $activities = $em->getRepository(Activity::class)->findAll();

        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/activity/{id}/unregister", name="unregister", methods={"POST"})
     */
    public function unregisterAction(Request $request, Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createUnregisterForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (null !== $data['registration_single']) {
                $registration = $em->getRepository(Registration::class)->find($data['registration_single']);

                if (null !== $registration) {
                    $em->remove($registration);
                    $em->flush();

                    $this->addFlash('success', 'Afmelding gelukt!');

                    return $this->redirectToRoute('activity_show', ['id' => $activity->getId()]);
                }
            }
        }

        $this->addFlash('error', 'Probleem tijdens afmelden');

        return $this->redirectToRoute('activity_show', ['id' => $activity->getId()]);
    }

    /**
     * Displays a form to edit an existing activity entity.
     *
     * @Route("/activity/{id}/register", name="register", methods={"POST"})
     */
    public function registerAction(Request $request, Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createRegisterForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (null !== $data['single_option']) {
                $option = $em->getRepository(PriceOption::class)->find($data['single_option']);

                if (null !== $option) {
                    $reg = new Registration();
                    $reg->setActivity($activity);
                    $reg->setOption($option);
                    $reg->setPerson($this->getUser()->getPerson());

                    $em->persist($reg);
                    $em->flush();

                    $this->addFlash('success', 'Aangemelding gelukt!');

                    return $this->redirectToRoute('activity_show', ['id' => $activity->getId()]);
                }
            }
        }

        $this->addFlash('error', 'Probleem tijdens aanmelden');

        return $this->redirectToRoute('activity_show', ['id' => $activity->getId()]);
    }

    /**
     * Finds and displays a activity entity.
     *
     * @Route("/activity/{id}", name="show", methods={"GET"})
     */
    public function showAction(Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $forms = [];
        foreach ($activity->getOptions() as $option) {
            $forms[] = [
                'data' => $option,
                'form' => $this->singleRegistrationForm($option)->createView(),
            ];
        }

        $unregister = null;
        if (null !== $this->getUser()) {
            $registration = $em->getRepository(Registration::class)->findOneBy(['activity' => $activity, 'person' => $this->getUser()->getPerson()]);
            if (null !== $registration) {
                $unregister = $this->singleUnregistrationForm($registration)->createView();
            }
        }

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
            'options' => $forms,
            'unregister' => $unregister,
        ]);
    }

    public function singleUnregistrationForm(Registration $registration)
    {
        $form = $this->createUnregisterForm($registration->getActivity());
        $form->get('registration_single')->setData($registration->getId());

        return $form;
    }

    public function singleRegistrationForm(PriceOption $option)
    {
        $form = $this->createRegisterForm($option->getActivity());
        $form->get('single_option')->setData($option->getId());

        return $form;
    }

    private function createUnregisterForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_unregister', ['id' => $activity->getId()]))
            ->add('registration_single', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'button delete'],
                'label' => 'Afmelden',
            ])
            ->getForm()
        ;
    }

    private function createRegisterForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_register', ['id' => $activity->getId()]))
            ->add('single_option', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'button confirm'],
                'label' => 'Aanmelden',
            ])
            ->getForm()
        ;
    }
}
