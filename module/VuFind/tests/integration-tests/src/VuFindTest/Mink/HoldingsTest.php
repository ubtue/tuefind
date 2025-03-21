<?php

/**
 * Test class for holdings and item statuses.
 *
 * PHP version 8
 *
 * Copyright (C) The National Library of Finland 2023.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Tests
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */

namespace VuFindTest\Mink;

use Behat\Mink\Element\DocumentElement;
use VuFind\ILS\Logic\AvailabilityStatusInterface;

/**
 * Test class for holdings and item statuses.
 *
 * @category VuFind
 * @package  Tests
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class HoldingsTest extends \VuFindTest\Integration\MinkTestCase
{
    use \VuFindTest\Feature\DemoDriverTestTrait;

    /**
     * Data provider for test methods
     *
     * @return array[]
     */
    public static function itemStatusAndHoldingsProvider(): array
    {
        $set = [
            [true, 'On Shelf', 'On Shelf', 'success'],
            [false, 'Checked Out', 'Checked Out', 'danger'],
            [AvailabilityStatusInterface::STATUS_AVAILABLE, 'On Shelf', 'On Shelf', 'success'],
            [AvailabilityStatusInterface::STATUS_UNAVAILABLE, 'Checked Out', 'Checked Out', 'danger'],
            [AvailabilityStatusInterface::STATUS_UNCERTAIN, 'Check with Staff', 'Check with Staff', 'warning'],
            [null, 'Live Status Unavailable', 'Live Status Unavailable', 'muted'],
        ];
        $msgSet = array_map(
            function ($a) {
                $a[] = 'msg';
                return $a;
            },
            $set
        );
        $groupSet = array_map(
            function ($a) {
                $a[] = 'group';
                return $a;
            },
            $set
        );
        $allSet = array_map(
            function ($a) {
                $a[] = 'all';
                return $a;
            },
            $set
        );

        return [...$msgSet, ...$groupSet, ...$allSet];
    }

    /**
     * Supplemental data provider for testItemStatusFull().
     *
     * @return array[]
     */
    public static function itemStatusAndHoldingsCustomTemplateProvider(): array
    {
        return ['custom template test' => [true, 'On Shelf', 'On Shelf', 'success', 'msg', true]];
    }

    /**
     * Test basic item status display in search results
     *
     * @param mixed  $availability      Item availability status
     * @param string $status            Status display string
     * @param string $expected          Expected availability display status
     * @param string $expectedType      Expected status type (e.g. 'success')
     * @param string $multipleLocations Configuration setting for multiple locations
     *
     * @dataProvider itemStatusAndHoldingsProvider
     *
     * @return void
     */
    public function testItemStatus(
        $availability,
        string $status,
        string $expected,
        string $expectedType,
        string $multipleLocations
    ): void {
        $this->changeConfigs(
            [
                'config' => $this->getConfigIniOverrides(false, $multipleLocations),
                'Demo' => $this->getDemoIniOverrides($availability, $status, true),
            ]
        );

        $page = $this->goToSearchResults();

        // The simple availability display will only show Available/Unavailable/Uncertain:
        $expectedMap = [
            // 'loan' service is displayed when availability is AvailabilityStatusInterface::STATUS_AVAILABLE
            // for non-grouped items:
            'success' => AvailabilityStatusInterface::STATUS_AVAILABLE === $availability
                && 'group' !== $multipleLocations
                ? 'Available for Loan' : 'Available',
            'danger' => 'Checked Out',
            'warning' => 'Uncertain',
            'default' => 'Live Status Unavailable',
        ];
        // 'default' is used instead of 'muted' in labels:
        if ('muted' === $expectedType) {
            $expectedType = 'default';
        }
        $this->assertEquals(
            $expectedMap[$expectedType],
            $this->findCssAndGetText($page, ".result-body .status .label.label-$expectedType")
        );
        if ($availability) {
            // Extra items, check for different display styles:
            if ('group' === $multipleLocations) {
                if (AvailabilityStatusInterface::STATUS_AVAILABLE === $availability) {
                    // For this case we have available items in both locations:
                    $this->assertEquals(
                        'Test Location',
                        $this->findCssAndGetText($page, '.result-body .callnumAndLocation .groupLocation .text-success')
                    );
                    $this->assertEquals(
                        'Main Library',
                        $this->findCssAndGetText(
                            $page,
                            '.result-body .callnumAndLocation .groupLocation .text-success',
                            null,
                            1
                        )
                    );
                } else {
                    $this->assertEquals(
                        'Test Location',
                        $this->findCssAndGetText($page, '.result-body .callnumAndLocation .groupLocation .text-danger')
                    );
                    $this->assertEquals(
                        'Main Library',
                        $this->findCssAndGetText($page, '.result-body .callnumAndLocation .groupLocation .text-success')
                    );
                }
            } else {
                $this->assertEquals(
                    'msg' === $multipleLocations ? 'Multiple Locations' : 'Test Location, Main Library',
                    $this->findCssAndGetText($page, '.result-body .callnumAndLocation .location')
                );
            }
        } else {
            // No extra items to care for:
            if ('group' === $multipleLocations) {
                // Unknown status displays as warning:
                $type = null === $availability ? 'warning' : 'danger';
                $selector = ".result-body .callnumAndLocation .groupLocation .text-$type";
            } else {
                $selector = '.result-body .callnumAndLocation .location';
            }
            $this->assertEquals('Main Library', $this->findCssAndGetText($page, $selector));
        }
    }

    /**
     * Test full item status display in search results
     *
     * @param mixed  $availability      Item availability status
     * @param string $status            Status display string
     * @param string $expected          Expected availability display status
     * @param string $expectedType      Expected status type (e.g. 'success')
     * @param string $multipleLocations Configuration setting for multiple locations
     * @param string $customTemplate    Include extra steps to test custom template?
     *
     * @dataProvider itemStatusAndHoldingsProvider
     * @dataProvider itemStatusAndHoldingsCustomTemplateProvider
     *
     * @return void
     */
    public function testItemStatusFull(
        $availability,
        string $status,
        string $expected,
        string $expectedType,
        string $multipleLocations,
        bool $customTemplate = false
    ): void {
        $config = $this->getConfigIniOverrides(true, $multipleLocations);
        // If testing with the custom template, switch to the minktest theme:
        if ($customTemplate) {
            $config['Site']['theme'] = 'minktest';
        }
        $this->changeConfigs(
            [
                'config' => $config,
                'Demo' => $this->getDemoIniOverrides($availability, $status, true),
            ]
        );

        $page = $this->goToSearchResults();

        $this->assertEquals(
            $expected,
            $this->findCssAndGetText($page, ".result-body .fullAvailability .text-$expectedType")
        );

        if ($availability) {
            // Extra items, check both:
            $this->assertEquals('Test Location', $this->findCssAndGetText($page, '.result-body .fullLocation'));
            $this->assertEquals('Main Library', $this->findCssAndGetText($page, '.result-body .fullLocation', null, 1));
        } else {
            // No extra items to care for:
            $this->assertEquals('Main Library', $this->findCssAndGetText($page, '.result-body .fullLocation'));
        }
        // If testing with the custom template, be sure its custom script executed as expected:
        if ($customTemplate) {
            $this->findCss($page, '.js-status-test');
            $this->unFindCss($page, '.js-status-test.hidden');
        }
    }

    /**
     * Test item status failure display in search results
     *
     * @return void
     */
    public function testItemStatusFailure(): void
    {
        $this->changeConfigs(
            [
                'config' => $this->getConfigIniOverrides(true, 'msg'),
                'Demo' => $this->getDemoIniOverrides(true, 'Available', true, 100),
            ]
        );

        $page = $this->goToSearchResults();
        $this->assertEquals(
            'Simulated failure',
            $this->findCssAndGetText($page, '.result-body .callnumAndLocation.text-danger')
        );
    }

    /**
     * Test holdings tab
     *
     * @param mixed  $availability      Item availability status
     * @param string $status            Status display string
     * @param string $expected          Expected availability display status
     * @param string $expectedType      Expected status type (e.g. 'success')
     * @param string $multipleLocations Configuration setting for multiple locations
     *
     * @dataProvider itemStatusAndHoldingsProvider
     *
     * @return void
     */
    public function testHoldings(
        $availability,
        string $status,
        string $expected,
        string $expectedType,
        string $multipleLocations
    ): void {
        $this->changeConfigs(
            [
                'config' => $this->getConfigIniOverrides(false, $multipleLocations),
                'Demo' => $this->getDemoIniOverrides($availability, $status),
            ]
        );

        $page = $this->goToRecord();
        $this->assertEquals($expected, $this->findCssAndGetText($page, ".holdings-tab span.text-$expectedType"));
    }

    /**
     * Get config.ini override settings for testing ILS functions.
     *
     * @param bool   $fullStatus        Whether to show full item status in results
     * @param string $multipleLocations Setting to use for multiple locations
     *
     * @return array
     */
    protected function getConfigIniOverrides(bool $fullStatus, string $multipleLocations): array
    {
        return [
            'Catalog' => [
                'driver' => 'Demo',
            ],
            'Item_Status' => [
                'show_full_status' => $fullStatus,
                'multiple_locations' => $multipleLocations,
            ],
        ];
    }

    /**
     * Get Demo.ini override settings for testing ILS functions.
     *
     * @param mixed  $availability       Item availability status
     * @param string $statusMsg          Status display string
     * @param bool   $addExtraItems      Whether to add extra items to ensure the
     * status logic works properly
     * @param int    $failureProbability Failure probability
     *
     * @return array
     */
    protected function getDemoIniOverrides(
        $availability,
        string $statusMsg,
        bool $addExtraItems = false,
        int $failureProbability = 0
    ): array {
        $items = [];
        // If the requested item is available or uncertain, add other items before
        // (if allowed) to test that the correct status prevails:
        if ($addExtraItems && $availability) {
            // Test Location:
            $item = $this->getFakeItem();
            $item['availability'] = AvailabilityStatusInterface::STATUS_UNAVAILABLE;
            $item['status'] = 'Foo';
            $items[] = $item;

            // "main" location:
            $item = $this->getFakeItem();
            $item['availability'] = AvailabilityStatusInterface::STATUS_UNAVAILABLE;
            $item['status'] = 'Foo';
            $item['location'] = 'main';
            $items[] = $item;
            if (AvailabilityStatusInterface::STATUS_UNCERTAIN !== $availability) {
                $item = $this->getFakeItem();
                $item['availability'] = AvailabilityStatusInterface::STATUS_UNCERTAIN;
                $item['status'] = 'Foo';
                $item['location'] = 'main';
                $items[] = $item;
            }
        }
        $item = $this->getFakeItem();
        if (null === $availability) {
            $item['use_unknown_message'] = true;
            $item['availability'] = false;
        } else {
            $item['availability'] = $availability;
        }
        $item['status'] = $statusMsg;
        $item['location'] = 'main';
        if (AvailabilityStatusInterface::STATUS_AVAILABLE === $item['availability']) {
            $item['services'] = ['loan', 'presentation'];
        }
        $items[] = $item;

        // If the requested item is available or uncertain, add one more item to test
        // handling order:
        if ($addExtraItems && AvailabilityStatusInterface::STATUS_AVAILABLE === $availability) {
            // Test Location:
            $item = $this->getFakeItem();
            $item['availability'] = AvailabilityStatusInterface::STATUS_AVAILABLE;
            $item['status'] = 'Foo';
            $items[] = $item;
        }

        return [
            'Records' => [
                'services' => [],
            ],
            'Failure_Probabilities' => [
                'getHolding' => $failureProbability,
                'getStatuses' => $failureProbability,
            ],
            'StaticHoldings' => ['testsample1' => json_encode($items)],
            'Users' => ['catuser' => 'catpass'],
        ];
    }

    /**
     * Get search results page
     *
     * @return DocumentElement
     */
    protected function goToSearchResults(): DocumentElement
    {
        $session = $this->getMinkSession();
        $session->visit(
            $this->getVuFindUrl() . '/Search/Results?lookfor='
            . urlencode('id:(testsample1)')
        );
        $page = $session->getPage();
        $this->waitForPageLoad($page);
        $this->findCss($page, '.js-item-done');
        $this->unFindCss($page, '.js-item-pending');
        return $page;
    }

    /**
     * Get record page
     *
     * @return DocumentElement
     */
    protected function goToRecord(): DocumentElement
    {
        $session = $this->getMinkSession();
        $session->visit(
            $this->getVuFindUrl() . '/Record/testsample1'
        );
        $page = $session->getPage();
        $this->waitForPageLoad($page);
        return $page;
    }
}
