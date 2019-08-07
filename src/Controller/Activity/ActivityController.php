<?php

namespace App\Controller\Activity;

use App\Entity\Activity\Activity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Activity\PriceOption;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
     * @Route("/activity/{id}/register", name="register", methods={"POST"})
     */
    public function registerAction(Request $request, Activity $activity)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createRegisterForm($activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            if ($data['single_option'] !== null) {
                $option = $em->getRepository(PriceOption::class)->find($data['single_option']);

                if ($option !== null) {
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
        
        $forms = array();
        foreach ($activity->getOptions() as $option) {
            $forms[] = [
                'data' => $option,
                'form' => $this->singleRegistrationForm($option)->createView(),
            ];
        }

        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
            'options' => $forms,
        ]);
    }

    public function singleRegistrationForm(PriceOption $option)
    {
        $form = $this->createRegisterForm($option->getActivity());
        $form->get('single_option')->setData($option->getId());
        return $form;
    }

    private function createRegisterForm(Activity $activity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('activity_register', ['id' => $activity->getId()]))
            ->add('single_option', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'confirm'],
                'label' => 'Aanmelden',
            ])
            ->getForm()
        ;
    }
}
