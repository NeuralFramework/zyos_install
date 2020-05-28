<?php

    namespace ZyosInstallBundle\Parameters;

    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Filesystem\Filesystem;

    /**
     * Class Parameters
     *
     * @package ZyosInstallBundle\Parameters
     */
    class Parameters {

        /**
         * @var ContainerInterface
         */
        private $container;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * Parameters constructor.
         *
         * @param ContainerInterface $container
         * @param Filesystem $filesystem
         */
        function __construct(ContainerInterface $container, Filesystem $filesystem) {

            $this->container = $container;
            $this->filesystem = $filesystem;
        }

        /**
         * @return string
         */
        public function getLockFile(): string {
            return sprintf('%s/lock.lock', $this->getPathLocal());
        }

        /**
         * @return bool
         */
        public function existsLockFile(): bool {
            return $this->filesystem->exists($this->getLockFile());
        }

        /**
         * @return string
         */
        public function getPathLocal(): string {
            return $this->container->getParameter('zyos_install.paths.local');
        }

        /**
         * @return bool
         */
        public function existsPathLocal(): bool {
            return $this->filesystem->exists($this->getPathLocal());
        }

        /**
         * @return string
         */
        public function getPathDump(): string {
            return $this->container->getParameter('zyos_install.paths.dump');
        }

        /**
         * @return bool
         */
        public function existsPathDump(): bool {
            return $this->filesystem->exists($this->getPathDump());
        }

        /**
         * @return string
         */
        public function getPathSQL(): string {
            return $this->container->getParameter('zyos_install.paths.sql');
        }

        /**
         * @return bool
         */
        public function existsPathSQL(): bool {
            return $this->filesystem->exists($this->getPathSQL());
        }

        public function getInstallEnable(): bool {
            return $this->container->getParameter('zyos_install.install.enable');
        }

        public function getInstallConfig(): array {
            return $this->container->getParameter('zyos_install.install.configurations');
        }

        /**
         * @return bool
         */
        public function getSymlinkEnable(): bool {
            return $this->container->getParameter('zyos_install.symlinks.enable');
        }

        /**
         * @return array
         */
        public function getSymlinkConfig(): array {
            return $this->container->getParameter('zyos_install.symlinks.configurations');
        }

        /**
         * @return bool
         */
        public function getMirrorEnable(): bool {
            return $this->container->getParameter('zyos_install.mirrors.enable');
        }

        /**
         * @return array
         */
        public function getMirrorConfig(): ?array {
            return $this->container->getParameter('zyos_install.mirrors.configurations');
        }

        /**
         * @return bool
         */
        public function getDumpEnable(): bool {
            return $this->container->getParameter('zyos_install.dump.enable');
        }

        /**
         * @return array
         */
        public function getDumpConnections(): array {
            return $this->container->getParameter('zyos_install.dump.connections');
        }

        /**
         * @return bool
         */
        public function getSQLImportEnable(): bool {
            return $this->container->getParameter('zyos_install.sql_import.enable');
        }

        /**
         * @return array
         */
        public function getSQLImportConfig(): array {
            return $this->container->getParameter('zyos_install.sql_import.configurations');
        }

        /**
         * @return bool
         */
        public function getCommandsEnable(): bool {
            return $this->container->getParameter('zyos_install.commands.enable');
        }

        /**
         * @return array
         */
        public function getCommandsConfig(): array {
            return $this->container->getParameter('zyos_install.commands.configurations');
        }
    }