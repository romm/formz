<?php
namespace Romm\Formz\Tests\Fixture\Form;

class ExtendedForm extends DefaultForm
{

    /**
     * @var array
     */
    protected $bar;

    /**
     * @return array
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * @param array $bar
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}
