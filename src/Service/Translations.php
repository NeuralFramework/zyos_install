<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 29/09/20
     * Time: 01:34 AM
     */
    namespace ZyosInstallBundle\Service;

    use Symfony\Contracts\Translation\TranslatorInterface;

    /**
     * Class Translations
     *
     * @package ZyosInstallBundle\Service
     */
    class Translations {

        /**
         * @var string
         */
        const LOCALE = 'es';

        /**
         * @var TranslatorInterface
         */
        private $translator;

        /**
         * Translations constructor.
         *
         * @param TranslatorInterface $translator
         */
        function __construct(TranslatorInterface $translator) {
            $this->translator = $translator;
        }

        /**
         * Generate string translation
         *
         * @param string $text
         * @param array  $params
         * @param string $locale
         *
         * @return string
         */
        /* ts-ignore */
        public function translate(string $text, array $params = [], string $locale = self::LOCALE) {
            return $this->translator->trans($text, $params, $this->getDomain(), $locale);
        }

        /**
         * Generate string translation
         *
         * @param string $text
         * @param array  $params
         * @param string $locale
         *
         * @return string
         */
        public function translateHelp(string $text, array $params = [], string $locale = self::LOCALE) {
            return $this->translator->trans($text, $params, $this->getDomainHelp(), $locale);
        }

        /**
         * Get domain
         *
         * @return string
         */
        public function getDomain(): string {
            return 'zyos';
        }

        /**
         * Get help domain
         *
         * @return string
         */
        public function getDomainHelp(): string {
            return sprintf('%s_help', $this->getDomain());
        }
    }