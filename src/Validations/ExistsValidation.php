<?php
    /**
     * Created by PhpStorm (Carlos Parra).
     * User: zyos
     * Email: neural.framework@gmail.com
     * Date: 25/11/20
     * Time: 05:27 AM
     */
    namespace ZyosInstallBundle\Validations;

    use Symfony\Component\Filesystem\Filesystem;
    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class ExistsValidation
     *
     * @package ZyosInstallBundle\Validations
     */
    class ExistsValidation implements ValidatorInterface {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * ExistsValidation constructor.
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
            return $this->filesystem->exists($value);
        }

        /**
         * Get description of validation
         *
         * @return string
         */
        public function getDescription(): string {
            return 'Existencia del recurso';
        }

        /**
         * Get name function validation
         *
         * @return string
         */
        public static function getName(): string {
            return 'exists';
        }
    }