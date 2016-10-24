<?php
namespace Romm\Formz\Tests\Fixture\Form;

use Romm\Formz\Form\FormTrait;

class ExtendedForm extends DefaultForm
{

    use FormTrait;

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
