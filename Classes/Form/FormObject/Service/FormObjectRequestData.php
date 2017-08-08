<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 FormZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Form\FormObject\Service;

use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Exceptions\InvalidArgumentValueException;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Security\Exception;

class FormObjectRequestData
{
    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $formHash;

    /**
     * @var string
     */
    protected $contentObjectTable;

    /**
     * @var int
     */
    protected $contentObjectUid;

    /**
     * The given argument must be a string, hashed with the hash service, and
     * must contain an instance of this class. The data will be fetched from the
     * instance, and injected in the actual class.
     *
     * @param string $hash
     */
    public function fillFromHash($hash)
    {
        try {
            /** @var FormObjectRequestData $requestData */
            $requestData = $this->hashService->validateAndStripHmac($hash);
        } catch (Exception $exception) {
            $this->throwFormDataException();
        }

        $requestData = base64_decode($requestData);

        if (false === $requestData) {
            $this->throwFormDataException();
        }

        $requestData = unserialize($requestData);

        if (false === $requestData) {
            $this->throwFormDataException();
        }

        foreach ($requestData as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasData($key)
    {
        return true === isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws EntryNotFoundException
     */
    public function getDataValue($key)
    {
        if (false === $this->hasData($key)) {
            throw EntryNotFoundException::formRequestDataNotFound($key);
        }

        return $this->data[$key];
    }

    /**
     * @return string
     */
    public function getFormHash()
    {
        return $this->formHash;
    }

    /**
     * @param string $formHash
     */
    public function setFormHash($formHash)
    {
        $this->formHash = $formHash;
    }

    /**
     * @return string
     */
    public function getContentObjectTable()
    {
        return $this->contentObjectTable;
    }

    /**
     * @param string $contentObjectTable
     */
    public function setContentObjectTable($contentObjectTable)
    {
        $this->contentObjectTable = $contentObjectTable;
    }

    /**
     * @return int
     */
    public function getContentObjectUid()
    {
        return (int)$this->contentObjectUid;
    }

    /**
     * @param int $contentObjectUid
     */
    public function setContentObjectUid($contentObjectUid)
    {
        $this->contentObjectUid = (int)$contentObjectUid;
    }

    /**
     * @param HashService $hashService
     */
    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        $properties = get_object_vars($this);
        unset($properties['hashService']);

        foreach (array_keys($properties) as $propertyName) {
            $result[$propertyName] = $this->$propertyName;
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentValueException
     */
    protected function throwFormDataException()
    {
        throw InvalidArgumentValueException::formDataFetchingError();
    }
}
