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

/** An assertion constraint that checks whether an sfForm instance passes
 *    validation.
 *
 * If a form fails validation, its error messages will be output with the
 *  failure message to make it easier to diagnose test failures.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.constraint
 */
class Test_Constraint_FormIsValid extends PHPUnit_Framework_Constraint
{
  protected
    $_expected;

  /** Init the class instance.
   *
   * @param bool $expected
   */
  public function __construct( $expected )
  {
    if( ! is_bool($expected) )
    {
      throw PHPUnit_Util_InvalidArgumentHelper::factory(0, 'bool');
    }

    $this->_expected = $expected;
  }

  /** Checks the status code.
   *
   * @param sfForm $form
   *
   * @return bool
   */
  protected function matches( $form )
  {
    if( ! ($form instanceof sfForm) )
    {
      throw PHPUnit_Util_InvalidArgumentHelper::factory(0, 'Test_Browser');
    }

    return ($form->isValid() == $this->_expected);
  }

  /** Returns a generic string representation of the object.
   *
   * @return string
   */
  public function toString(  )
  {
    return sprintf('is equal to <bool:%d>', $this->_expected);
  }

  /** Appends relevant error message information to a failed status check.
   *
   * @param sfForm $form
   *
   * @return string
   */
  protected function failureDescription( $form )
  {
    return sprintf(
      '%s is%s valid',
        get_class($form),
        ($this->_expected ? '' : ' not')
    );
  }

  /** Return additional failure description where needed
   *
   * The function can be overritten to provide additional failure information
   *  like a diff
   *
   * @param  sfForm $form Evaluated value or object.
   * @return string
   */
  protected function additionalFailureDescription( $form )
  {
    /* If the form has errors, add them to the message, since that's what we're
     *  going to be interested in when the assertion fails).
     *
     * Note that we only have to check for this if we were expecting the form to
     *  be valid (give it a second; it'll come to you).
     */
    if( $this->_expected and ($errors = $form->getErrorSchema()->getErrors()) )
    {
      return sprintf(
        'Form has errors:%s%s',
          PHP_EOL,
          print_r(array_map('strval', $errors), true)
      );
    }

    return '';
  }
}