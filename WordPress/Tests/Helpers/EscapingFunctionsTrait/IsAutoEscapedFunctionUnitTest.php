<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

declare( strict_types=1 );

namespace WordPressCS\WordPress\Tests\Helpers\EscapingFunctionsTrait;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WordPressCS\WordPress\Helpers\EscapingFunctionsTrait;

/**
 * Tests for the `EscapingFunctionsTrait::is_auto_escaped_function()` method.
 *
 * @since 3.4.2
 *
 * @covers \WordPressCS\WordPress\Helpers\EscapingFunctionsTrait::is_auto_escaped_function
 */
final class IsAutoEscapedFunctionUnitTest extends TestCase {

	/**
	 * Test class using the EscapingFunctionsTrait for testing purposes.
	 *
	 * @var object
	 */
	private ?object $testClass = null;

	/**
	 * Set up the test class for each test.
	 *
	 * @before
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->testClass = new class() {
			use EscapingFunctionsTrait;
		};
	}

	// phpcs:disable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound
	/**
	 * Test is_auto_escaped_function() with default auto escaped functions.
	 *
	 * @dataProvider dataIsAutoEscapedFunction
	 *
	 * @param string $functionName   The function name to test.
	 * @param bool   $expectedResult The expected return value.
	 *
	 * @return void
	 */
	#[DataProvider( 'dataIsAutoEscapedFunction' )]
	public function testIsAutoEscapedFunction( string $functionName, bool $expectedResult ): void {
		$this->assertSame(
			$expectedResult,
			$this->testClass->is_auto_escaped_function( $functionName )
		);
	}
	// phpcs:enable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound

	/**
	 * Data provider.
	 *
	 * @see testIsAutoEscapedFunction()
	 *
	 * @return array<string, array<string, bool|string>>
	 */
	public static function dataIsAutoEscapedFunction(): array {
		return array(
			'lowercase_name'               => array(
				'functionName'   => 'bloginfo',
				'expectedResult' => true,
			),
			'mixedcase_name'               => array(
				'functionName'   => 'bOdY_ClAsS',
				'expectedResult' => true,
			),
			'not_an_auto_escaped_function' => array(
				'functionName'   => 'esc_html',
				'expectedResult' => false,
			),
		);
	}

	/**
	 * Test that a custom auto-escaped function is recognized.
	 *
	 * @return void
	 */
	public function testCustomAutoEscapedFunctionIsRecognized(): void {
		$this->assertFalse(
			$this->testClass->is_auto_escaped_function( 'my_custom_auto_escaped' ),
			'Custom auto-escaped function is recognized even though not set'
		);

		$this->testClass->customAutoEscapedFunctions = array( 'my_custom_auto_escaped' );
		$this->assertTrue(
			$this->testClass->is_auto_escaped_function( 'my_custom_auto_escaped' ),
			'Custom auto-escaped function is not recognized when set'
		);
		$this->assertTrue(
			$this->testClass->is_auto_escaped_function( 'bloginfo' ),
			'WP native auto-escaped function is not recognized when custom functions are set'
		);
	}

	/**
	 * Test that the auto-escaped function list is updated when the custom
	 * auto-escaped functions are changed.
	 *
	 * @return void
	 */
	public function testIsAutoEscapedFunctionUpdatesWhenCustomFunctionsChange(): void {
		$this->testClass->customAutoEscapedFunctions = array( 'first_custom' );
		$this->assertTrue(
			$this->testClass->is_auto_escaped_function( 'first_custom' ),
			'Custom auto-escaped function is not recognized when set (first)'
		);

		$this->testClass->customAutoEscapedFunctions = array( 'second_custom' );
		$this->assertTrue(
			$this->testClass->is_auto_escaped_function( 'second_custom' ),
			'Custom auto-escaped function is not recognized when set (second)'
		);
		$this->assertFalse(
			$this->testClass->is_auto_escaped_function( 'first_custom' ),
			'Custom auto-escaped function is still recognized while it should have been removed (first)'
		);
	}

	/**
	 * Safeguard the strict boundaries between escaping functions and auto-escaped functions.
	 *
	 * @return void
	 */
	public function testIsAutoEscapedFunctionDoesNotRecognizeEscapingFunctions(): void {
		$this->testClass->customEscapingFunctions    = array( 'custom_escape' );
		$this->testClass->customAutoEscapedFunctions = array( 'custom_autoescape' );

		$this->assertTrue(
			$this->testClass->is_auto_escaped_function( 'custom_autoescape' ),
			'Custom auto-escaped function is not recognized when set'
		);
		$this->assertTrue(
			$this->testClass->is_auto_escaped_function( 'the_author' ),
			'WP native auto-escaped function is not recognized'
		);

		$this->assertFalse(
			$this->testClass->is_auto_escaped_function( 'custom_escape' ),
			'Custom escaping function is recognized as auto-escaped function'
		);
		$this->assertFalse(
			$this->testClass->is_auto_escaped_function( 'number_format' ),
			'WP (PHP) native escaping function is recognized as auto-escaped function'
		);
	}
}
