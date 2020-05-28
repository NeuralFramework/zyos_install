<?php

    namespace ZyosInstallBundle\Component;

    use Symfony\Component\Filesystem\Filesystem;

    /**
     * Class SymfonyMirror
     *
     * @package ZyosInstallBundle\Component
     */
    class SymfonyMirror {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * SymfonyMirror constructor.
         *
         * @param Filesystem $filesystem
         */
        function __construct(Filesystem $filesystem) {
            $this->filesystem = $filesystem;
        }

        /**
         * Exists Directory
         *
         * @param string $path
         *
         * @return bool
         */
        public function exists(string $path): bool {
            return $this->filesystem->exists($path);
        }

        /**
         * Is a directory
         *
         * @param string $path
         *
         * @return bool
         */
        public function isDirectory(string $path): bool {
            return is_dir($path);
        }

        /**
         * Create Mirror
         *
         * @param string $origin
         *
         * @param string $destination
         */
        public function create(string $origin, string $destination): void {
            $this->filesystem->mirror($origin, $destination);
        }

        /**
         * Remove destination if exists directory
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