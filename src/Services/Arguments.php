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

        /**
         * Format arguments commands
         *
         * @param null $value
         * @param string|null $env
         *
         * @return array|string|null
         */
        public function format($value = null, ?string $env = null) {

            if (is_string($value)):
                return strtr($value, ['{{ arg_env }}' => is_null($env) ? $value : $env]);
            elseif (is_array($value)):
                return $this->formatArray($value, $env);
            else:
                return $value;
            endif;
        }

        /**
         * @param array $array
         * @param string|null $env
         *
         * @return array
         */
        private function formatArray(array $array = [], ?string $env = null): array {

            $list = [];
            if (count($array) > 0):
                foreach ($array as $key => $value):
                    $list[$key] = $this->format($value, $env);
                endforeach;
            endif;
            return $list;
        }
    }