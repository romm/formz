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

namespace Romm\Formz\Middleware\Item\Begin\Service;

use Romm\Formz\Domain\Repository\FormMetadataRepository;
use Romm\Formz\Form\FormInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 * @todo rename and move service somewhere else, as it is used both in the
 * Ajax controller and the begin middleware. Maybe move it inside the FormObject?
 */
class FormService
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Arguments
     */
    protected $requestArguments;

    /**
     * @var FormMetadataRepository
     */
    protected $formMetadataRepository;

    /**
     * @param Request $request
     * @param Arguments $requestArguments
     */
    public function __construct(Request $request, Arguments $requestArguments)
    {
        $this->request = $request;
        $this->requestArguments = $requestArguments;
    }

    /**
     * @param string $formName
     * @return FormInterface
     */
    public function getFormInstance($formName)
    {
        $argument = $this->requestArguments->getArgument($formName);
        $formArray = $this->getFormArray($formName);

        return $argument->setValue($formArray)->getValue();
    }

    /**
     * @param string $formName
     * @return array
     */
    protected function getFormArray($formName)
    {
        $formArray = $this->request->getArgument($formName);
        $formArray = is_array($formArray)
            ? $formArray
            : [];

        $identifier = $this->getFormIdentifier();

        if ($identifier) {
            /*
             * Forcing the identity of the object, that will be handled
             * internally by Extbase.
             */
            $formArray['__identity'] = $identifier;
            $propertyMappingConfiguration = $this->requestArguments->getArgument($formName)->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->setTypeConverterOption(
                PersistentObjectConverter::class,
                PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED,
                true
            );
        } else {
            // Making sure no identity has been forced by the user.
            unset($formArray['__identity']);
        }

        return $formArray;
    }

    /**
     * Fetches the form identifier from the metadata, using the hash passed to
     * the request.
     *
     * @return string
     */
    protected function getFormIdentifier()
    {
        if ($this->request->hasArgument('fz-hash')) {
            $hash = $this->request->getArgument('fz-hash');
            $metaData = $this->formMetadataRepository->findOneByHash($hash);

            if ($metaData) {
                return $metaData->getIdentifier();
            }
        }

        return null;
    }

    /**
     * @param FormMetadataRepository $formMetadataRepository
     */
    public function injectFormMetadataRepository(FormMetadataRepository $formMetadataRepository)
    {
        $this->formMetadataRepository = $formMetadataRepository;
    }
}
