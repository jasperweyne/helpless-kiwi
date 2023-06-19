<?php

namespace Tests\Integration\Form\Security\Import;

use App\Form\Security\Import\ConfirmationType;
use App\Form\Security\Import\ImportedAccounts;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfirmationTypeTest.
 *
 * @covers \App\Form\Security\Import\ConfirmationType
 */
class ConfirmationTypeTest extends KernelTestCase
{
    protected ConfirmationType $confirmationType;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->confirmationType = new ConfirmationType();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->confirmationType);
    }

    public function testBindValidData(): void
    {
        $csvContent = "email,given_name,family_name,admin,oidc\n";

        /** @var UploadedFile&MockObject */
        $file = self::getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('getMimeType')->willReturn('text/csv');
        $file->method('getClientOriginalName')->willReturn('test.csv');
        $file->method('isValid')->willReturn(true);
        $file->method('getPathname')->willReturn('');
        $file->method('isReadable')->willReturn(true);
        $file->method('move')->willReturn(true);
        $file->method('openFile')->willReturn(new \SplFileObject('data://text/plain;base64,'.base64_encode($csvContent)));
        $type = new ImportedAccounts([], $file);

        $formData = [
            'willAdd' => true,
            'willRemove' => false,
        ];

        $formFactory = self::getContainer()->get(FormFactoryInterface::class);
        $form = $formFactory->create(ConfirmationType::class, $type, ['csrf_protection' => false]);

        $form->submit($formData);

        if (!$form->isValid()) {
            $errors = $form->getErrors(true, true);
            foreach ($errors as $error) {
                echo $error->getMessage()."\n";
            }
        }

        $this::assertTrue($form->isSynchronized());
        $this::assertTrue($form->isSubmitted());
        $this::assertTrue($form->isValid());
    }

    public function testConfigureOptions(): void
    {
        /** @var OptionsResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects(self::exactly(1))->method('setDefaults');
        $this->confirmationType->configureOptions($resolver);
    }
}
