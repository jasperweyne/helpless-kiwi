<?php

namespace App\Form\Security;

use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Expression;

class LocalAccountType extends AbstractType
{
    /** @var string[] */
    protected $emails;

    public function __construct(EntityManagerInterface $em)
    {
        $accounts = new ArrayCollection($em->getRepository(LocalAccount::class)->findAll());
        $this->emails = $accounts->map(fn (LocalAccount $account) => (string) $account->getEmail())->toArray();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('givenname', TextType::class, ['required' => true])
            ->add('familyname', TextType::class, ['required' => true])
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Expression([
                        'expression' => 'value not in emails',
                        'message' => 'Dit e-mailadres hoort al bij een gebruikersaccount',
                        'values' => ['emails' => $this->emails],
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocalAccount::class,
        ]);
    }
}
