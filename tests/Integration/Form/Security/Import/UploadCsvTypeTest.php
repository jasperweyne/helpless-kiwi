<?php

namespace App\Tests\Form\Security\Import;

use App\Form\Security\Import\ImportedAccounts;
use App\Form\Security\Import\UploadCsvType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validation;

class UploadCsvTypeTest extends TypeTestCase
{
    /**
     * @return array<int, ValidatorExtension>
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            /** @var UploadedFile&MockObject */
            'file' => $file = self::createMock(UploadedFile::class),
        ];

        $mockedObject = new ImportedAccounts([], $file);

        $form = $this->factory->create(UploadCsvType::class, $mockedObject);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($mockedObject, $form->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            /** @var UploadedFile&MockObject */
            'file' => $file = self::createMock(UploadedFile::class),
        ];

        $mockedObject = new ImportedAccounts([], $file);

        $form = $this->factory->create(UploadCsvType::class, $mockedObject);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertCount(0, $form->getErrors());
    }
}
