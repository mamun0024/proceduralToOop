<?php

namespace Test;

require_once __DIR__ . '/../src/oop/Exceptions/BaseException.php';
require_once __DIR__ . '/../src/oop/Exceptions/FileNotExistsException.php';
require_once __DIR__ . '/../src/oop/Exceptions/FileDataFormatException.php';
require_once __DIR__ . '/../src/oop/Traits/HelperTrait.php';
require_once __DIR__ . '/../src/oop/CommissionFile.php';

use Oop\CommissionFile;
use Oop\Exceptions\FileDataFormatException;
use Oop\Exceptions\FileNotExistsException;
use Oop\Traits\HelperTrait;
use PHPUnit\Framework\TestCase;

class CommissionFileTest extends TestCase
{
    use HelperTrait;

    private $comm_file;

    protected function setUp(): void
    {
        $this->comm_file = new CommissionFile();
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

    public function testFileExistenceCheckReturnTrue()
    {
        $this->comm_file->setFileName('input_test1.txt');
        $this->comm_file->setFilePath('files/test/');
        try {
            $this->assertTrue($this->comm_file->checkFileExistence());
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        }
    }

    public function testFileExistenceCheckThrowException()
    {
        $this->comm_file->setFileName('input_test12.txt');
        $this->comm_file->setFilePath('files/test/');
        try {
            $this->comm_file->checkFileExistence();
        } catch (FileNotExistsException $e) {
            $this->assertStringContainsString('Input file is not exists', $e->errorMessage());
        }
    }

    public function testFileReadReturnDataArray()
    {
        $this->comm_file->setFileName('input_test1.txt');
        $this->comm_file->setFilePath('files/test/');
        try {
            $this->comm_file->checkFileExistence();
            $this->assertIsArray($this->comm_file->readFile());
        } catch (FileDataFormatException $e) {
            echo $e->errorMessage();
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        }
    }

    public function testFileReadThrowException()
    {
        $this->comm_file->setFileName('input_test13.txt');
        $this->comm_file->setFilePath('files/test/');
        try {
            $this->comm_file->checkFileExistence();
            $this->comm_file->readFile();
        } catch (FileDataFormatException $e) {
            $this->assertStringContainsString('File data format is not as expected', $e->errorMessage());
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        }
    }
}
