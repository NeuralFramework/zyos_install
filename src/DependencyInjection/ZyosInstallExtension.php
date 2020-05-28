<?php

    namespace ZyosInstallBundle\DependencyInjection;

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

            $configuration = $this->getConfiguration($configs, $container);
            $config = $this->processConfiguration($configuration, $configs);

            $container->setParameter(sprintf('%s.paths.local', $this->getAlias()), $config['paths']['local']);
            $container->setParameter(sprintf('%s.paths.dump', $this->getAlias()), $config['paths']['dump']);
            $container->setParameter(sprintf('%s.paths.sql', $this->getAlias()), $config['paths']['sql']);

            $container->setParameter(sprintf('%s.install.enable', $this->getAlias()), $config['install']['enable']);
            $container->setParameter(sprintf('%s.install.configurations', $this->getAlias()), $config['install']['configurations']);

            $container->setParameter(sprintf('%s.symlinks.enable', $this->getAlias()), $config['symlinks']['enable']);
            $container->setParameter(sprintf('%s.symlinks.configurations', $this->getAlias()), $config['symlinks']['configurations']);

            $container->setParameter(sprintf('%s.mirrors.enable', $this->getAlias()), $config['mirrors']['enable']);
            $container->setParameter(sprintf('%s.mirrors.configurations', $this->getAlias()), $config['mirrors']['configurations']);

            $container->setParameter(sprintf('%s.dump.enable', $this->getAlias()), $config['dump']['enable']);
            $container->setParameter(sprintf('%s.dump.connections', $this->getAlias()), $config['dump']['connections']);

            $container->setParameter(sprintf('%s.sql_import.enable', $this->getAlias()), $config['sql_import']['enable']);
            $container->setParameter(sprintf('%s.sql_import.configurations', $this->getAlias()), $config['sql_import']['configurations']);

            $container->setParameter(sprintf('%s.commands.enable', $this->getAlias()), $config['commands']['enable']);
            $container->setParameter(sprintf('%s.commands.configurations', $this->getAlias()), $config['commands']['configurations']);

            $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
            $loader->load('services.yaml');
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