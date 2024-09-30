<?php

namespace Tests\Integration\Form\Security\Import;

use App\Entity\Security\LocalAccount;
use App\Event\Security\CreateAccountsEvent;
use App\Event\Security\RemoveAccountsEvent;
use App\Form\Security\Import\ImportedAccounts;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImportedAccountsTest.
 *
 * @covers \App\Form\Security\Import\ImportedAccounts
 */
class ImportedAccountsTest extends KernelTestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private EntityManagerInterface&MockObject $entityManager;
    /** @var LocalAccount[] */
    private array $localAccounts;
    private UploadedFile $uploadedFile;
    private ImportedAccounts $importedAccounts;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->localAccounts = [new LocalAccount(), new LocalAccount()];
        $this->uploadedFile = $this->createMock(UploadedFile::class);
        $this->importedAccounts = new ImportedAccounts($this->localAccounts, $this->uploadedFile);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->dispatcher);
        unset($this->entityManager);
        unset($this->localAccounts);
        unset($this->uploadedFile);
        unset($this->importedAccounts);
    }

    public function testWillAddAndWillRemoveFlags(): void
    {
        self::assertTrue($this->importedAccounts->willAdd);
        self::assertFalse($this->importedAccounts->willRemove);

        $this->importedAccounts->willAdd = false;
        $this->importedAccounts->willRemove = true;

        self::assertFalse($this->importedAccounts->willAdd);
        self::assertTrue($this->importedAccounts->willRemove);
    }

    public function testExecuteImportWithNoAdditionsAndRemovals(): void
    {
        $this->importedAccounts->willAdd = false;
        $this->importedAccounts->willRemove = false;

        $this->dispatcher->expects(self::never())
            ->method('dispatch')
            ->with($this::isInstanceOf(CreateAccountsEvent::class));

        $this->dispatcher->expects(self::never())
            ->method('dispatch')
            ->with($this::isInstanceOf(RemoveAccountsEvent::class));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->importedAccounts->executeImport($this->dispatcher, $this->entityManager);
    }

    public function testExecuteImportWithAddition(): void
    {
        $account = new LocalAccount();
        $additions = new ArrayCollection([$account]);

        $this->importedAccounts->willAdd = true;
        $this->importedAccounts->willRemove = false;

        // Set the additions collection using reflection
        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $additionsProperty = $reflection->getProperty('additions');
        $additionsProperty->setAccessible(true);
        $additionsProperty->setValue($this->importedAccounts, $additions);

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new CreateAccountsEvent([$account]));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->importedAccounts->executeImport($this->dispatcher, $this->entityManager);

        $actualAdditions = $additionsProperty->getValue($this->importedAccounts);
        $this::assertInstanceOf(Collection::class, $actualAdditions);
        $this::assertEquals([$account], $actualAdditions->toArray());
    }

    public function testExecuteImportWithRemoval(): void
    {
        $account = new LocalAccount();
        $removals = new ArrayCollection([$account]);

        $this->importedAccounts->willAdd = false;
        $this->importedAccounts->willRemove = true;

        // Set the additions collection using reflection
        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $removalsProperty = $reflection->getProperty('removals');
        $removalsProperty->setAccessible(true);
        $removalsProperty->setValue($this->importedAccounts, $removals);

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new RemoveAccountsEvent([$account]));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->importedAccounts->executeImport($this->dispatcher, $this->entityManager);

        $actualRemovals = $removalsProperty->getValue($this->importedAccounts);
        self::assertInstanceOf(Collection::class, $actualRemovals);
        self::assertEquals([$account], $actualRemovals->toArray());
    }

    public function testGetAdditions(): void
    {
        $account1 = new LocalAccount();
        $account2 = new LocalAccount();
        $additions = new ArrayCollection([$account1, $account2]);

        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $additionsProperty = $reflection->getProperty('additions');
        $additionsProperty->setAccessible(true);
        $additionsProperty->setValue($this->importedAccounts, $additions);

        $result = $this->importedAccounts->getAdditions();

        $this::assertCount(2, $result);
        $this::assertEquals([$account1, $account2], $result->toArray());
    }

    public function testGetRemovals(): void
    {
        $account1 = new LocalAccount();
        $account2 = new LocalAccount();
        $removals = new ArrayCollection([$account1, $account2]);

        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $removalsProperty = $reflection->getProperty('removals');
        $removalsProperty->setAccessible(true);
        $removalsProperty->setValue($this->importedAccounts, $removals);

        $result = $this->importedAccounts->getRemovals();

        self::assertCount(2, $result);
        self::assertEquals([$account1, $account2], $result->toArray());
    }

    public function testGetUpdates(): void
    {
        $account1 = new LocalAccount();
        $account2 = new LocalAccount();
        $updates = new ArrayCollection([$account1, $account2]);

        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $updatesProperty = $reflection->getProperty('updates');
        $updatesProperty->setAccessible(true);
        $updatesProperty->setValue($this->importedAccounts, $updates);

        $result = $this->importedAccounts->getUpdates();

        self::assertCount(2, $result);
        self::assertEquals([$account1, $account2], $result->toArray());
    }

    public function testParseFileThrowsExceptionForNoFileLoaded(): void
    {
        $this->importedAccounts = new ImportedAccounts([], null);

        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $parseFileMethod = $reflection->getMethod('parseFile');
        $parseFileMethod->setAccessible(true);

        self::expectException(\InvalidArgumentException::class);

        $parseFileMethod->invoke($this->importedAccounts);
    }

    public function testParseFileThrowsExceptionForInvalidCSV(): void
    {
        $csvContent = "not,a,valid,cvs\n";

        $file = self::getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('getMimeType')->willReturn('text/csv');
        $file->method('getClientOriginalName')->willReturn('test.csv');
        $file->method('isValid')->willReturn(true);
        $file->method('getPathname')->willReturn('');
        $file->method('isReadable')->willReturn(true);
        $file->method('openFile')->willReturn(new \SplFileObject('data://text/plain;base64,'.base64_encode($csvContent)));

        $this->importedAccounts = new ImportedAccounts([]);

        $reflection = new \ReflectionClass(ImportedAccounts::class);
        $parseFileMethod = $reflection->getMethod('parseFile');
        $parseFileMethod->setAccessible(true);

        $fileProperty = $reflection->getProperty('file');
        $fileProperty->setAccessible(true);
        $fileProperty->setValue($this->importedAccounts, $file);

        self::expectException(\InvalidArgumentException::class);

        $parseFileMethod->invoke($this->importedAccounts);
    }
}
