<?php

    namespace ZyosInstallBundle\Services;

    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Filesystem\Filesystem;

    /**
     * Class Skeleton
     *
     * @package ZyosInstallBundle\Services
     */
    class Skeleton {

        /**
         * @var ContainerInterface
         */
        private $container;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * Skeleton constructor.
         *
         * @param ContainerInterface $container
         * @param Filesystem $filesystem
         */
        public function __construct(ContainerInterface $container, Filesystem $filesystem) {

            $this->container = $container;
            $this->filesystem = $filesystem;
        }

        /**
         * Validate and create structure
         *
         * @return $this
         */
        public function validate(): self {

            $this->createIfNotExists(dirname($this->getInstall()));
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
            return $this->container->getParameter('zyos_install.path');
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
            return $this->container->getParameter('zyos_install.path_dump');
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
            return $this->container->getParameter('zyos_install.path_sql');
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
            return sprintf('%s/lock.lock', $this->getInstall());
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
         * Crear directorio si no existe
         *
         * @param string $path
         */
        private function createIfNotExists(string $path): void {

            if (!$this->filesystem->exists($path)):
                $this->filesystem->mkdir($path);
                $this->createGitIgnore($path);
                $this->createPermission($path);
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

        /**
         * Asignación de permisos
         *
         * @param string $path
         */
        private function createPermission(string $path): void {

            //$this->filesystem->chown($path, $this->container->getParameter('zyos_install.directory_permission'), true);
            //$this->filesystem->chgrp($path, $this->container->getParameter('zyos_install.apache_user'), true);
        }
    }