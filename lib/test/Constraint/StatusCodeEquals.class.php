<?php
/**
 * Copyright (c) 2011 J. Walter Thompson dba JWT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/** An assertion constraint that checks a the Test_Browser's status code, with
 *    reporting of error codes on failure.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.constraint
 */
class Test_Constraint_StatusCodeEquals extends PHPUnit_Framework_Constraint
{
  const
    MESSAGE = 'response has %d HTTP status code (got: %d %s)';

  protected
    $_expected;

  /** Init the class instance.
   *
   * @param int $expected
   */
  public function __construct( $expected )
  {
    if( ! ctype_digit((string) $expected) )
    {
      throw PHPUnit_Util_InvalidArgumentHelper::factory(0, 'int');
    }

    $this->_expected = $expected;
  }

  /** Checks the status code.
   *
   * @param Test_Browser $browser
   *
   * @return bool
   */
  protected function matches( $browser )
  {
    if( ! ($browser instanceof Test_Browser) )
    {
      throw PHPUnit_Util_InvalidArgumentHelper::factory(0, 'Test_Browser');
    }

    return ($browser->getResponse()->getStatusCode() == $this->_expected);
  }

  /** Returns a generic string representation of the object.
   *
   * @return string
   */
  public function toString()
  {
    return sprintf('is equal to <int:%d>', $this->_expected);
  }

  /** Appends relevant error message information to a failed status check.
   *
   * @param Test_Browser  $browser
   *
   * @return string
   */
  protected function failureDescription( $browser )
  {
    $code = $browser->getResponse()->getStatusCode();

    /* See if there's an error we can report. */
    if( ! $error = (string) $browser->getError() )
    {
      $error = Zend_Http_Response::responseCodeAsText($code);
    }

    return sprintf(
      self::MESSAGE,
        $this->_expected,
        $code,
        $error
    );
  }
}