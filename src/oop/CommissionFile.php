<?php

namespace Oop;

use Oop\Exceptions\FileDataFormatException;
use Oop\Exceptions\FileNotExistsException;
use Oop\Traits\HelperTrait;
use SplFileObject;

class CommissionFile
{
    use HelperTrait;

    private $file;
    private $file_name;
    private $file_path;

    /**
     * @return SplFileObject
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param SplFileObject $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @param string $file_name
     */
    public function setFileName($file_name): void
    {
        $this->file_name = $file_name;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        if ($this->emptyCheck($this->file_path)) {
            $path = $this->file_path;
            $f = substr($path, 0, 1);
            $l = substr($path, -1);

            if ($f == "/") {
                $path = ltrim($path, '/');
            }
            if ($l != "/") {
                $path .= "/";
            }
        } else {
            $path = null;
        }
        return $path;
    }

    /**
     * @param string $file_path
     */
    public function setFilePath($file_path): void
    {
        $this->file_path = $file_path;
    }

    /**
     * Read the input file and
     * check the existence.
     *
     * @return void|boolean
     * @throws FileNotExistsException
     */
    public function checkFileExistence()
    {
        if (!file_exists("././" . $this->getFilePath() . $this->getFileName())) {
            throw new FileNotExistsException();
        } else {
            $this->setFile(new SplFileObject("././" . $this->getFilePath() . $this->getFileName()));
            return true;
        }
    }

    /**
     * Read the input file.
     *
     * @return void|array
     * @throws FileDataFormatException
     */
    public function readFile()
    {
        $data = [];
        $input_file = $this->getFile();
        $i = 0;
        while (!$input_file->eof()) {
            $rowData = json_decode($input_file->fgets(), true);
            if (
                $this->emptyCheck($rowData['bin']) &&
                $this->emptyCheck($rowData['amount']) &&
                $this->emptyCheck($rowData['currency'])
            ) {
                $data[$i]['bin'] = $rowData['bin'];
                $data[$i]['amount'] = $rowData['amount'];
                $data[$i]['currency'] = $rowData['currency'];
                $i++;
            } else {
                throw new FileDataFormatException();
            }
        }
        return $data;
    }
}
