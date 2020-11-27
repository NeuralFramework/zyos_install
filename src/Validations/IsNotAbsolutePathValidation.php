<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 27/11/20
     * Time: 03:28 AM
     */
    namespace ZyosInstallBundle\Validations;

    use Symfony\Component\Filesystem\Filesystem;
    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class IsNotAbsolutePathValidation
     *
     * @package ZyosInstallBundle\Validations
     */
    class IsNotAbsolutePathValidation implements ValidatorInterface {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * IsNotAbsolutePathValidation constructor.
         *
         * @param Filesystem $filesystem
         */
        function __construct(Filesystem $filesystem) {
            $this->filesystem = $filesystem;
        }

        /**
         * Generate validation of value
         *
         * @param       $value
         * @param array $params
         *
         * @return bool
         */
        public function validate($value, array $params = []): bool {
            return !$this->filesystem->isAbsolutePath($value);
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            return 'No es una ruta absoluta';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public function getName(): string {
            return 'is_not_absolute_path';
        }
    }