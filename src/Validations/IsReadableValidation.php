<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 06:02 AM
     */
    namespace ZyosInstallBundle\Validations;

    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class IsReadableValidation
     *
     * @package ZyosInstallBundle\Validations
     */
    class IsReadableValidation implements ValidatorInterface {

        /**
         * Generate validation of value
         *
         * @param       $value
         * @param array $params
         *
         * @return bool|null
         */
        public function validate($value, array $params = []): bool {
            return is_readable($value);
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            return 'Se puede leer';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public function getName(): string {
            return 'is_readable';
        }
    }