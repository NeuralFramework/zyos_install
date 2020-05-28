<?php

    namespace ZyosInstallBundle\Component;

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use ZyosInstallBundle\Parameters\Parameters;

    /**
     * Class SymfonyImportSQL
     *
     * @package ZyosInstallBundle\Component
     */
    class SymfonyImportSQL {

        /**
         * @var Parameters
         */
        private $parameters;

        /**
         * @var Filesystem
         */
        private $filesystem;

        /**
         * @var SymfonyCommand
         */
        private $symfonyCommand;

        /**
         * SymfonyImportSQL constructor.
         *
         * @param Parameters $parameters
         * @param Filesystem $filesystem
         * @param SymfonyCommand $symfonyCommand
         */
        function __construct(Parameters $parameters, Filesystem $filesystem, SymfonyCommand $symfonyCommand) {

            $this->parameters = $parameters;
            $this->filesystem = $filesystem;
            $this->symfonyCommand = $symfonyCommand;
        }

        /**
         * @param string $filename
         *
         * @return string
         */
        public function getFile(string $filename): string {
            return sprintf('%s/%s', $this->parameters->getPathSQL(), $filename);
        }

        /**
         * @param string $filename
         *
         * @return bool
         */
        public function existsFile(string $filename): bool {
            return $this->filesystem->exists($this->getFile($filename));
        }

        /**
         * @param array $array
         */
        public function existsFiles(array $array = []): void {

            foreach ($array AS $item):
                if (!$this->existsFile($item)):
                    throw new \RuntimeException(sprintf('No existe el archivo: %s', $this->getFile($item)));
                endif;
            endforeach;
        }

        /**
         * Execute command
         *
         * @param Application $application
         * @param OutputInterface $output
         * @param array $array
         *
         * @throws \Exception
         */
        public function executeCommand(Application $application, OutputInterface $output, array $array = []): void {

            foreach ($array AS $filename):
                $this->command($application, $output, $this->getFile($filename));
            endforeach;
        }

        /**
         * Execute command
         *
         * @param Application $application
         * @param OutputInterface $output
         * @param string $file
         *
         * @return int
         * @throws \Exception
         */
        private function command(Application $application, OutputInterface $output, string $file) {
            return $this->symfonyCommand->execute($application, $output, 'doctrine:database:import', ['file' => $file]);
        }
    }