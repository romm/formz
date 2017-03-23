<?php
namespace Romm\Formz\Tests\Fixture\Configuration;

use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

class FormzConfiguration
{

    /**
     * Returns the default configuration for FormZ.
     *
     * Will parse the default FormZ TypoScript configuration file, located at:
     * `EXT:formz/Configuration/TypoScript/Configuration/Settings.ts` and
     * convert it to a plain array. The array is then returned.
     *
     * @return array
     */
    public static function getDefaultConfiguration()
    {
        $typoScriptConfiguration = file_get_contents(realpath(dirname(__FILE__)) . '/../../../Configuration/TypoScript/Configuration/Settings.ts');

        $typoScriptParser = new TypoScriptParser;
        $typoScriptParser->parse($typoScriptConfiguration);

        $typoScriptService = new TypoScriptService;

        $configuration = $typoScriptService->convertTypoScriptArrayToPlainArray($typoScriptParser->setup);

        // We force the type of backend cache.
        $configuration = ArrayUtility::setValueByPath(
            $configuration,
            'config.tx_formz.settings.defaultBackendCache',
            TransientMemoryBackend::class
        );

        return $configuration;
    }
}
