<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\Utility;

use \TYPO3\CMS\Core\TimeTracker\TimeTracker as TYPO3TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Little utility to track the time used by certain functionality.
 */
class TimeTracker extends TYPO3TimeTracker
{

    /**
     * @var array
     */
    protected $logs = [];

    /**
     * @return TimeTracker
     */
    public static function getAndStart()
    {
        /** @var TimeTracker $timeTracker */
        $timeTracker = GeneralUtility::makeInstance(TimeTracker::class);
        $timeTracker->start();

        return $timeTracker;
    }

    /**
     * @param string $label
     */
    public function logTime($label)
    {
        $this->logs[$label] = $this->getDifferenceToStarttime();
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @return string
     */
    public function getHTMLCommentLogs()
    {
        $logs = [];
        foreach ($this->getLogs() as $label => $value) {
            $logs[] = "$label: $value";
        }

        return '<!-- parse [' . implode('][', $logs) . '] -->';
    }
}
