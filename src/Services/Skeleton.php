<?php

    namespace ZyosInstallBundle\Services;

    use Symfony\Component\Filesystem\Filesystem;
    use ZyosInstallBundle\Parameters\Parameters;

    /**
     * Class Skeleton
     *
     * @package ZyosInstallBundle\Services
     */
    class Skeleton {

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * Skeleton constructor.
         *
         * @param Parameters $parameters
         * @param Filesystem $filesystem
         */
        public function __construct(Parameters $parameters, Filesystem $filesystem) {

            $this->parameters = $parameters;
            $this->filesystem = $filesystem;
        }

        /**
         * Validate and create structure
         *
         * @return $this
         */
        public function validate(): self {

            $this->createIfNotExists($this->getInstall());
            $this->createIfNotExists($this->getDump());
            $this->createIfNotExists($this->getSql());

            return $this;
        }

        /**
         * Path install
         *
         * @return mixed
         */
        public function getInstall(): string {
            return $this->parameters->getPathLocal();
        }

        /**
         * Exists Path Install
         *
         * @return bool
         */
        public function installExists(): bool {
            return $this->filesystem->exists($this->getInstall());
        }

        /**
         * Get Dump directory
         *
         * @return string
         */
        public function getDump(): string {
            return $this->parameters->getPathDump();
        }

        /**
         * Exists Dump Directory
         *
         * @return bool
         */
        public function dumpExists(): bool {
            return $this->filesystem->exists($this->getDump());
        }

        /**
         * Get SQL Directory
         *
         * @return string
         */
        public function getSql(): string {
            return $this->parameters->getPathSQL();
        }

        /**
         * Exists SQL Directory
         *
         * @return bool
         */
        public function sqlExists(): bool {
            return $this->filesystem->exists($this->getSql());
        }

        /**
         * Get lock file
         *
         * @return string
         */
        public function getLockFile(): string {
            return $this->parameters->getLockFile();
        }

        /**
         * Exists log file
         *
         * @return bool
         */
        public function lockFileExists(): bool {
            return $this->filesystem->exists($this->getLockFile());
        }

        /**
         * Create lock file
         */
        public function createLockFile(): void {
            $this->filesystem->dumpFile($this->getLockFile(), '');
        }

        /**
         * Crear directorio si no existe
         *
         * @param string $path
         */
        private function createIfNotExists(string $path): void {

            if (!$this->filesystem->exists($path)):
                $this->filesystem->mkdir($path);
                $this->createGitIgnore($path);
            endif;
        }

        /**
         * Create git ignore file
         *
         * @param string $path
         */
        private function createGitIgnore(string $path) {
            $this->filesystem->dumpFile(sprintf('%s/.gitignore', $path), '');
        }
    }