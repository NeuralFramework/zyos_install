<?php

    namespace ZyosInstallBundle\Component;

    use Symfony\Component\Filesystem\Filesystem;

    /**
     * Class SymfonySymlink
     *
     * @package ZyosInstallBundle\Component
     */
    class SymfonySymlink {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * SymfonySymlink constructor.
         *
         * @param Filesystem $filesystem
         */
        function __construct(Filesystem $filesystem) {
            $this->filesystem = $filesystem;
        }

        /**
         * Exists resource
         *
         * @param string $resource
         *
         * @return bool
         */
        public function exists(string $resource): bool {
            return $this->filesystem->exists($resource);
        }

        /**
         * Is a symlink
         *
         * @param string $resource
         *
         * @return bool
         */
        public function isLink(string $resource): bool {
            return is_link($resource);
        }

        /**
         * Create symlink
         *
         * @param string $origin
         * @param string $destination
         */
        public function create(string $origin, string $destination): void {
            $this->filesystem->symlink($origin, $destination, true);
        }

        /**
         * Remove if exist destination
         *
         * @param string $origin
         * @param string $destination
         */
        public function createIfNotExists(string $origin, string $destination): void {

            if ($this->exists($destination)):
                $this->filesystem->remove($destination);
            endif;

            $this->create($origin, $destination);
        }
    }