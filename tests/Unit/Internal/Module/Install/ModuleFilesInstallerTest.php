<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Module\Install;

use org\bovigo\vfs\vfsStream;
use OxidEsales\EshopCommunity\Internal\Common\CopyGlob\CopyGlobServiceInterface;
use OxidEsales\EshopCommunity\Internal\Common\Exception\DirectoryExistentException;
use OxidEsales\EshopCommunity\Internal\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Module\Install\Service\ModuleFilesInstaller;
use OxidEsales\EshopCommunity\Internal\Application\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Internal\Module\Install\Dao\OxidEshopPackageDaoInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
class ModuleFilesInstallerTest extends TestCase
{
    public function testCopyDispatchesCallsToTheCopyGlobService()
    {
        $packageName = 'myvendor/mymodule';
        $packagePath = '/var/www/vendor/myvendor/mymodule';
        $extra = [
            "oxideshop" => [
                "blacklist-filter" => [
                    "documentation/**/*.*",
                    "CHANGELOG.md",
                    "composer.json",
                    "CONTRIBUTING.md",
                    "README.md"
                ],
                "target-directory" => "myvendor/myinstallationpath",
                "source-directory" => "src/mymodule"
            ]
        ];

        vfsStream::setup();
        $context = $this->getContext();
        $oxidEshopPackageDao = $this->getOxidEshopPackageDao($packageName, $extra);

        $finder = new Finder();

        $fileSystem = $this->getMockBuilder(Filesystem::class)->getMock();
        $fileSystem
            ->expects($this->once())
            ->method('mirror')
            ->with(
                $packagePath . DIRECTORY_SEPARATOR . $extra['oxideshop']['source-directory'],
                $context->getModulesPath() . DIRECTORY_SEPARATOR . $extra['oxideshop']['target-directory'],
                $finder,
                ['override' => true]
            );

        $moduleFilesInstaller = new ModuleFilesInstaller($oxidEshopPackageDao, $context, $fileSystem);
        $moduleFilesInstaller->copy($packagePath);
    }

    public function testCopyThrowsExceptionIfTargetDirectoryAlreadyPresent()
    {
        $structure = [
            'source' => [
                'modules' => [
                    'myvendor' => [
                        'mymodule' => [
                            'metadata.php' => ''
                        ]
                    ]
                ]
            ]
        ];
        vfsStream::setup('root', null, $structure);

        $packageName = 'myvendor/mymodule';

        $context = $this->getContext();
        $oxidEshopPackageDao = $this->getOxidEshopPackageDao($packageName, []);
        $copyGlobService = $this->getCopyGlobService();

        $this->expectException(\OxidEsales\EshopCommunity\Internal\Common\Exception\DirectoryExistentException::class);

        $moduleFilesInstaller = new ModuleFilesInstaller($oxidEshopPackageDao, $context, $copyGlobService);

        $this->expectException(\OxidEsales\EshopCommunity\Internal\Common\Exception\DirectoryExistentException::class);
        try {
            $moduleFilesInstaller->copy('pathDoesNotMatterHere');
            $this->fail('Exception should be thrown when target directory is already present');
        } catch (DirectoryExistentException $exception) {
            $directory = $exception->getDirectoryAlreadyExistent();
            $this->assertEquals(vfsStream::url('root/source/modules/myvendor/mymodule'), $directory);
            throw $exception;
        }
    }

    /**
     * @param string $packageName
     * @param array  $extraParameters
     *
     * @return OxidEshopPackageDaoInterface|MockObject
     */
    private function getOxidEshopPackageDao(string $packageName, array $extraParameters = [])
    {
        $package = new OxidEshopPackage($packageName, $extraParameters);

        $oxidEshopPackageDao = $this->getMockBuilder(OxidEshopPackageDaoInterface::class)->getMock();
        $oxidEshopPackageDao->method('getPackage')->willReturn($package);

        return $oxidEshopPackageDao;
    }

    /**
     * @return BasicContextInterface|MockObject
     */
    private function getContext()
    {
        $context = $this->getMockBuilder(BasicContextInterface::class)->getMock();
        $context->method('getModulesPath')->willReturn(vfsStream::url('root/source/modules'));
        return $context;
    }


    /**
     * @return BasicContextInterface|MockObject
     */
    private function getCopyGlobService()
    {
        $copyGlobServiceInterface = $this->getMockBuilder(CopyGlobServiceInterface::class)->getMock();
        return $copyGlobServiceInterface;
    }
}
