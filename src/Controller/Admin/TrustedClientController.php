<?php

namespace App\Controller\Admin;

use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Form\Security\GenerateTokenType;
use App\Repository\ApiTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Security controller.
 */
#[Route("/admin/security/client", name: "admin_security_client_", priority: 10)]
class TrustedClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Lists all local account entities.
     */
    #[Route("/", name: "index", methods: ["GET"])]
    public function indexAction(): Response
    {
        $clients = $this->em->getRepository(TrustedClient::class)->findAll();

        return $this->render('admin/security/client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * Creates a new activity entity.
     */
    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function newAction(Request $request, PasswordHasherFactoryInterface $factory): Response
    {
        $form = $this->createFormBuilder()
            ->add('id')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $form->get('id')->getData();
            $secret = base64_encode(random_bytes(1024 / 8));
            $hashed = $factory->getPasswordHasher(TrustedClient::class)->hash($secret);
            assert(is_string($id));

            $this->em->persist(new TrustedClient($id, $hashed));
            $this->em->flush();

            $this->addFlash('success', "Client '$id' gecreeerd met secret '$secret'");

            return $this->redirectToRoute('admin_security_client_index');
        }

        return $this->render('admin/security/client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a new activity entity.
     */
    #[Route("/clear", name: "clear", methods: ["GET"])]
    public function clearAction(ApiTokenRepository $repository): Response
    {
        $amount = $repository->cleanup();

        $this->addFlash('success', "$amount verlopen tokens zijn opgeruimd");

        return $this->redirectToRoute('admin_security_client_index');
    }

    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/{id}/token", name: "token")]
    public function tokenAction(Request $request, TrustedClient $client, ApiTokenRepository $repository): Response
    {
        $form = $this->createForm(GenerateTokenType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            assert(is_array($data));
            assert($data['account'] instanceof LocalAccount);
            assert($data['expiresAt'] instanceof \DateTime);

            $secret = $repository->generate($data['account'], $client, \DateTimeImmutable::createFromMutable($data['expiresAt']));
            $this->addFlash('success', "Token gecreeerd met secret '$secret'");

            return $this->redirectToRoute('admin_security_client_index');
        }

        return $this->render('admin/security/client/token.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
    /**
     * Deletes a ApiKey entity.
     */
    #[Route("/{id}/delete", name: "delete")]
    public function deleteAction(Request $request, TrustedClient $client): Response
    {
        $form = $this->createDeleteForm($client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($client);
            $this->em->flush();

            $this->addFlash('success', "Client {$client->id} en {$client->tokens->count()} bijbehorende tokens verwijderd");

            return $this->redirectToRoute('admin_security_client_index');
        }

        return $this->render('admin/security/client/delete.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to check out all checked in users.
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(TrustedClient $client): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_security_client_delete', ['id' => $client->id]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
