<?php

namespace App\Controller\Admin\Person;

use App\Entity\Security\Auth;
use App\Entity\Person\Person;
use App\Entity\Document\Scheme\Scheme;
use App\Entity\Document\Document;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Mail\MailService;
use App\Template\Annotation\MenuItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\PasswordResetService;
use App\Security\AuthUserProvider;

/**
 * Person controller.
 *
 * @Route("/admin/person", name="admin_person_")
 */
class PersonController extends AbstractController
{
    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all Contact entities.
     *
     * @MenuItem(title="Personen", menu="admin", activeCriteria="admin_person_")
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $persons = $em->getRepository(Person::class)->findAll();
        $schemes = $em->getRepository(Scheme::class)->findAll();

        return $this->render('admin/person/index.html.twig', [
            'persons' => $persons,
            'schemes' => $schemes,
        ]);
    }
    
    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newSelectAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //Change this to be working later need repository for this to work.
        $schemes = $em->getRepository(Scheme::class)->findAll();
        if (0 == count($schemes)) {
            $this->addFlash('error', 'Kan geen persoon aanmaken zonder schema. Maak eerst een schema aan.');

            return $this->redirectToRoute('admin_document_scheme_new');
        } elseif (1 == count($schemes)) {
            return $this->redirectToRoute('admin_person_new_selected', ['id' => $schemes[0]->getId()]);
        }

        $form = $this->createForm('App\Form\Person\PersonSchemeSelectorType',null,['schemes' => $schemes]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scheme = $form['document']['scheme']->getData();

            return $this->redirectToRoute('admin_person_new_selected', ['id' => $scheme->getId()]);
        }

        return $this->render('admin/person/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new/{id}", name="new_selected", methods={"GET", "POST"})
     */
    public function newAction(Request $request, Scheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        $person = new Person();
        $document = new Document();
        $document->setScheme($scheme);
        $person->setDocument($document);

        $form = $this->createForm('App\Form\Person\PersonType',$person,['current_user' => $this->getUser()->getPerson()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($person);
            $em->persist($document);
            //Persist all new field values. 
            foreach ($person->getDocument()->getFieldValues() as $val){
                $em->persist($val);
            }
            $em->flush();

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a person entity.
     *
     * @Route("/{id}", name="show", methods={"GET", "POST"})
     */
    public function showAction(Request $request, Person $person)
    {
        $em = $this->getDoctrine()->getManager();

        $createdAt = $this->events->findOneBy($person, EntityNewEvent::class);
        $modifs = $this->events->findBy($person, EntityUpdateEvent::class);

        return $this->render('admin/person/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'person' => $person,
            //'form' => $form->createView(),
        ]);
    }

    /**
     * Add a login to person.
     *
     * @Route("/{id}/auth", name="auth", methods={"GET"})
     */
    public function authAction(Request $request, Person $person, PasswordResetService $passwordReset, AuthUserProvider $authProvider, MailService $mailer)
    {
        $em = $this->getDoctrine()->getManager();

        if (null !== $person->getAuth()) {
            $oldAuth = $person->getAuth();
            $person->setAuth(null);

            $em->remove($oldAuth);
            $em->flush();
        }

        $auth = new Auth();
        $auth
            ->setPerson($person)
            ->setAuthId($authProvider->usernameHash($person->getEmail()))
        ;

        $token = $passwordReset->generatePasswordRequestToken($auth);
        $auth->setPasswordRequestedAt(null);

        $em->persist($auth);
        $em->flush();

        $body = $this->renderView('email/newaccount.html.twig', [
            'name' => $person->getShortname(),
            'auth' => $auth,
            'token' => $token,
        ]);

        $mailer->message($person, 'Jouw account', $body);

        return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Person $person, AuthUserProvider $authProvider)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('App\Form\Person\PersonType',$person,['current_user' => $this->getUser()->getPerson()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auth = $person->getAuth();
            if ($auth){
                $auth->setAuthId($authProvider->usernameHash($person->getEmail()));
            }
            
            foreach ($person->getDocument()->getFieldValues() as $val){
                $em->persist($val);
            }
            $em->flush();

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{id}/scheme", name="scheme", methods={"GET", "POST"})
     */
    public function schemeSelectAction(Request $request, Person $person)
    {
        $em = $this->getDoctrine()->getManager();

        //Change this to be working later
        $schemes = $em->getRepository(Scheme::class)->findAll();
        $form = $this->createForm('App\Form\Person\PersonSchemeSelectorType', $person,['schemes' => $schemes]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            /*if (!is_null($person->getDocument()->getScheme()) && $person->getDocument()->getScheme()->getId() == $scheme->getId()) {
                $this->addFlash('error', $person->getCanonical().' heeft al '.$scheme->getName().' als schema, kies een ander schema!');

                return $this->render('admin/person/scheme.html.twig', [
                    'person' => $person,
                    'form' => $form->createView(),
                ]);
            }*/

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/scheme.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing person entity.
     *
     * @Route("/{person_id}/scheme/{scheme_id}", name="scheme_selected", methods={"GET", "POST"})
     * @ParamConverter("person", options={"id": "person_id"})
     * @ParamConverter("scheme", options={"id": "scheme_id"})
     */
    public function schemeAction(Request $request, Person $person, Scheme $scheme, AuthUserProvider $authProvider)
    {
        $em = $this->getDoctrine()->getManager();

        $document= $person->getDocument();
        $document->setScheme($scheme);


        $form = $this->createForm('App\Form\Person\PersonType', $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auth = $person->getAuth();
            $auth->setAuthId($authProvider->usernameHash($person->getEmail()));

            foreach ($person->getDocument()->getFieldValues() as $val){
                $em->persist($val);
            }

            $em->flush();

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a scheme entity of the person type.
     *
     * @Route("/scheme/{id}", name="show_scheme", methods={"GET", "POST"})
     */
    public function showSchemeAction(Scheme $scheme)
    {
        $em = $this->getDoctrine()->getManager();

        //Need repository function that find person class. 
        $persons = $em->getRepository(Person::class)->findAll();

        $createdAt = $this->events->findOneBy($scheme, EntityNewEvent::class);
        $modifs = $this->events->findBy($scheme, EntityUpdateEvent::class);

        return $this->render('admin/person/scheme/showScheme.html.twig', [
            'persons' => $persons,
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'scheme' => $scheme,
        ]);
    }


    /**
     * Deletes a person entity.
     *
     * @Route("/{id}/delete", name="delete")
     */
    public function deleteAction(Request $request, Person $person)
    {
        $form = $this->createDeleteForm($person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($person);
            $em->flush();

            return $this->redirectToRoute('admin_person_index');
        }

        return $this->render('admin/person/delete.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Person $person)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_person_delete', ['id' => $person->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
