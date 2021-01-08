<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 24/11/20
     * Time: 11:08 AM
     */
    namespace ZyosInstallBundle\Interfaces;

    /**
     * Interface ValidatorInterface
     *
     * @package ZyosInstallBundle\Interfaces
     */
    interface ValidatorInterface {

        /**
         * Generate validation of value
         *
         * @param       $value
         * @param array $params
         *
         * @return bool
         */
        public function validate($value, array $params = []): bool;

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string;

        /**
         * Get name function validation
         *
         * @return string
         */
        public static function getName(): string;
    }