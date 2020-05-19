<?php

    namespace ZyosInstallBundle;

    use Symfony\Component\HttpFoundation\ParameterBag;

    /**
     * Class MethodBag
     *
     * @package ZyosInstallBundle
     */
    class MethodBag extends ParameterBag {

        /**
         * MethodBag constructor.
         *
         * @param array $parameters
         */
        public function __construct(array $parameters = []) {
            parent::__construct($parameters);
        }

        /**
         * Return object MethodBag for array into key
         *
         * @param string $key
         *
         * @return MethodBag
         */
        public function self(string $key) {
            return new self($this->get($key, []));
        }

        /**
         * Merge array data
         *
         * @param string $key
         * @param array $params
         *
         * @return array
         */
        public function merge(string $key, array $params = []): array {
            return array_merge($this->get($key, []), $params);
        }
    }