<?php
namespace Romm\Formz\Tests\Unit\Form;

use Romm\Formz\Form\FormObjectHash;
use Romm\Formz\Tests\Unit\AbstractUnitTest;

class FormObjectHashTest extends AbstractUnitTest
{
    /**
     * Checking that the hash can be retrieved with its getter.
     *
     * @test
     */
    public function hashCanBeRetrieved()
    {
        /** @var FormObjectHash|\PHPUnit_Framework_MockObject_MockObject $formObjectHash */
        $formObjectHash = $this->getMockBuilder(FormObjectHash::class)
            ->setMethods(['calculateHash'])
            ->disableOriginalConstructor()
            ->getMock();
        $hash = 'foo';

        $formObjectHash->expects($this->any())
            ->method('calculateHash')
            ->willReturn($hash);

        $this->assertEquals($hash, $formObjectHash->getHash());

        unset($formObjectHash);
    }

    /**
     * The hash should be calculated only once, as it can lead to performance
     * issues if the object is used many times.
     *
     * @test
     */
    public function hashIsCalculatedOnlyOnce()
    {
        /** @var FormObjectHash|\PHPUnit_Framework_MockObject_MockObject $formObjectHash */
        $formObjectHash = $this->getMockBuilder(FormObjectHash::class)
            ->setMethods(['calculateHash'])
            ->disableOriginalConstructor()
            ->getMock();

        $formObjectHash->expects($this->once())
            ->method('calculateHash')
            ->willReturn('foo');

        for ($i = 0; $i < 3; $i++) {
            $formObjectHash->getHash();
        }

        unset($formObjectHash);
    }

    /**
     * @test
     */
    public function resetHashResetsHash()
    {
        /** @var FormObjectHash|\PHPUnit_Framework_MockObject_MockObject $formObjectHash */
        $formObjectHash = $this->getMockBuilder(FormObjectHash::class)
            ->setMethods(['calculateHash'])
            ->disableOriginalConstructor()
            ->getMock();

        $formObjectHash->expects($spy = $this->exactly(2))
            ->method('calculateHash')
            ->willReturn('foo');

        for ($i = 0; $i < 3; $i++) {
            $formObjectHash->getHash();
        }

        $this->assertEquals(1, $spy->getInvocationCount());

        $formObjectHash->resetHash();

        for ($i = 0; $i < 3; $i++) {
            $formObjectHash->getHash();
        }
    }
}
