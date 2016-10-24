<?php
namespace Romm\Formz\Tests\Fixture\Form;

class ExtendedForm extends DefaultForm
{

    /**
     * @var string
     */
    protected $bar;

    /**
     * @return string
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * @param string $bar
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}
