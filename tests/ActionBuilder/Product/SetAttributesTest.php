<?php

declare(strict_types=1);

namespace BestIt\CommercetoolsODM\Tests\ActionBuilder\Product;

use BestIt\CommercetoolsODM\ActionBuilder\Product\SetAttributes;
use BestIt\CommercetoolsODM\Mapping\ClassMetadataInterface;
use BestIt\CommercetoolsODM\Tests\ActionBuilder\SupportTestTrait;
use Commercetools\Core\Model\Product\Product;
use Commercetools\Core\Request\Products\Command\ProductSetAttributeAction;
use PHPUnit\Framework\TestCase;

/**
 * Tests the SetAttributes.
 * @author blange <lange@bestit-online.de>
 * @package BestIt\CommercetoolsODM\Tests\ActionBuilder\Product
 */
class SetAttributesTest extends TestCase
{
    use SupportTestTrait;

    /**
     * The tested class.
     * @var SetAttributes|null
     */
    protected $fixture;

    /**
     * Returns assertions for the create call.
     * @return array
     */
    public function getCatalogs(): array
    {
        return [
            ['current'],
            ['staged', true]
        ];
    }

    /**
     * Returns an array with the assertions for the upport method.
     *
     * The First Element is the field path, the second element is the reference class and the optional third value
     * indicates the return value of the support method.
     * @return array
     */
    public function getSupportAssertions(): array
    {
        return [
            ['masterData/current/masterVariant/attributes', Product::class, true],
            ['masterData/current/variants/1/attributes', Product::class, true],
            ['masterData/current/variants/10/attributes', Product::class, true],
            ['masterData/current/variants/f/attributes', Product::class],
            ['masterData/staged/masterVariant/attributes', Product::class, true],
            ['masterData/staged/variants/1/attributes', Product::class, true],
            ['masterData/staged/variants/10/attributes', Product::class, true],
            ['masterData/staged/variants/f/attributes', Product::class],
            ['masterData/current/variants/1/sku', Product::class],
            ['masterData/current/variants/100/sku', Product::class],
            ['masterData/staged/masterVariant/sku', Product::class],
            ['masterData/staged/variants/1/sku', Product::class],
            ['masterData/staged/variants/100/sku', Product::class],
        ];
    }

    /**
     * Sets up the test.
     * @reteurn void
     */
    protected function setUp()
    {
        $this->fixture = new SetAttributes();
    }

