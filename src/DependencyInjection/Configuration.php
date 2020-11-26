<?php

    namespace ZyosInstallBundle\DependencyInjection;

    use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
    use Symfony\Component\Config\Definition\Builder\TreeBuilder;
    use Symfony\Component\Config\Definition\ConfigurationInterface;

    /**
     * Class Configuration
     *
     * @package ZyosInstallBundle\DependencyInjection
     */
    class Configuration implements ConfigurationInterface {

        /**
         * Generates the configuration tree builder.
         *
         * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
         */
        public function getConfigTreeBuilder() {

            $treeBuilder = new TreeBuilder('zyos_install');
            $rootNode = $treeBuilder->getRootNode();

            $this->getConfigTranslation($rootNode);
            $this->getConfigEnv($rootNode);
            $this->getConfigPath($rootNode);
            $this->getConfigInstall($rootNode);
            $this->getConfigSymlink($rootNode);
            $this->getConfigMirror($rootNode);
            $this->getConfigSQL($rootNode);
            $this->getConfigCLI($rootNode);
            $this->getConfigValidations($rootNode);
            $this->getConfigDump($rootNode);

            return $treeBuilder;
        }

        /**
         * return configuration translations
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigTranslation(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->enumNode('translation')
                        ->info('Traducción de texto / Texts translations')
                        ->values(['es', 'en'])
                        ->defaultValue('es')
                    ->end()
                ->end();
        }

        /**
         * Return list environments
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigEnv(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('environments')
                        ->info('Entornos de aplicación / Environments Application')
                        ->beforeNormalization()
                            ->ifString()->then(function ($v) { return empty($v) ? ['dev', 'prod'] : [$v]; })
                        ->end()
                        ->prototype('scalar')->end()
                        ->defaultValue(['dev', 'prod'])
                        ->requiresAtLeastOneElement()
                    ->end()
                ->end();
        }

        /**
         * Return path install
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigPath(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->scalarNode('path')
                        ->info('Path de configuración / Path configuration')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return '%kernel.project_dir%/src/Resources/install'; })
                        ->end()
                        ->defaultValue('%kernel.project_dir%/src/Resources/install')
                    ->end()
                ->end();
        }

        /**
         * Return install configuration
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigInstall(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('install')
                        ->info('Comandos de Instalación / Install Commands')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->treatNullLike([])
                                ->prototype('array')
                                    ->validate()
                                        ->always(function ($v) {
                                            if (!array_key_exists('arguments', $v)) {
                                                $v['arguments'] = [];
                                            }
                                            return $v;
                                        })
                                    ->end()
                                    ->children()

                                        ->booleanNode('enable')
                                            ->defaultFalse()
                                            ->isRequired()
                                        ->end()
                                        ->append($this->getFieldEnvironments())
                                        ->scalarNode('command')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->arrayNode('arguments')
                                            ->info('Argumentos del comando.')
                                            ->normalizeKeys(false)
                                            ->ignoreExtraKeys(false)
                                            ->beforeNormalization()
                                                ->ifEmpty()->then(function ($v) { return []; })
                                            ->end()
                                            ->treatNullLike([])
                                            ->treatFalseLike([])
                                        ->end()

                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Return symlink configuration
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigSymlink(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('symlink')
                        ->info('Creación de Symlinks / Create Symlinks')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'lockable' => true, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'lockable' => true, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->booleanNode('lockable')->defaultTrue()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->treatNullLike([])
                                ->prototype('array')
                                    ->children()

                                        ->booleanNode('enable')
                                            ->defaultFalse()
                                            ->isRequired()
                                        ->end()
                                        ->append($this->getFieldEnvironments())
                                        ->scalarNode('origin')
                                            ->isRequired()
                                            ->beforeNormalization()
                                                ->ifString()->then(function ($v) { return realpath($v); })
                                            ->end()
                                        ->end()
                                        ->scalarNode('destination')
                                            ->isRequired()
                                            ->beforeNormalization()
                                                ->ifString()->then(function ($v) { return '%kernel.project_dir%/'.ltrim($v, DIRECTORY_SEPARATOR); })
                                            ->end()
                                        ->end()

                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Return mirror configuration
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigMirror(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('mirror')
                        ->info('Creación de Mirror / Create Mirror')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'lockable' => true, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'lockable' => true, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->booleanNode('lockable')->defaultTrue()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->treatNullLike([])
                                ->prototype('array')
                                    ->children()

                                        ->booleanNode('enable')
                                            ->defaultFalse()
                                            ->isRequired()
                                        ->end()
                                        ->append($this->getFieldEnvironments())
                                        ->scalarNode('origin')
                                            ->isRequired()
                                            ->beforeNormalization()
                                                ->ifString()->then(function ($v) { return realpath($v); })
                                            ->end()
                                        ->end()
                                        ->scalarNode('destination')
                                            ->isRequired()
                                            ->beforeNormalization()
                                                ->ifString()->then(function ($v) { return '%kernel.project_dir%/'.ltrim($v, DIRECTORY_SEPARATOR); })
                                            ->end()
                                        ->end()

                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Get configuration sql
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigSQL(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('sql')
                        ->info('Cargar Archivos SQL / Load SQL files')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'lockable' => true, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'lockable' => true, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->booleanNode('lockable')->defaultTrue()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->treatNullLike([])
                                ->prototype('array')
                                    ->children()

                                        ->booleanNode('enable')->defaultFalse()->isRequired()->end()
                                        ->append($this->getFieldEnvironments())
                                        ->scalarNode('connection')
                                            ->defaultNull()
                                        ->end()
                                        ->arrayNode('files')
                                            ->beforeNormalization()
                                                ->ifString()->then(function ($v) { return empty($v) ? [] : [$v]; })
                                            ->end()
                                            ->prototype('scalar')->end()
                                            ->isRequired()
                                            ->defaultValue([])
                                            ->requiresAtLeastOneElement()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Get configuration terminal
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigCLI(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('cli')
                        ->info('Ejecutar Comandos en terminal / Execute commands in terminal')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'lockable' => true, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'lockable' => true, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->booleanNode('lockable')->defaultTrue()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->treatNullLike([])
                                ->prototype('array')
                                    ->children()

                                        ->booleanNode('enable')->defaultFalse()->isRequired()->end()
                                        ->append($this->getFieldEnvironments())
                                        ->scalarNode('command')
                                            ->cannotBeEmpty()
                                            ->isRequired()
                                            ->defaultNull()
                                        ->end()

                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Get configuration validations
         *
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigValidations(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('validation')
                        ->info('Ejecutar Validaciones / Execute validations')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'lockable' => true, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'lockable' => true, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->booleanNode('lockable')->defaultTrue()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->treatNullLike([])
                                ->prototype('array')
                                    ->children()

                                        ->booleanNode('enable')->defaultFalse()->isRequired()->end()
                                        ->append($this->getFieldEnvironments())
                                        ->scalarNode('filepath')
                                            ->cannotBeEmpty()
                                            ->isRequired()
                                            ->defaultNull()
                                        ->end()
                                        ->arrayNode('validations')
                                            ->prototype('array')
                                                ->validate()
                                                    ->always(function ($v) {
                                                        if (!array_key_exists('params', $v)):
                                                            $v['params'] = [];
                                                        endif;
                                                        return $v;
                                                    })
                                                ->end()
                                                ->children()
                                                    ->scalarNode('validation')->isRequired()->cannotBeEmpty()->end()
                                                    ->arrayNode('params')
                                                        ->normalizeKeys(false)
                                                        ->ignoreExtraKeys(false)
                                                        ->beforeNormalization()
                                                            ->ifEmpty()->then(function ($v) { return []; })
                                                        ->end()
                                                        ->treatNullLike([])
                                                        ->treatFalseLike([])
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()

                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * get configuration dump
         * @param ArrayNodeDefinition $rootNode
         *
         * @return void
         */
        private function getConfigDump(ArrayNodeDefinition $rootNode): void {

            $rootNode
                ->children()
                    ->arrayNode('export')
                        ->info('Exportar Base de Datos / Dump database')
                        ->beforeNormalization()
                            ->ifEmpty()->then(function ($v) { return ['enable' => false, 'commands' => []]; })
                        ->end()
                        ->treatNullLike(['enable' => false, 'commands' => []])
                        ->children()
                            ->booleanNode('enable')->defaultFalse()->end()
                            ->arrayNode('commands')
                                ->beforeNormalization()
                                    ->ifEmpty()->then(function ($v) { return []; })
                                ->end()
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->booleanNode('enable')->defaultFalse()->end()
                                        ->booleanNode('lockable')->defaultTrue()->end()
                                        ->arrayNode('params')
                                            ->isRequired()
                                            ->children()
                                                ->enumNode('client')->isRequired()->values(['mysqldump'])->end()
                                                ->scalarNode('host')->isRequired()->cannotBeEmpty()->defaultNull()->end()
                                                ->integerNode('port')->defaultNull()->end()
                                                ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                                                ->scalarNode('database')->isRequired()->cannotBeEmpty()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Get field Env configuration
         *
         * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
         */
        private function getFieldEnvironments() {

            $treeBuilder = new TreeBuilder('env');
            $node = $treeBuilder->getRootNode();

            $node
                ->info('Entornos Disponibles para Ejecutar')
                ->beforeNormalization()
                    ->ifEmpty()->then(function ($v) { return []; })
                    ->ifString()->then(function ($v) { return empty($v) ? [] : [$v]; })
                ->end()
                ->prototype('scalar')->end()
                ->cannotBeEmpty()
                ->isRequired()
                ->requiresAtLeastOneElement();

            return $node;
        }
    }