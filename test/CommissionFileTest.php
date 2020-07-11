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

    private $commission_file;

    protected function setUp(): void
    {
        $this->commission_file = new CommissionFile();
    }

    public function testGetFilePath()
    {
        $this->commission_file->setFilePath('files');
        $this->assertEquals("files/", $this->commission_file->getFilePath());
        $this->commission_file->setFilePath('files/');
        $this->assertEquals("files/", $this->commission_file->getFilePath());
        $this->commission_file->setFilePath('/files');
        $this->assertEquals("files/", $this->commission_file->getFilePath());
        $this->commission_file->setFilePath('/files/');
        $this->assertEquals("files/", $this->commission_file->getFilePath());

        $this->commission_file->setFilePath('files/test');
        $this->assertEquals("files/test/", $this->commission_file->getFilePath());
        $this->commission_file->setFilePath('files/test/');
        $this->assertEquals("files/test/", $this->commission_file->getFilePath());
        $this->commission_file->setFilePath('/files/test');
        $this->assertEquals("files/test/", $this->commission_file->getFilePath());
        $this->commission_file->setFilePath('/files/test/');
        $this->assertEquals("files/test/", $this->commission_file->getFilePath());
    }

    public function testFileExistenceCheckReturnTrue()
    {
        $this->commission_file->setFileName('input_test1.txt');
        $this->commission_file->setFilePath('files/test/');
        try {
            $this->assertTrue($this->commission_file->checkFileExistence());
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        }
    }

    public function testFileExistenceCheckThrowException()
    {
        $this->commission_file->setFileName('input_test12.txt');
        $this->commission_file->setFilePath('files/test/');
        try {
            $this->commission_file->checkFileExistence();
        } catch (FileNotExistsException $e) {
            $this->assertStringContainsString('Input file is not exists', $e->errorMessage());
        }
    }

    public function testFileReadReturnDataArray()
    {
        $this->commission_file->setFileName('input_test1.txt');
        $this->commission_file->setFilePath('files/test/');
        try {
            $this->commission_file->checkFileExistence();
            $this->assertIsArray($this->commission_file->readFile());
        } catch (FileDataFormatException $e) {
            echo $e->errorMessage();
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        }
    }

    public function testFileReadThrowException()
    {
        $this->commission_file->setFileName('input_test13.txt');
        $this->commission_file->setFilePath('files/test/');
        try {
            $this->commission_file->checkFileExistence();
            $this->commission_file->readFile();
        } catch (FileDataFormatException $e) {
            $this->assertStringContainsString('File data format is not as expected', $e->errorMessage());
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        }
    }
}
