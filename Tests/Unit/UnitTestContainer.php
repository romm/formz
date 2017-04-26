<?php
namespace Romm\Formz\Tests\Unit;

use Romm\Formz\Service\InstanceService;
use Romm\Formz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Extbase\Object\Container\ClassInfo;
use TYPO3\CMS\Extbase\Object\Container\Container;

/**
 * Overrides Extbase default objects container, and adds the possibility to
 * register an object instance (generally a mock) for a given class name.
 */
class UnitTestContainer extends Container
{
    use SelfInstantiateTrait;

    /**
     * @var array
     */
    protected $mockedInstances = [];

    /**
     * @inheritdoc
     */
    protected function instanciateObject(ClassInfo $classInfo, array $givenConstructorArguments)
    {
        if (isset($this->mockedInstances[$classInfo->getClassName()])) {
            return $this->mockedInstances[$classInfo->getClassName()];
        } else {
            return parent::instanciateObject($classInfo, $givenConstructorArguments);
        }
    }

    /**
     * @param string $className
     * @param object $instance
     */
    public function registerMockedInstance($className, $instance)
    {
        InstanceService::get()->forceInstance($className, $instance);

        $this->mockedInstances[$className] = $instance;
    }

    /**
     * @param string $className
     * @return object|null
     */
    public function getMockedInstance($className)
    {
        return (isset($this->mockedInstances[$className]))
            ? $this->mockedInstances[$className]
            : null;
    }

    /**
     * Empty the instances array.
     */
    public function resetInstances()
    {
        InstanceService::get()->reset();

        $blankInstance = new static;
        $reflectionClass = new \ReflectionClass(get_parent_class());

        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $property->setValue($this, $property->getValue($blankInstance));
        }
    }
}
