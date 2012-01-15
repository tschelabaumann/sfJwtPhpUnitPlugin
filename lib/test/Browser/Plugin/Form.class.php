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

  /** Returns a reference to an sfForm instance from the action stack.
   *
   * @param string $var Specify the name of the variable to retrieve.  If null,
   *  the first bound form in the action will be returned (if one exists).
   *
   * @return sfForm Note:  If no form object can be found, this method returns
   *  null.
   */
  public function invoke( $var = null )
  {
    /** @var $Action sfAction */
    /** @noinspection PhpUndefinedMethodInspection */
    $Action =
      $this->getBrowser()
        ->getContext()
          ->getActionStack()
          ->getLastEntry()
            ->getActionInstance();

    if( $var )
    {
      if( $form = $Action->getVar($var) and ($form instanceof sfForm) )
      {
        return $form;
      }
    }
    else
    {
      foreach( $Action->getVarHolder()->getAll() as $name => $value )
      {
        /** @noinspection PhpUndefinedMethodInspection */
        if( ($value instanceof sfForm) and $value->isBound() )
        {
          return $value;
        }
      }
    }

    return null;
  }
}