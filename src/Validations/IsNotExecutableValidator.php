<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 06:00 AM
     */
    namespace ZyosInstallBundle\Validations;

    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class IsNotExecutableValidator
     *
     * @package ZyosInstallBundle\Validations
     */
    class IsNotExecutableValidator implements ValidatorInterface {

        /**
         * Generate validation of value
         *
         * @param       $value
         * @param array $params
         *
         * @return bool|null
         */
        public function validate($value, array $params = []): bool {
            return !is_executable($value);
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            return 'No es un ejecutable';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public function getName(): string {
            return 'is_not_executable';
        }
    }