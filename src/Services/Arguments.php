<?php

    namespace ZyosInstallBundle\Services;

    use Symfony\Component\Console\Input\InputInterface;

    /**
     * Class Arguments
     *
     * @package ZyosInstallBundle\Services
     */
    class Arguments {

        /**
         * @var array
         */
        private $environment;

        /**
         * Arguments constructor.
         */
        function __construct() {
            $this->environment = ['dev', 'prod'];
        }

        /**
         * Validate environment is valid
         *
         * @param InputInterface $input
         *
         * @return bool
         */
        public function validateEnvironment(InputInterface $input): bool {

            $env = $input->getArgument('environment');
            return in_array($env, $this->environment);
        }

        /**
         * Get environment
         *
         * @param InputInterface $input
         *
         * @return string
         */
        public function getEnvironment(InputInterface $input): string {

            $env = $input->getArgument('environment');
            return is_string($env) ? mb_strtolower($env) : 'prod';
        }
    }