<?php
declare(strict_types=1);

namespace RZ\Roadiz\Documentation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Translation\Translator;

final class DocumentationServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     * @return void
     */
    public function register(Container $container)
    {
        $container->extend('translator', function (Translator $translator) {
            $dir = dirname(__FILE__) . '/Resources/translations';
            $availableLocales = ['ar', 'de', 'en', 'es', 'fr', 'id', 'it', 'ru', 'sr', 'tr', 'uk', 'zh'];
            foreach ($availableLocales as $locale) {
                $translator->addResource(
                    'xlf',
                    $dir . '/messages.'.$locale.'.xlf',
                    $locale,
                    'messages'
                );
            }
            return $translator;
        });
    }
}
