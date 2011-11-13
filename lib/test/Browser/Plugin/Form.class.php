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

/** Exposes the sfForm instance bound to the request.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 *
 * Partial list of methods exposed for the encapsulated sfForm object (other
 *  methods are available, but they are not read-only and are probably not
 *  useful for testing):
 *
 * @method string                 __toString()
 * @method boolean                hasGlobalErrors()
 * @method array                  getGlobalErrors()
 * @method boolean                isBound()
 * @method string[]               getTaintedValues()
 * @method boolean                isValid()
 * @method boolean                hasErrors()
 * @method string[]               getValues()
 * @method mixed                  getValue(string $field)
 * @method string|boolean         getName()
 * @method sfValidatorErrorSchema getErrorSchema()
 * @method sfForm[]               getEmbeddedForms()
 * @method sfForm                 getEmbeddedForm(string $name)
 * @method sfValidatorBase        getValidator(string $name)
 * @method sfValidatorSchema      getValidatorSchema()
 * @method sfWidgetForm           getWidget(string $name)
 * @method sfWidgetFormSchema     getWidgetSchema()
 * @method string[]               getStylesheets()
 * @method string[]               getJavaScripts()
 * @method array                  getOptions()
 * @method mixed                  getOption(string $name, mixed $default = null)
 * @method string                 getDefault(string $name)
 * @method boolean                hasDefault(string $name)
 * @method string[]               getDefaults()
 * @method string                 getCSRFToken(string $secret = null)
 * @method boolean                isCSRFProtected()
 * @method boolean                isMultipart()
 * @method sfFormFieldSchema      getFormFieldSchema()
 */
class Test_Browser_Plugin_Form extends Test_Browser_Plugin
{
  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  public function getMethodName(  )
  {
    return 'getForm';
  }

  /** Returns a reference to the sfForm instance from the action stack.
   *
   * Note:  If no form was submitted, this method returns null.
   *
   * @return Test_Browser_Plugin_Form($this)|null
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      /** @var $Action sfAction */
      $Action =
        $this->getBrowser()
          ->getContext()
            ->getActionStack()
            ->getLastEntry()
              ->getActionInstance();

      foreach( $Action->getVarHolder()->getAll() as $name => $value )
      {
        if( $value instanceof sfForm and $value->isBound() )
        {
          $this->setEncapsulatedObject($value);
        }
      }
    }

    return $this->hasEncapsulatedObject() ? $this : null;
  }
}