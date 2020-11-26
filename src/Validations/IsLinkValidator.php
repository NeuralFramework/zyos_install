<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 05:45 AM
     */
    namespace ZyosInstallBundle\Validations;

    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class IsLinkValidator
     *
     * @package ZyosInstallBundle\Validations
     */
    class IsLinkValidator implements ValidatorInterface {

        /**
         * Generate validation of value
         *
         * @param       $value
         * @param array $params
         *
         * @return bool|null
         */
        public function validate($value, array $params = []): bool {
            return is_link($value);
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            return 'Es un enlace simbolico';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public function getName(): string {
            return 'is_link';
        }
    }