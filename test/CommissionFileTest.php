<?php

namespace Test;

require_once __DIR__ . '/../oop/app/Exceptions/BaseException.php';
require_once __DIR__ . '/../oop/app/Exceptions/FileNotExistsException.php';
require_once __DIR__ . '/../oop/app/Exceptions/FileDataFormatException.php';
require_once __DIR__ . '/../oop/app/Traits/HelperTrait.php';
require_once __DIR__ . '/../oop/app/Interfaces/CommissionFileInterface.php';
require_once __DIR__ . '/../oop/app/CommissionFile.php';

use App\CommissionFile;
use App\Exceptions\FileDataFormatException;
use App\Exceptions\FileNotExistsException;
use App\Traits\HelperTrait;
use PHPUnit\Framework\TestCase;

class CommissionFileTest extends TestCase
{
    use HelperTrait;

    private $comm_file;

    protected function setUp(): void
    {
        $this->comm_file = new CommissionFile('input.txt', 'files');
    }

    public function testGetFilePath()
    {
        $this->comm_file->setFilePath('');
        $this->assertNull($this->comm_file->getFilePath());
        $this->comm_file->setFilePath(null);
        $this->assertNull($this->comm_file->getFilePath());

        $this->comm_file->setFilePath('files');
        $this->assertEquals("files/", $this->comm_file->getFilePath());
        $this->comm_file->setFilePath('files/');
        $this->assertEquals("files/", $this->comm_file->getFilePath());
        $this->comm_file->setFilePath('/files');
        $this->assertEquals("files/", $this->comm_file->getFilePath());
        $this->comm_file->setFilePath('/files/');
        $this->assertEquals("files/", $this->comm_file->getFilePath());

        $this->comm_file->setFilePath('files/test');
        $this->assertEquals("files/test/", $this->comm_file->getFilePath());
        $this->comm_file->setFilePath('files/test/');
        $this->assertEquals("files/test/", $this->comm_file->getFilePath());
        $this->comm_file->setFilePath('/files/test');
        $this->assertEquals("files/test/", $this->comm_file->getFilePath());
        $this->comm_file->setFilePath('/files/test/');
        $this->assertEquals("files/test/", $this->comm_file->getFilePath());
    }

    public function testCheckFileExistenceFunction()
    {
        $com_file = $this->getMockBuilder(CommissionFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $com_file->expects($this->any())
            ->method('checkFileExistence')
            ->will($this->returnValue(true));
        $this->assertTrue($com_file->checkFileExistence());

        try {
            $com_file->expects($this->any())
                ->method('checkFileExistence')
                ->willThrowException(new FileNotExistsException());
            $com_file->checkFileExistence();
        } catch (FileNotExistsException $e) {
            $this->assertStringContainsString('Input file is not exists.', $e->errorMessage());
        }
    }

    public function testReadFileFunction()
    {
        $com_file = $this->getMockBuilder(CommissionFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $com_file->expects($this->any())
            ->method('readFile')
            ->will($this->returnValue([]));
        $this->assertIsArray($com_file->readFile());

        try {
            $com_file->expects($this->any())
                ->method('readFile')
                ->willThrowException(new FileDataFormatException());
            $com_file->readFile();
        } catch (FileDataFormatException $e) {
            $this->assertStringContainsString('File data format is not as expected.', $e->errorMessage());
        }
    }
}
