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
use Romm\Formz\Middleware\Processor\MiddlewareProcessor;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

class FormService
{
    /**
     * @var MiddlewareProcessor
     */
    private $processor;

    /**
     * @var FormMetadataRepository
     */
    protected $formMetadataRepository;

    /**
     * @param MiddlewareProcessor $processor
     */
    public function __construct(MiddlewareProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @return FormInterface
     */
    public function getFormInstance()
    {
        $formName = $this->processor->getFormObject()->getName();
        $argument = $this->processor->getRequestArguments()->getArgument($formName);
        $formArray = $this->getFormArray($formName);

        return $argument->setValue($formArray)->getValue();
    }

    /**
     * @param string $formName
     * @return array
     */
    protected function getFormArray($formName)
    {
        $formArray = $this->processor->getRequest()->getArgument($formName);
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
            $propertyMappingConfiguration = $this->processor->getRequestArguments()->getArgument($formName)->getPropertyMappingConfiguration();
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
        $request = $this->processor->getRequest();

        if ($request->hasArgument('fz-hash')) {
            $hash = $request->getArgument('fz-hash');
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
