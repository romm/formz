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

namespace Romm\Formz\Persistence\Item\Session;

use Romm\Formz\Core\Core;
use Romm\Formz\Domain\Model\FormMetadata;
use Romm\Formz\Exceptions\EntryNotFoundException;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Persistence\AbstractPersistence;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class SessionPersistence extends AbstractPersistence
{
    /**
     * @var int
     */
    protected $priority = 500;

    /**
     * @var FrontendUserAuthentication|BackendUserAuthentication
     */
    protected $user;

    /**
     * Initializes the user session, depending on the current TYPO3 environment.
     */
    public function initialize()
    {
        $environmentService = Core::instantiate(EnvironmentService::class);

        if ($environmentService->isEnvironmentInFrontendMode()) {
            $this->user = Core::get()->getPageController()->fe_user;
            $this->user->fetchSessionData();
        } else {
            $this->user = Core::get()->getBackendUser();
        }
    }

    /**
     * Checks that the form instance that matches the identifier exists in the
     * session.
     *
     * @param FormMetadata $metadata
     * @return bool
     */
    public function has(FormMetadata $metadata)
    {
        $identifier = $this->sanitizeIdentifier($metadata->getHash());

        return $this->user->getSessionData($identifier) !== null;
    }

    /**
     * Returns the form instance that matches the identifier. If it does not
     * exist, an exception is thrown.
     *
     * @param FormMetadata $metadata
     * @return FormInterface
     * @throws EntryNotFoundException
     */
    public function fetch(FormMetadata $metadata)
    {
        $this->checkInstanceCanBeFetched($metadata);

        $identifier = $this->sanitizeIdentifier($metadata->getHash());
        $form = $this->user->getSessionData($identifier);

        return $form;
    }

    /**
     * Adds the given form entry to the session.
     *
     * @param FormMetadata $metadata
     * @param FormInterface $form
     */
    public function save(FormMetadata $metadata, FormInterface $form)
    {
        $identifier = $this->sanitizeIdentifier($metadata->getHash());

        $this->user->setAndSaveSessionData($identifier, $form);
    }

    /**
     * Removes the given entry from session.
     *
     * @param FormMetadata $metadata
     * @return void
     */
    public function delete(FormMetadata $metadata)
    {
        $identifier = $this->sanitizeIdentifier($metadata->getHash());

        $this->user->setAndSaveSessionData($identifier, null);
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function sanitizeIdentifier($identifier)
    {
        return 'FormZ-' . $identifier;
    }
}
