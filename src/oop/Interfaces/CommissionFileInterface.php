<?php

namespace Oop\Interfaces;

interface CommissionFileInterface
{
    public function setFile($file);
    public function getFile();

    public function setFileName($file_name);
    public function getFileName();

    public function setFilePath($file_path);
    public function getFilePath();

    public function checkFileExistence();
    public function readFile();
}
