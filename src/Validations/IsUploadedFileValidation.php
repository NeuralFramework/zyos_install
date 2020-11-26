<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 06:13 AM
     */
    namespace ZyosInstallBundle\Validations;

    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class IsUploadedFileValidation
     *
     * @package ZyosInstallBundle\Validations
     */
    class IsUploadedFileValidation implements ValidatorInterface {

        /**
         * Generate validation of value
         *
         * @param       $value
         * @param array $params
         *
         * @return bool|null
         */
        public function validate($value, array $params = []): bool {
            return is_uploaded_file($value);
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            return 'El archivo fue subido mediante HTTP POST';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public function getName(): string {
            return 'is_uploaded_file';
        }
    }