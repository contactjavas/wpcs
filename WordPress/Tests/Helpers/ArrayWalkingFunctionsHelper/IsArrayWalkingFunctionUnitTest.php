<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace WordPressCS\WordPress\Tests\Helpers\ArrayWalkingFunctionsHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WordPressCS\WordPress\Helpers\ArrayWalkingFunctionsHelper;

/**
 * Tests for the `ArrayWalkingFunctionsHelper::is_array_walking_function()` method.
 *
 * @since 3.4.0
 *
 * @covers \WordPressCS\WordPress\Helpers\ArrayWalkingFunctionsHelper::is_array_walking_function
 */
final class IsArrayWalkingFunctionUnitTest extends TestCase {

	// phpcs:disable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound
	/**
	 * Test is_array_walking_function().
	 *
	 * @dataProvider dataIsArrayWalkingFunction
	 *
	 * @param string $functionName   The function name to test.
	 * @param bool   $expectedResult The expected return value.
	 *
	 * @return void
	 */
	#[DataProvider( 'dataIsArrayWalkingFunction' )]
	public function testIsArrayWalkingFunction( $functionName, $expectedResult ) {
		$this->assertSame(
			$expectedResult,
			ArrayWalkingFunctionsHelper::is_array_walking_function( $functionName )
		);
	}
	// phpcs:enable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound

	/**
	 * Data provider.
	 *
	 * @see testIsArrayWalkingFunction()
	 *
	 * @return array<string, array<string, bool|string>>
	 */
	public static function dataIsArrayWalkingFunction() {
		return array(
			'lowercase_name' => array(
				'functionName'   => 'array_map',
				'expectedResult' => true,
			),
			'mixedcase_name' => array(
				'functionName'   => 'mAp_DeEp',
				'expectedResult' => true,
			),
			'not_an_array_walking_function' => array(
				'functionName'   => 'array_filter',
				'expectedResult' => false,
			),
		);
	}
}
