<?php
namespace Romm\Formz\Tests\Fixture\Condition\Parser\Node;

use Romm\Formz\Condition\Parser\Node\AbstractNode;
use Romm\Formz\Condition\Processor\DataObject\PhpConditionDataObject;

class DefaultNode extends AbstractNode
{

    /**
     * @var string
     */
    protected $cssResult;

    /**
     * @var string
     */
    protected $javaScriptResult;

    /**
     * @var bool
     */
    protected $phpResult;

    /**
     * @return string
     */
    public function getCssResult()
    {
        return $this->cssResult;
    }

    /**
     * @param string $cssResult
     */
    public function setCssResult($cssResult)
    {
        $this->cssResult = $cssResult;
    }

    /**
     * @return string
     */
    public function getJavaScriptResult()
    {
        return $this->javaScriptResult;
    }

    /**
     * @param string $javaScriptResult
     */
    public function setJavaScriptResult($javaScriptResult)
    {
        $this->javaScriptResult = $javaScriptResult;
    }

    /**
     * @param PhpConditionDataObject $dataObject
     * @return bool
     */
    public function getPhpResult(PhpConditionDataObject $dataObject)
    {
        return $this->phpResult;
    }

    /**
     * @param bool $phpResult
     */
    public function setPhpResult($phpResult)
    {
        $this->phpResult = $phpResult;
    }
}
