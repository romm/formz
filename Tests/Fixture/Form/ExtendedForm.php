<?php
namespace Romm\Formz\Tests\Fixture\Form;

class ExtendedForm extends DefaultForm
{

    /**
     * @var array
     */
    protected $bar;

    /**
     * @var string
     */
    public $publicProperty;

    /**
     * @var string
     */
    protected $notAccessibleProperty;

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
