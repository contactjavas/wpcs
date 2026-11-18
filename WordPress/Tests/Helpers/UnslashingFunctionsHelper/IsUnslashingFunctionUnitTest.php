<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

declare( strict_types=1 );

namespace WordPressCS\WordPress\Tests\Helpers\UnslashingFunctionsHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WordPressCS\WordPress\Helpers\UnslashingFunctionsHelper;

/**
 * Tests for the `UnslashingFunctionsHelper::is_unslashing_function()` method.
 *
 * @since 3.4.0
 *
 * @covers \WordPressCS\WordPress\Helpers\UnslashingFunctionsHelper::is_unslashing_function
 */
final class IsUnslashingFunctionUnitTest extends TestCase {

	// phpcs:disable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound
	/**
	 * Test is_unslashing_function().
	 *
	 * @dataProvider dataIsUnslashingFunction
	 *
	 * @param string $functionName   The function name to test.
	 * @param bool   $expectedResult The expected return value.
	 *
	 * @return void
	 */
	#[DataProvider( 'dataIsUnslashingFunction' )]
	public function testIsUnslashingFunction( string $functionName, bool $expectedResult ): void {
		$this->assertSame(
			$expectedResult,
			UnslashingFunctionsHelper::is_unslashing_function( $functionName )
		);
	}
	// phpcs:enable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound

	/**
	 * Data provider.
	 *
	 * @see testIsUnslashingFunction()
	 *
	 * @return array<string, array<string, bool|string>>
	 */
	public static function dataIsUnslashingFunction(): array {
		return array(
			'lowercase_name'             => array(
				'functionName'   => 'wp_unslash',
				'expectedResult' => true,
			),
			'mixedcase_name'             => array(
				'functionName'   => 'sTrIpSlAsHeS_DeEp',
				'expectedResult' => true,
			),
			'not_an_unslashing_function' => array(
				'functionName'   => 'stripslashes',
				'expectedResult' => false,
			),
		);
	}
}
