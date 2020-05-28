<?php

    namespace ZyosInstallBundle\Services;

    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Style\SymfonyStyle;

    /**
     * Class Helpers
     *
     * @package ZyosInstallBundle\Services
     */
    class Helpers {

        /**
         * @var SymfonyStyle
         */
        private $io;

        /**
         * @param SymfonyStyle $io
         */
        public function setSymfonyStyle(SymfonyStyle $io): void {
            $this->io = $io;
        }

        /**
         * Get SymfonyStyle object
         *
         * @return SymfonyStyle
         */
        public function gettio(): SymfonyStyle {
            return $this->io;
        }

        /**
         * Create choice selection
         *
         * @param null $default
         * @param array $options
         *
         * @return int
         */
        public function getChoice($default = null, array $options = []): int {

            $option = $this->io->choice('Seleccione una opción', $options, $default);
            $choices = array_flip($options);
            return array_key_exists($option, $choices) ? $choices[$option] : null;
        }
    }