    /**
     * Checks if the master variant attributes can be changed.
     * @dataProvider getCatalogs
     * @param string $container
     * @param bool $staged
     */
    public function testCreateUpdateActionsForMasterVariant(string $container, bool $staged = false)
    {
        $this->fixture->supports("masterData/{$container}/masterVariant/attributes", Product::class);

        $actions = $this->fixture->createUpdateActions(
            [
                [
                    'value' => $mockedValue = uniqid()
                ]
            ],
            $this->createMock(ClassMetadataInterface::class),
            [],
            [
                'masterData' => [
                    $container => [
                        'masterVariant' => [
                            'attributes' => [
                                [
                                    'name' => $attrName = 'manufacturer',
                                    'value' => uniqid()
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            new Product()
        );

        static::assertCount(1, $actions, 'Wrong action count.');

        /** @var $action ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action = $actions[0],
            'Wrong instance.'
        );

        static::assertSame(1, $action->getVariantId(), 'Wrong variant id.');
        static::assertSame($attrName, $action->getName(), 'Wrong name.');
        static::assertSame($mockedValue, $action->getValue(), 'Wrong value');
        static::assertSame($staged, $action->getStaged(), 'Staged wrongly set.');
    }

    /**
     * Checks an attribute can be added to the master variant attributes.
     * @dataProvider getCatalogs
     * @param string $container
     * @param bool $staged
     */
    public function testCreateUpdateActionsForMasterVariantAddAttr(string $container, bool $staged = false)
    {
        $this->fixture->supports("masterData/{$container}/masterVariant/attributes", Product::class);

        $actions = $this->fixture->createUpdateActions(
            [
                [
                    'value' => $mockedValue1 = uniqid()
                ],
                [
                    'name' => $attrName2 = uniqid(),
                    'value' => $mockedValue2 = uniqid()
                ]
            ],
            $this->createMock(ClassMetadataInterface::class),
            [],
            [
                'masterData' => [
                    $container => [
                        'masterVariant' => [
                            'attributes' => [
                                [
                                    'name' => $attrName1 = 'manufacturer',
                                    'value' => uniqid()
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            new Product()
        );

        static::assertCount(2, $actions, 'Wrong action count.');

        /** @var $action ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action = $actions[0],
            'Wrong instance.'
        );

        static::assertSame(1, $action->getVariantId(), 'Wrong variant id.');
        static::assertSame($attrName1, $action->getName(), 'Wrong name.');
        static::assertSame($mockedValue1, $action->getValue(), 'Wrong value');
        static::assertSame($staged, $action->getStaged(), 'Staged wrongly set.');

        /** @var $action ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action = $actions[1],
            'Wrong instance.'
        );

        static::assertSame(1, $action->getVariantId(), 'Wrong variant id.');
        static::assertSame($attrName2, $action->getName(), 'Wrong name.');
        static::assertSame($mockedValue2, $action->getValue(), 'Wrong value');
        static::assertSame($staged, $action->getStaged(), 'Staged wrongly set.');
    }

    /**
     * Checks if a nested master variant attribute and its difference to arrays can be changed
     * @dataProvider getCatalogs
     * @param string $container
     * @param bool $staged
     */
    public function testCreateUpdateActionsForMasterVariantNestedAttr(string $container, bool $staged = false)
    {
        $this->fixture->supports("masterData/{$container}/masterVariant/attributes", Product::class);

        $actions = $this->fixture->createUpdateActions(
            [
                [
                    'value' => []
                ],
                [
                    'value' => [2]
                ],
                [
                    'value' => [
                        1 => [
                            'value' => 'new-value2'
                        ],
                        // We add this value.
                        [
                            'name' => 'new-name3',
                            'value' => 'new-value3'
                        ],
                    ]
                ]
            ],
            $this->createMock(ClassMetadataInterface::class),
            [],
            [
                'masterData' => [
                    $container => [
                        'masterVariant' => [
                            'attributes' => [
                                [
                                    'name' => 'array1',
                                    'value' => [uniqid(), uniqid()]
                                ],
                                [
                                    'name' => 'array2',
                                    'value' => [1,2]
                                ],
                                [
                                    'name' => $attrName = 'nested',
                                    'value' => [
                                        // attr 1 needs to be overtaken, because it is a nested attr.
                                        [
                                            'name' => 'name1',
                                            'value' => 'value1'
                                        ],
                                        // We overwrite only the value here
                                        [
                                            'name' => 'name2',
                                            'value' => uniqid()
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            new Product()
        );

        static::assertCount(3, $actions, 'Wrong action count.');

        /** @var $action1 ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action1 = $actions[0],
            'Wrong instance. (1)'
        );

        static::assertSame(1, $action1->getVariantId(), 'Wrong variant id. (1)');
        static::assertSame('array1', $action1->getName(), 'Wrong name. (1)');
        static::assertSame($staged, $action1->getStaged(), 'Staged wrongly set. (1)');

        static::assertSame([], $action1->getValue(), 'Wrong value. (1)');

        /** @var $action1 ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action1 = $actions[1],
            'Wrong instance. (2)'
        );

        static::assertSame(1, $action1->getVariantId(), 'Wrong variant id. (2)');
        static::assertSame('array2', $action1->getName(), 'Wrong name. (2)');
        static::assertSame($staged, $action1->getStaged(), 'Staged wrongly set. (2)');

        static::assertSame([2], $action1->getValue(), 'Wrong value. (2)');

        /** @var $action3 ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action3 = $actions[2],
            'Wrong instance. (3)'
        );

        static::assertSame(1, $action3->getVariantId(), 'Wrong variant id. (3)');
        static::assertSame($attrName, $action3->getName(), 'Wrong name. (3)');
        static::assertSame($staged, $action3->getStaged(), 'Staged wrongly set. (3)');

        static::assertSame(
            [
                ['name' => 'name1', 'value' => 'value1'],
                ['value' => 'new-value2', 'name' => 'name2'],
                ['name' => 'new-name3', 'value' => 'new-value3']
            ],
            $action3->getValue(),
            'Wrong value. (3)'
        );
    }

    /**
     * Checks if variant attributes can be changed.
     * @dataProvider getCatalogs
     * @param string $container
     * @param bool $staged
     */
    public function testCreateUpdateActionsForVariantAttributes(string $container, bool $staged = false)
    {
        $this->fixture->supports(
            sprintf('masterData/%s/variants/%s/attributes', $container, $variantId = mt_rand(0, 1000)),
            Product::class
        );

        $actions = $this->fixture->createUpdateActions(
            [
                [
                    'value' => $mockedValue1 = uniqid()
                ],
                [
                    null
                ]
            ],
            $this->createMock(ClassMetadataInterface::class),
            [],
            [
                'masterData' => [
                    $container => [
                        'variants' => [
                            $variantId => [
                                'id' => $variantId,
                                'attributes' => [
                                    [
                                        'name' => $attrName1 = 'manufacturer',
                                        'value' => uniqid()
                                    ],
                                    [
                                        'name' => $attrName2 = 'manufacturer',
                                        'value' => uniqid()
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            new Product()
        );

        static::assertCount(2, $actions, 'Wrong action count.');

        /** @var $action ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action = $actions[0],
            'Wrong instance.'
        );

        static::assertSame($variantId, $action->getVariantId(), 'Wrong variant id.');
        static::assertSame($attrName1, $action->getName(), 'Wrong name.');
        static::assertSame($mockedValue1, $action->getValue(), 'Wrong value');
        static::assertSame($staged, $action->getStaged(), 'Staged wrongly set.');

        /** @var $action ProductSetAttributeAction */
        static::assertInstanceOf(
            ProductSetAttributeAction::class,
            $action = $actions[1],
            'Wrong instance.'
        );

        static::assertSame($variantId, $action->getVariantId(), 'Wrong variant id.');
        static::assertSame($attrName2, $action->getName(), 'Wrong name.');
        static::assertSame(null, $action->getValue(), 'Wrong value');
        static::assertSame($staged, $action->getStaged(), 'Staged wrongly set.');
    }
}
