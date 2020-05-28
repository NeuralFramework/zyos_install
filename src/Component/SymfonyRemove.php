<?php

    namespace ZyosInstallBundle\Component;

    use Symfony\Component\Filesystem\Filesystem;

    /**
     * Class SymfonyRemove
     *
     * @package ZyosInstallBundle\Component
     */
    class SymfonyRemove {

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * SymfonyRemove constructor.
         *
         * @param Filesystem $filesystem
         */
        function __construct(Filesystem $filesystem) {
            $this->filesystem = $filesystem;
        }

        /**
         * Exists file / directory
         *
         * @param string $source
         *
         * @return bool
         */
        public function exists(string $source): bool {
            return $this->filesystem->exists($source);
        }

        /**
         * Remove file / directory
         *
         * @param string $source
         */
        public function remove(string $source): void {
            $this->filesystem->remove($source);
        }

        /**
         * Remove if only exists
         *
         * @param string $source
         */
        public function removeIfExists(string $source): void {

            if ($this->exists($source)):
                $this->remove($source);
            endif;
        }
    }