<?php

namespace App\Controller\Admin\Person;

use App\Entity\Security\Auth;
use App\Entity\Person\Person;
use App\Entity\Person\PersonField;
use App\Entity\Person\PersonValue;
use App\Log\EventService;
use App\Log\Doctrine\EntityNewEvent;
use App\Log\Doctrine\EntityUpdateEvent;
use App\Mail\MailService;
use App\Template\Annotation\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\PasswordResetService;
use App\Security\AuthUserProvider;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Person controller.
 *
 * @Route("/admin/person", name="admin_person_")
 */
class PersonController extends AbstractController
{
    const TYPES = [
        "string" => [
            "type" => TextType::class,
            "defs" => [],
        ],
        "switch" => [
            "type" => CheckboxType::class,
            "defs" => [],
        ],
    ];

    private $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    /**
     * Lists all Contact entities.
     *
     * @MenuItem(title="Personen", menu="admin", activeCriteria="admin_person_")
     * @Route("/", name="index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $persons = $em->getRepository(Person::class)->findAll();
        $fields = $em->getRepository(PersonField::class)->findAll();

        return $this->render('admin/person/index.html.twig', [
            'persons' => $persons,
            'fields' => $fields,
        ]);
    }

    /**
     * Creates a new activity entity.
     *
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $person = new Person();
        $fields = $em->getRepository(PersonField::class)->findAll();

        $form = $this->createForm('App\Form\Person\PersonType', $person, $this->buildFormFields($fields));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($person);

            foreach ($fields as $field) {
                $value = new PersonValue();
                $value
                    ->setPerson($person)
                    ->setField($field)
                    ->setValue($form[$field->getName()]->getData())
                ;
                $em->persist($value);
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
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function showAction(Person $person)
    {
        $em = $this->getDoctrine()->getManager();

        $createdAt = $this->events->findOneBy($person, EntityNewEvent::class);
        $modifs = $this->events->findBy($person, EntityUpdateEvent::class);

        return $this->render('admin/person/show.html.twig', [
            'createdAt' => $createdAt,
            'modifs' => $modifs,
            'person' => $person,
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
            'name' => $person->getFirstname(),
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

        $fields = $em->getRepository(PersonField::class)->findAll();
        $form = $this->createForm('App\Form\Person\PersonType', $person, $this->buildFormFields($fields));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auth = $person->getAuth();
            $auth->setAuthId($authProvider->usernameHash($person->getEmail()));
            $em->flush();

            return $this->redirectToRoute('admin_person_show', ['id' => $person->getId()]);
        }

        return $this->render('admin/person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
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

    private function buildFormFields($fields)
    {
        $parse = function ($field) {
            $type = TextType::class; 
            $opts = array();

            if (array_key_exists($field->getValueType(), self::TYPES)) {
                $type = self::TYPES[$field->getValueType()]['type'] ?? TextType::class;
                $opts = self::TYPES[$field->getValueType()]['defs'] ?? [];
            } else {
                throw new \UnexpectedValueException("Unknown person field type '"  . $field->getValueType() . "'");
            }

            return [
                'name' => $field->getName(),
                'type' => $type,
                'options' => $opts,
            ];
        };

        return [
            'fields' => array_map($parse, $fields),
        ];
    }
}
