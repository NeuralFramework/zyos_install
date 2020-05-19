<?php

    namespace ZyosInstallBundle\DependencyInjection;

    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\Filesystem\Filesystem;
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

            $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
            $loader->load('services.yaml');

            $configuration = $this->getConfiguration($configs, $container);
            $config = $this->processConfiguration($configuration, $configs);

            foreach ($config AS $key => $value):
                $container->setParameter(sprintf('%s.%s', $this->getAlias(), $key), $value);
            endforeach;
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