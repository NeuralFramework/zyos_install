<?php

    namespace ZyosInstallBundle\DependencyInjection;

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use ZyosInstallBundle\Interfaces\ValidatorInterface;

    /**
     * Class ZyosInstallExtension
     *
     * @package ZyosInstallBundle\DependencyInjection
     */
    class ZyosInstallExtension extends Extension {

        /**
         * Loads a specific configuration.
         *
         * @param array $configs
         * @param ContainerBuilder $container
         * @throws \Exception
         */
        public function load(array $configs, ContainerBuilder $container): void {

            $container->registerForAutoconfiguration(ValidatorInterface::class)->addTag('zyos_install.validators');

            $configuration = $this->getConfiguration($configs, $container);
            $config = $this->processConfiguration($configuration, $configs);

            $this->setContainerItem($container, $config, 'translation', 'translation', 'es');
            $this->setContainerItem($container, $config, 'environments', 'environments', ['dev', 'prod']);
            $this->setContainerItem($container, $config, 'path', 'path', '%kernel.project_dir%/src/Resources/install');

            $this->setContainerItem($container, array_key_exists('install', $config) ? $config['install'] : [], 'enable', 'install.enable', false);
            $this->setContainerItem($container, array_key_exists('install', $config) ? $config['install'] : [], 'commands', 'install.commands', []);

            $this->setContainerItem($container, array_key_exists('symlink', $config) ? $config['symlink'] : [], 'enable', 'symlink.enable', false);
            $this->setContainerItem($container, array_key_exists('symlink', $config) ? $config['symlink'] : [], 'lockable', 'symlink.lockable', true);
            $this->setContainerItem($container, array_key_exists('symlink', $config) ? $config['symlink'] : [], 'commands', 'symlink.commands', []);

            $this->setContainerItem($container, array_key_exists('mirror', $config) ? $config['mirror'] : [], 'enable', 'mirror.enable', false);
            $this->setContainerItem($container, array_key_exists('mirror', $config) ? $config['mirror'] : [], 'lockable', 'mirror.lockable', true);
            $this->setContainerItem($container, array_key_exists('mirror', $config) ? $config['mirror'] : [], 'commands', 'mirror.commands', []);

            $this->setContainerItem($container, array_key_exists('sql', $config) ? $config['sql'] : [], 'enable', 'sql.enable', false);
            $this->setContainerItem($container, array_key_exists('sql', $config) ? $config['sql'] : [], 'lockable', 'sql.lockable', true);
            $this->setContainerItem($container, array_key_exists('sql', $config) ? $config['sql'] : [], 'commands', 'sql.commands', []);

            $this->setContainerItem($container, array_key_exists('cli', $config) ? $config['cli'] : [], 'enable', 'cli.enable', false);
            $this->setContainerItem($container, array_key_exists('cli', $config) ? $config['cli'] : [], 'lockable', 'cli.lockable', true);
            $this->setContainerItem($container, array_key_exists('cli', $config) ? $config['cli'] : [], 'commands', 'cli.commands', []);

            $this->setContainerItem($container, array_key_exists('validation', $config) ? $config['validation'] : [], 'enable', 'validation.enable', false);
            $this->setContainerItem($container, array_key_exists('validation', $config) ? $config['validation'] : [], 'lockable', 'validation.lockable', true);
            $this->setContainerItem($container, array_key_exists('validation', $config) ? $config['validation'] : [], 'commands', 'validation.commands', []);

            $this->setContainerItem($container, array_key_exists('export', $config) ? $config['export'] : [], 'enable', 'export.enable', false);
            $this->setContainerItem($container, array_key_exists('export', $config) ? $config['export'] : [], 'lockable', 'export.lockable', true);
            $this->setContainerItem($container, array_key_exists('export', $config) ? $config['export'] : [], 'commands', 'export.commands', []);

            $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
            $loader->load('services.yaml');
        }

        /**
         * Set parameters
         *
         * @param ContainerBuilder $container
         * @param array            $array
         * @param string           $key
         * @param string           $alias
         * @param bool             $default
         *
         * @return void
         */
        private function setContainerItem(ContainerBuilder $container, array $array = [], string $key, string $alias, $default = false): void {

            $defaults = array_key_exists($key, $array) ? $array[$key] : $default;
            $container->setParameter(sprintf('%s.%s', $this->getAlias(), $alias), $defaults);
        }

        /**
         * Alias
         *
         * @return string
         */
        public function getAlias(): string {
            return 'zyos_install';
        }
    }