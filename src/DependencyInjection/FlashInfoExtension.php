<?php

namespace Pixel\FlashInfoBundle\DependencyInjection;

use Pixel\FlashInfoBundle\Admin\FlashInfoAdmin;
use Pixel\FlashInfoBundle\Entity\FlashInfo;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

class FlashInfoExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'resources' => [
                        'flash_infos' => [
                            'routes' => [
                                'detail' => 'flashinfo.get_flash-info',
                                'list' => 'flashinfo.get_flash-infos',
                            ],
                        ],
                        'flash_infos_settings' => [
                            'routes' => [
                                'detail' => "flashinfo.get_flash-info-settings",
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'flash-info_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => FlashInfo::RESOURCE_KEY,
                                'view' => [
                                    'name' => FlashInfoAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => FlashInfo::LIST_KEY,
                                        'display_properties' => ['title'],
                                        'icon' => 'fa-bell',
                                        'label' => 'flash_infos',
                                        'overlay_title' => 'flash_info.list',
                                    ],
                                ],
                            ],
                        ],
                        'single_selection' => [
                            'single_flash-info_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => FlashInfo::RESOURCE_KEY,
                                'view' => [
                                    'name' => FlashInfoAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => FlashInfo::LIST_KEY,
                                        'display_properties' => ['title'],
                                        'icon' => 'fa-bell',
                                        'empty_text' => 'flash_info.empty',
                                        'overlay_title' => 'flash_info.list',
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => ['title'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            );
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loaderYaml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load("services.xml");
        $loaderYaml->load("services.yaml");
    }
}
