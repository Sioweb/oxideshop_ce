<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Module\Configuration\Service;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\Chain;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Service\ModuleConfigurationMergingService;
use PHPUnit\Framework\TestCase;

class ModuleConfigurationMergingServiceTest extends TestCase
{
    public function testMergeNewModuleConfiguration()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration->setId('newModule');

        $moduleConfigurationMergingService = new ModuleConfigurationMergingService();
        $shopConfiguration = $moduleConfigurationMergingService->merge(new ShopConfiguration(), $moduleConfiguration);

        $this->assertSame(
            $moduleConfiguration,
            $shopConfiguration->getModuleConfiguration('newModule')
        );
    }

    public function testExtensionClassAppendToChainAfterMergingNewModuleConfiguration()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration->setId('newModule');
        $moduleConfiguration->addSetting(new ModuleSetting(
            ModuleSetting::CLASS_EXTENSIONS,
            [
                'shopClass' => 'testModuleClassExtendsShopClass',
            ]
        ));

        $shopConfigurationWithChain = new ShopConfiguration();
        $chain = new Chain();
        $chain
            ->setName(Chain::CLASS_EXTENSIONS)
            ->setChain([
                'shopClass'             => ['alreadyInstalledShopClass', 'anotherAlreadyInstalledShopClass'],
                'someAnotherShopClass'  => ['alreadyInstalledShopClass'],
            ]);

        $shopConfigurationWithChain->addChain($chain);

        $moduleConfigurationMergingService = new ModuleConfigurationMergingService();
        $shopConfiguration = $moduleConfigurationMergingService->merge($shopConfigurationWithChain, $moduleConfiguration);

        $this->assertSame(
            [
                'shopClass'             => [
                    'alreadyInstalledShopClass',
                    'anotherAlreadyInstalledShopClass',
                    'testModuleClassExtendsShopClass',
                ],
                'someAnotherShopClass'  => ['alreadyInstalledShopClass'],
            ],
            $shopConfiguration->getChain(Chain::CLASS_EXTENSIONS)->getChain()
        );
    }

    public function testMergeModuleConfigurationOfAlreadyInstalledModule()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration->setId('installedModule');

        $moduleConfigurationMergingService = new ModuleConfigurationMergingService();
        $shopConfiguration = $moduleConfigurationMergingService->merge(
            $this->getShopConfigurationWithAlreadyInstalledModule(),
            $moduleConfiguration
        );

        $this->assertSame(
            $moduleConfiguration,
            $shopConfiguration->getModuleConfiguration('installedModule')
        );
    }

    public function testExtensionClassChainUpdatedAfterMergingAlreadyInstalledModule()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration->setId('installedModule');
        $moduleConfiguration->addSetting(new ModuleSetting(
            ModuleSetting::CLASS_EXTENSIONS,
            [
                'shopClass'         => 'extensionToStayInNewModuleConfiguration',
                'anotherShopClass'  => 'newExtension',
            ]
        ));

        $moduleConfigurationMergingService = new ModuleConfigurationMergingService();
        $shopConfiguration = $moduleConfigurationMergingService->merge(
            $this->getShopConfigurationWithAlreadyInstalledModule(),
            $moduleConfiguration
        );

        $this->assertEquals(
            [
                'shopClass'         => ['someOtherExtension', 'extensionToStayInNewModuleConfiguration'],
                'anotherShopClass'  => ['someOtherExtension', 'newExtension']
            ],
            $shopConfiguration->getChain(Chain::CLASS_EXTENSIONS)->getChain()
        );
    }

    public function testShopModuleSettingUpdatedAfterMergingAlreadyInstalledModule()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration->setId('installedModule');
        $moduleConfiguration->addSetting(new ModuleSetting(
            ModuleSetting::SHOP_MODULE_SETTING,
            [
                [
                    'name'          => 'groupToChange',
                    'group'         => 'newGroup',
                    'type'          => 'int',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                ],
                [
                    'name'          => 'withTypeToChange',
                    'type'          => 'bool',
                    'position'      => '100500',
                ],
                [
                    'name'          => 'withConstraintsToChange',
                    'type'          => 'str',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                ],
                [
                    'name'          => 'staysSame',
                    'type'          => 'int',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                ],
                [
                    'name'          => 'completeNewOne',
                    'type'          => 'string',
                    'position'      => '100500',
                ],
            ]
        ));

        $moduleConfigurationMergingService = new ModuleConfigurationMergingService();
        $shopConfiguration = $moduleConfigurationMergingService->merge(
            $this->getShopConfigurationWithAlreadyInstalledModule(),
            $moduleConfiguration
        );

        $mergedModuleConfiguration = $shopConfiguration->getModuleConfiguration('installedModule');

        $this->assertEquals(
            [
                [
                    'name'          => 'groupToChange',
                    'group'         => 'newGroup',
                    'type'          => 'int',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                    'value'         => 'staysSame',
                ],
                [
                    'name'          => 'withTypeToChange',
                    'type'          => 'bool',
                    'position'      => '100500',
                ],
                [
                    'name'          => 'withConstraintsToChange',
                    'type'          => 'str',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                ],
                [
                    'name'          => 'staysSame',
                    'type'          => 'int',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                    'value'         => '3',
                ],
                [
                    'name'          => 'completeNewOne',
                    'type'          => 'string',
                    'position'      => '100500',
                ],
            ],
            $mergedModuleConfiguration->getSetting(ModuleSetting::SHOP_MODULE_SETTING)->getValue()
        );
    }

    private function getShopConfigurationWithAlreadyInstalledModule(): ShopConfiguration
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration->setId('installedModule');
        $moduleConfiguration->addSetting(new ModuleSetting(
            ModuleSetting::CLASS_EXTENSIONS,
            [
                'shopClass'         => 'extensionToStayInNewModuleConfiguration',
                'anotherShopClass'  => 'extensionToBeDeletedInNewModuleConfiguration',
            ]
        ));

        $moduleConfiguration->addSetting(new ModuleSetting(
            ModuleSetting::SHOP_MODULE_SETTING,
            [
                [
                    'name'          => 'groupToChange',
                    'group'         => 'oldGroup',
                    'type'          => 'int',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                    'value'         => 'staysSame',
                ],
                [
                    'name'          => 'withTypeToChange',
                    'type'          => 'str',
                    'position'      => '100500',
                    'value'         => 'toDelete',
                ],
                [
                    'name'          => 'withConstraintsToChange',
                    'type'          => 'str',
                    'constraints'   => ['1', 'toDelete'],
                    'position'      => '100500',
                    'value'         => 'toDelete',
                ],
                [
                    'name'          => 'staysSame',
                    'type'          => 'int',
                    'constraints'   => ['1', '2', '3'],
                    'position'      => '100500',
                    'value'         => '3',
                ],
            ]
        ));

        $chain = new Chain();
        $chain
            ->setName(Chain::CLASS_EXTENSIONS)
            ->setChain([
                'shopClass'         => ['someOtherExtension', 'extensionToStayInNewModuleConfiguration'],
                'anotherShopClass'  => ['someOtherExtension', 'extensionToBeDeletedInNewModuleConfiguration']
            ]);

        $shopConfiguration = new ShopConfiguration();
        $shopConfiguration->addModuleConfiguration($moduleConfiguration);
        $shopConfiguration->addChain($chain);

        return $shopConfiguration;
    }
}
