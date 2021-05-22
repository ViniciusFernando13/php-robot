<?php

namespace App\Services;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\WebDriverBy;

class ExternalFormService
{

    /**
     * fill external form by URL
     * @param string $url
     * @param Array $inputs
     * @return void
     */
    public function run(string $url, array $inputs, $driver)
    {

        // open driver with url
        $driver->get($url);

        // set values in inputs
        foreach ($inputs as $key => $value) {

            // makes the name to call
            $fill = 'fill' . $value['type'];
            $fill = ucfirst($fill);

            // call specific function
            $this->$fill($driver, $key, $value['value']);
        }

        // submit the form
        $btnSubmit = $driver->findElement(WebDriverBy::cssSelector('[type="submit"]'));
        if ($btnSubmit) $btnSubmit->click();
    }

    /**
     * Fill input text, textarea and file field
     *
     * @param ChromeDriver $driver
     * @param string $key
     * @param string $value
     * @return void
     */
    private function fillInput(ChromeDriver $driver, string $key, string $value)
    {

        // get input element
        $input = $driver->findElement(WebDriverBy::name($key));
        if ($input) {

            // clear input
            $input->clear();

            // fill
            $input->sendKeys($value);
        };
    }

    /**
     * Fill select
     *
     * @param ChromeDriver $driver
     * @param string $key
     * @param string|Array<string> $value
     * @return void
     */
    private function fillSelect(ChromeDriver $driver, string $key, $value)
    {
        $this->fillSelectOrCheckbox($driver, $key, $value, true);
    }

    /**
     * Fill checkbox or checkboxes field
     *
     * @param ChromeDriver $driver
     * @param string $key
     * @param string|Array<string> $value
     * @return void
     */
    private function fillCheckbox(ChromeDriver $driver, string $key, $value)
    {
        $this->fillSelectOrCheckbox($driver, $key, $value);
    }

    /**
     * Fill radio field
     *
     * @param ChromeDriver $driver
     * @param string $key
     * @param string $value
     * @return void
     */
    private function fillRadio(ChromeDriver $driver, string $key, string $value)
    {

        // get radios elements
        $inputs = $driver->findElements(WebDriverBy::name($key));
        foreach ($inputs as $input) {

            // click selected radio
            if ($input && $input->getAttribute('value') == $value) $input->click();
        }
    }

    /**
     * Fill select or checkbox or checkboxes field
     *
     * @param ChromeDriver $driver
     * @param string $key
     * @param string|Array<string> $value
     * @param boolean $isSelect
     * @return void
     */
    private function fillSelectOrCheckbox(ChromeDriver $driver, string $key, $value, $isSelect = false)
    {

        // verify multiple values
        if (is_array($value)) {

            // check type
            $inputs = $isSelect ? $driver->findElements(WebDriverBy::name($key . '[]')->tagName('option')) : $driver->findElements(WebDriverBy::name($key . '[]'));
            foreach ($inputs as $input) {

                // checks if value input is selected
                $checked = array_filter($value, function ($val) use ($input) {
                    return $val == $input->getAttribute('value');
                });
                if ($input && count($checked) != 0) $input->click();
                elseif ($input->getAttribute('selected') || $input->isSelected()) $input->click();
            }
        } else {
            // get input element
            $input = $driver->findElement(WebDriverBy::name($key)->cssSelector("[value=\"" . $value . "\"]"));
           
            // click selected value
            if ($input && !$input->isSelected()) $input->click();
        }
    }
}
