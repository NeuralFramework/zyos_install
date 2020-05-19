<?php

    namespace ZyosInstallBundle\Traits;

    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\OutputInterface;

    /**
     * Trait ExecuteCommand
     *
     * @package ZyosInstallBundle\Traits
     */
    trait ExecuteCommand {

        /**
         * Execute commands configured
         *
         * @param OutputInterface $output
         * @param string $command
         * @param array $arguments
         *
         * @return mixed
         */
        private function getExecute(OutputInterface $output, string $command, array $arguments = []) {

            if (!$this->commands->has($command)):
                throw new \RuntimeException('No es posible ejecutar este comando, no se encuentra su configuración');
            endif;

            $console = $this->getApplication()->find($this->commands->self($command)->get('command'));
            $input = new ArrayInput($this->commands->self($command)->merge('params', $arguments));
            return $console->run($input, $output);
        }
    }