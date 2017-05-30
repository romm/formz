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

namespace Romm\Formz\Validation\Validator;

use Romm\Formz\Core\Core;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class PageExistsValidator extends AbstractValidator
{
    const MESSAGE_DEFAULT = 'default';

    /**
     * @inheritdoc
     */
    protected $supportedMessages = [
        self::MESSAGE_DEFAULT => [
            'key'       => 'validator.page_exists.error',
            'extension' => null
        ]
    ];

    /**
     * @inheritdoc
     */
    public function isValid($uid)
    {
        $page = Core::get()->getDatabase()->exec_SELECTgetSingleRow(
            'uid',
            'pages',
            'deleted=0 AND uid=' . (int)$uid
        );

        if (false === is_array($page)
            || false === isset($page['uid'])
            || $uid != $page['uid']
        ) {
            $this->addError(
                $this->translateErrorMessage('validator.page_exists.error', 'formz', [$uid]),
                1491829709
            );
        }
    }
}
