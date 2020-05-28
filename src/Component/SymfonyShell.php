<?php

    namespace ZyosInstallBundle\Component;


    use Symfony\Component\Process\Process;

    /**
     * Class SymfonyShell
     *
     * @package ZyosInstallBundle\Component
     */
    class SymfonyShell {

        /**
         * Execute command shell
         *
         * @param string $command
         *
         * @return string|null
         */
        public function run(string $command): ?string {

            $process = Process::fromShellCommandline($command);
            $process->run();

            while ($process->isRunning()):endwhile;
            return $process->getOutput();
        }

        /**
         * Execute command shell
         *
         * @param array $array
         *
         * @return string|null
         */
        public function runArray(array $array = []) {
            return $this->run($this->commandArray($array));
        }

        /**
         * Format command from array
         *
         * @param array $array
         *
         * @return string
         */
        private function commandArray(array $array = []): string {
            return implode(' ', array_filter($array));
        }
    }