<?php

namespace Oop;

class CalculateCommission
{
    private $settings;

    public function __construct($settings = [])
    {
        $this->settings = $settings['settings'];
    }

    private function readFile()
    {
        if (!file_exists("././" . $this->settings['file_name'])) {
            throw new FileNotExistsException($this->settings['file_name']);
        } else {
            $file = fopen("././" . $this->settings['file_name'], "r");
        }
    }

    public function run()
    {
        try {
            $this->readFile();
        } catch (FileNotExistsException $e) {
            echo $e->errorMessage();
        } catch (\Exception $e) {
            echo "Exception : " . $e->getMessage();
        }
    }
}
