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

            $this->addConfigurationInstall($rootNode);
            $this->addConfigurationPaths($rootNode);
            $this->addConfigurationSymlink($rootNode);
            $this->addConfigurationMirrors($rootNode);
            $this->addConfigurationDumpConnection($rootNode);
            $this->addConfigurationSQLImport($rootNode);
            $this->addConfigurationCommands($rootNode);

            return $treeBuilder;
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationDumpConnection(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('dump')
                        ->children()
                            ->booleanNode('enable')->defaultTrue()->end()
                            ->arrayNode('connections')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->enumNode('client')->values(['mysqldump'])->end()
                                        ->scalarNode('host')->isRequired()->cannotBeEmpty()->defaultNull()->end()
                                        ->integerNode('port')->defaultValue('3306')->end()
                                        ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('database')->isRequired()->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationPaths(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('paths')
                        ->children()
                            ->scalarNode('local')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('dump')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('sql')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationSymlink(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('symlinks')
                        ->children()
                            ->booleanNode('enable')->isRequired()->defaultFalse()->end()
                            ->arrayNode('configurations')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('environment')
                                            ->info('Entornos de ejecución del registro.')
                                            ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                        ->scalarNode('origin')
                                            ->isRequired()
                                            ->info('Directorio o archivo de origen.')
                                            ->beforeNormalization()->ifString()->then(function ($v) { return realpath($v); })->end()
                                        ->end()
                                        ->scalarNode('destination')
                                            ->isRequired()
                                            ->info('Directorio o archivo de destino.')
                                            ->beforeNormalization()->ifString()->then(function ($v) { return '%kernel.project_dir%/'.ltrim($v, DIRECTORY_SEPARATOR); })->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationMirrors(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('mirrors')
                        ->children()
                            ->booleanNode('enable')->isRequired()->defaultFalse()->end()
                            ->arrayNode('configurations')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('environment')
                                            ->info('Entornos de ejecución del registro.')
                                            ->isRequired()
                                            ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                        ->scalarNode('origin')
                                            ->isRequired()
                                            ->info('Directorio o archivo de origen.')
                                            ->beforeNormalization()->ifString()->then(function ($v) { return realpath($v); })->end()
                                        ->end()
                                        ->scalarNode('destination')
                                            ->isRequired()
                                            ->info('Directorio o archivo de destino.')
                                            ->beforeNormalization()->ifString()->then(function ($v) { return '%kernel.project_dir%/'.ltrim($v, DIRECTORY_SEPARATOR); })->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationSQLImport(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('sql_import')
                        ->children()
                            ->booleanNode('enable')->isRequired()->defaultFalse()->end()
                            ->arrayNode('configurations')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('environment')
                                            ->info('Entornos de ejecución del registro.')
                                            ->isRequired()
                                            ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                        ->arrayNode('files')
                                            ->info('Archivos SQL a ejecutar en la base de datos.')
                                            ->isRequired()
                                            ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationCommands(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('commands')
                        ->children()
                            ->booleanNode('enable')->isRequired()->defaultFalse()->end()
                            ->arrayNode('configurations')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('environment')
                                            ->info('Entornos de ejecución del registro.')
                                            ->isRequired()
                                            ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                        ->scalarNode('command')->isRequired()->cannotBeEmpty()->end()
                                        ->arrayNode('arguments')
                                            ->info('Argumentos del comando.')
                                            ->normalizeKeys(false)
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * @param ArrayNodeDefinition $rootNode
         */
        private function addConfigurationInstall(ArrayNodeDefinition $rootNode): void {

            $rootNode->children()
                    ->arrayNode('install')
                        ->children()
                            ->booleanNode('enable')->isRequired()->defaultFalse()->end()
                            ->arrayNode('configurations')
                                ->prototype('array')
                                    ->ignoreExtraKeys(false)
                                    ->beforeNormalization()
                                        ->castToArray()
                                    ->end()
                                    ->validate()
                                        ->ifTrue(function ($a) {
                                            if(array_key_exists('type', $a)):
                                                if ($a['type'] === 'symfony_command'):
                                                    $this->validateKeysType('symfony_command', $a);
                                                endif;
                                            endif;
                                            return false;
                                        })
                                        ->thenInvalid('Se ha presentado un error %s')
                                    ->end()
                                    ->children()
                                        ->arrayNode('environment')
                                            ->info('Entornos de ejecución del registro.')
                                            ->isRequired()
                                            ->beforeNormalization()->ifString()->then(function ($v) { return [$v]; })->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue([])
                                        ->end()
                                        ->enumNode('type')->values(['symfony_command'])->isRequired()->end()
                                        ->booleanNode('enable')->defaultTrue()->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        }

        /**
         * Validate key of types node
         *
         * @param string $type
         * @param array $array
         *
         * @return void
         */
        private function validateKeysType(string $type, array $array = []): void {

            $compare = [
                'symfony_command' => ['type' => true, 'enable' => true, 'command' => true, 'arguments' => true]
            ];
            $result = array_diff_key($compare[$type], $array);

            if (count($result) > 0):
                throw new \RuntimeException('No se configuraron las propiedades: ['.implode(', ', array_keys($result)).']');
            endif;
        }
    }