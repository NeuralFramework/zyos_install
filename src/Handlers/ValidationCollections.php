<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 09:28 AM
     */
    namespace ZyosInstallBundle\Handlers;

    use ZyosInstallBundle\Service\ParameterBag;

    /**
     * Class ValidationCollections
     *
     * @package ZyosInstallBundle\Handlers
     */
    class ValidationCollections extends ParameterBag {

        /**
         * ValidationCollections constructor.
         *
         * @param iterable $handlers
         */
        function __construct(iterable $handlers) {
            $this->formatHandlers($handlers);
        }

        /**
         * Add list of format validations
         *
         * @param iterable $handlers
         *
         * @return void
         */
        private function formatHandlers(iterable $handlers): void {

            $array = iterator_to_array($handlers);
            foreach ($array AS $item):
                $this->set($item->getName(), $item);
            endforeach;
        }
    }