<?php
/**
 * ProjectVersion.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;

class ProjectVersionMock extends \FMUP\ProjectVersion
{
    public function __construct()
    {

    }
}

class ProjectVersionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\ProjectVersion::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\ProjectVersion::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\ProjectVersion::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $version = \FMUP\ProjectVersion::getInstance();
        $this->assertInstanceOf(\FMUP\ProjectVersion::class, $version);
        $version2 = \FMUP\ProjectVersion::getInstance();
        $this->assertSame($version, $version2);
        return $version;
    }

    public function testGetWhenFileDontExists()
    {
        $projectVersion = $this->getMockBuilder('\FMUPTests\ProjectVersionMock')->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn('nonexistent_file');

        $reflection = new \ReflectionProperty('\FMUP\ProjectVersion', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("composer.json does not exist");
        /** @var $projectVersion \FMUP\ProjectVersion */
        $projectVersion->get();
    }

    public function testGetWhenStructureIsBad()
    {
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn(__FILE__);

        $reflection = new \ReflectionProperty(\FMUP\ProjectVersion::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('composer.json invalid structure');
        /** @var $projectVersion \FMUP\ProjectVersion */
        $projectVersion->get();
    }

    public function testComposerPath()
    {
        $reflection = new \ReflectionMethod('\FMUP\ProjectVersion', 'getComposerPath');
        $reflection->setAccessible(true);

        $projectVersion = $this->getMockBuilder('\FMUPTests\ProjectVersionMock')->setMethods(null)->getMock();
        $this->assertRegExp('~/../../../../composer.json$~', $reflection->invoke($projectVersion));
    }

    public function testGetAndName()
    {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . '.files' . DIRECTORY_SEPARATOR . 'composer.lock';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $version = uniqid();
        $data = <<<COMPOSER
{
    "_readme": [
        "This file locks the dependencies of your project to a known state",
        "Read more about it at https://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file",
        "This file is @generated automatically"
    ],
    "hash": "264569b5655b4ffbeb2d46d1ab24f62a",
    "content-hash": "a48e6cbbddd373417c84634c7eafb16c",
    "packages": [
    {
                "name": "gettext/gettext",
                "version": "v4.3.0",
                "source": {
                    "type": "git",
                    "url": "https://github.com/oscarotero/Gettext.git",
                    "reference": "9f8b05914581173725b256157e15ee14c42d3865"
                },
                "dist": {
                    "type": "zip",
                    "url": "https://api.github.com/repos/oscarotero/Gettext/zipball/9f8b05914581173725b256157e15ee14c42d3865",
                    "reference": "9f8b05914581173725b256157e15ee14c42d3865",
                    "shasum": ""
                },
                "require": {
                    "gettext/languages": "2.*",
                    "php": ">=5.4.0"
                },
                "require-dev": {
                    "illuminate/view": "*",
                    "symfony/yaml": "~2",
                    "twig/extensions": "*",
                    "twig/twig": "^1.31|^2.0"
                },
                "suggest": {
                    "illuminate/view": "Is necessary if you want to use the Blade extractor",
                    "symfony/yaml": "Is necessary if you want to use the Yaml extractor/generator",
                    "twig/extensions": "Is necessary if you want to use the Twig extractor",
                    "twig/twig": "Is necessary if you want to use the Twig extractor"
                },
                "type": "library",
                "autoload": {
                    "psr-4": {
                        "Gettext\\": "src"
                    }
                },
                "notification-url": "https://packagist.org/downloads/",
                "license": [
                    "MIT"
                ],
                "authors": [
                    {
                        "name": "Oscar Otero",
                        "email": "oom@oscarotero.com",
                        "homepage": "http://oscarotero.com",
                        "role": "Developer"
                    }
                ],
                "description": "PHP gettext manager",
                "homepage": "https://github.com/oscarotero/Gettext",
                "keywords": [
                    "JS",
                    "gettext",
                    "i18n",
                    "mo",
                    "po",
                    "translation"
                ],
                "time": "2017-03-04 16:39:13"
            },
        {
            "name": "fmup/fmup",
            "version": "$version",
            "source": {
                "type": "git",
                "url": "https://github.com/Logi-CE/fmup.git",
                "reference": "4e555e4081731eebb0b3d10096141ee16c94491a"
            },
            "dist": {
                "type": "zip",
                "url": "https://api.github.com/repos/Logi-CE/fmup/zipball/4e555e4081731eebb0b3d10096141ee16c94491a",
                "reference": "4e555e4081731eebb0b3d10096141ee16c94491a",
                "shasum": ""
            },
            "require": {
                "ext-json": "^1.2",
                "ext-mbstring": "0.0.0.*",
                "ext-pcre": "0.0.0.*",
                "ext-spl": "0.2.*",
                "monolog/monolog": "^1.13",
                "php": "^5.4",
                "php-amqplib/php-amqplib": "^2.5",
                "phpmailer/phpmailer": "^5.2"
            },
            "require-dev": {
                "codeclimate/php-test-reporter": "dev-master",
                "mayflower/php-codebrowser": "^1.1",
                "pdepend/pdepend": "^2.1",
                "phing/phing": "^2.14",
                "phpdocumentor/phpdocumentor": "^2.8",
                "phploc/phploc": "^2.0 || ^3.0",
                "phpmd/phpmd": "^2.2",
                "phpunit/phpunit": "^4.0",
                "sebastian/phpcpd": "^2.0",
                "squizlabs/php_codesniffer": "^2.3"
            },
            "suggest": {
                "ext-memcached": "Memcached >= 2.0.0 to use the Memcached Cache adapter",
                "ext-pdo_mysql": "To use Mysql connections",
                "ext-pdo_sqlite": "To use Sqlite connections",
                "ext-sqlite3": "To use Sqlite connections",
                "ext-ssh2": "To use Ftp system",
                "ext-sysvmsg": "To use Queue system"
            },
            "type": "library",
            "autoload": {
                "psr-4": {
                    "FMUP\\": "lib"
                },
                "files": [
                    "system/autoload.php"
                ]
            },
            "notification-url": "https://packagist.org/downloads/",
            "time": "2017-02-20 13:11:32"
        },
        {
            "name": "gettext/languages",
            "version": "2.2.0",
            "source": {
                "type": "git",
                "url": "https://github.com/mlocati/cldr-to-gettext-plural-rules.git",
                "reference": "bd19ab830291d9b74b23d21428233e06389ef7c2"
            },
            "dist": {
                "type": "zip",
                "url": "https://api.github.com/repos/mlocati/cldr-to-gettext-plural-rules/zipball/bd19ab830291d9b74b23d21428233e06389ef7c2",
                "reference": "bd19ab830291d9b74b23d21428233e06389ef7c2",
                "shasum": ""
            },
            "require": {
                "php": ">=5.3"
            },
            "bin": [
                "bin/export-plural-rules",
                "bin/export-plural-rules.php"
            ],
            "type": "library",
            "autoload": {
                "psr-4": {
                    "Gettext\\Languages\\": "src/"
                }
            },
            "notification-url": "https://packagist.org/downloads/",
            "license": [
                "MIT"
            ],
            "authors": [
                {
                    "name": "Michele Locati",
                    "email": "mlocati@gmail.com",
                    "role": "Developer"
                }
            ],
            "description": "gettext languages with plural rules",
            "homepage": "https://github.com/mlocati/cldr-to-gettext-plural-rules",
            "keywords": [
                "cldr",
                "i18n",
                "internationalization",
                "l10n",
                "language",
                "languages",
                "localization",
                "php",
                "plural",
                "plural rules",
                "plurals",
                "translate",
                "translations",
                "unicode"
            ],
            "time": "2017-02-06 14:30:42"
        }
    ]
}
COMPOSER;
        file_put_contents($filePath, $data);
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn($filePath);

        $reflection = new \ReflectionProperty(\FMUP\ProjectVersion::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('ProjectTest' . $version, $projectVersion->name());
        $this->assertSame($version, $projectVersion->get());
        unlink($filePath);
    }
}
