<?php
/**
 * WordPress Coding Standard.
 *
 * @package WPCS\WordPressCodingStandards
 * @link    https://github.com/WordPress/WordPress-Coding-Standards
 * @license https://opensource.org/licenses/MIT MIT
 */

declare( strict_types=1 );

namespace WordPressCS\WordPress\Sniffs\PHP;

use PHPCSUtils\Utils\PassedParameters;
use WordPressCS\WordPress\AbstractFunctionParameterSniff;

/**
 * Flag calling preg_quote() without the second ($delimiter) parameter.
 *
 * @since 1.0.0
 */
final class PregQuoteDelimiterSniff extends AbstractFunctionParameterSniff {

	/**
	 * The group name for this group of functions.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $group_name = 'preg_quote';

	/**
	 * List of functions this sniff should examine.
	 *
	 * @link https://www.php.net/preg_quote
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, true> Key is function name, value irrelevant.
	 */
	protected array $target_functions = array(
		'preg_quote' => true,
	);

	/**
	 * Process the parameters of a matched function.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $stackPtr        The position of the current token in the stack.
	 * @param string $group_name      The name of the group which was matched.
	 * @param string $matched_content The token content (function name) which was matched
	 *                                in lowercase.
	 * @param array  $parameters      Array with information about the parameters.
	 *
	 * @return null
	 */
	public function process_parameters( int $stackPtr, string $group_name, string $matched_content, array $parameters ): ?int {

		$delimiter = PassedParameters::getParameterFromStack( $parameters, 2, 'delimiter' );
		if ( false !== $delimiter ) {
			return null;
		}

		$this->phpcsFile->addWarning(
			'Passing the $delimiter parameter to preg_quote() is strongly recommended.',
			$stackPtr,
			'Missing'
		);

		return null;
	}
}
