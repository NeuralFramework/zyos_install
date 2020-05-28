<?php

    namespace ZyosInstallBundle\Component;

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\OutputInterface;
    use ZyosInstallBundle\Services\Arguments;

    /**
     * Class SymfonyCommand
     *
     * @package ZyosInstallBundle\Component
     */
    class SymfonyCommand {

        /**
         * @var Arguments
         */
        private $arguments;

        /**
         * SymfonyCommand constructor.
         *
         * @param Arguments $arguments
         */
        function __construct(Arguments $arguments) {
            $this->arguments = $arguments;
        }

        /**
         * Execute command Symfony
         *
         * @param Application $application
         * @param OutputInterface $output
         * @param string $command
         * @param array $arguments
         * @param string|null $env
         *
         * @return int
         * @throws \Exception
         */
        public function execute(Application $application, OutputInterface $output, string $command, array $arguments = [], ?string $env = null) {

            $console = $application->find($command);
            $input = new ArrayInput($this->arguments->format($arguments, $env));
            return $console->run($input, $output);
        }
    }