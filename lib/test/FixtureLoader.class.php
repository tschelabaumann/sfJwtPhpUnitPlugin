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

/** Loads fixtures for unit/functional tests.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_FixtureLoader
{
  protected
    $_fixturesLoaded,
    $_varHolder,
    $_depth;

  /** Init the class instance.
   */
  public function __construct(  )
  {
    $this->_depth = 0;

    $this->flushFixtures();
    $this->_varHolder = new sfParameterHolder();
  }

  /** Loads a fixture file.
   *
   * @param string|string[] $fixture  The name(s) of the fixture file(s)
   *  (e.g., test_data.yml).
   * @param bool            $prod     Whether to load production fixtures
   *  instead of test fixtures.
   * @param string          $plugin   The name of the plugin to load fixtures
   *  for.
   * @param bool            $force    If true, the fixture will be loaded
   *  even if it has already been loaded.
   *
   * @return mixed Some fixture files can return a value.  If an array value is
   *  passed in, an array will be returned as:
   *   {fixture_file_name: return_value, ...}
   *
   * If the fixture file was already loaded (and $force is false), loadFixture()
   *  will return false.
   */
  public function loadFixture(
    $fixture,
    $prod     = false,
    $plugin   = null,
    $force    = false
  )
  {
    if( is_array($fixture) )
    {
      $res = array();
      foreach( $fixture as $file )
      {
        $res[$file] = $this->loadFixture($file, $prod, $plugin, $force);
      }
      return $res;
    }
    else
    {
      $basedir = $this->getFixtureDir($prod, $plugin);
      if( ! is_dir($basedir) )
      {
        throw new InvalidArgumentException(sprintf(
          'Fixture directory "%s" does not exist.',
            $basedir
        ));
      }

      /* Check to see if this fixture has already been loaded. */
      if( $force or ! $this->isFixtureLoaded($basedir . $fixture) )
      {
        /* Check file extension to determine which fixture loader to use. */
        if( $pos = strrpos($fixture, '.') )
        {
          $class =
              'Test_FixtureLoader_Loader_'
            . ucfirst(strtolower(substr($fixture, $pos + 1)));

          ++$this->_depth;

          /** @var $Loader Test_FixtureLoader_Loader */
          $Loader = new $class($this, $plugin);
          $res = $Loader->load($fixture, $basedir);

          --$this->_depth;
        }
        else
        {
          throw new InvalidArgumentException(sprintf(
            'Fixture filename "%s" has no extension.',
              $fixture
          ));
        }

        $this->_fixturesLoaded[$basedir . $fixture] = true;
        return $res;
      }
      else
      {
        /* Fixture file was already loaded. */
        return false;
      }
    }
  }

  /** Returns whether a fixture has been loaded.
   *
   * @param string $fixture
   *
   * @return bool
   */
  public function isFixtureLoaded( $fixture )
  {
    return ! empty($this->_fixturesLoaded[$fixture]);
  }

  /** Resets $_fixturesLoaded.  Generally only used by
   *   Test_Case::flushDatabase().
   *
   * @return Test_FixtureLoader $this
   */
  public function flushFixtures(  )
  {
    $this->_fixturesLoaded = array();
    return $this;
  }

  /** Returns the path to the fixture directory for this test case.
   *
   * @param bool    $production   Whether to load production fixtures.
   * @param string  $plugin       Which plugin to load fixtures for.
   *
   * @return string
   */
  public function getFixtureDir( $production = false, $plugin = null )
  {
    if( $plugin )
    {
      $config =
      sfContext::getInstance()
      ->getConfiguration()
      ->getPluginConfiguration($plugin);

      return sprintf(
        '%s/%s/fixtures/',
        $config->getRootDir(),
        ($production ? 'data' : 'test')
      );
    }
    else
    {
      return sprintf(
        '%s/fixtures/',
        sfConfig::get($production ? 'sf_data_dir' : 'sf_test_dir')
      );
    }
  }

  /** Accessor for $_depth.
   *
   * Implemented as an instance method rather than a static method so that it is
   *  accessible to PHP fixture files.
   *
   * @return int
   */
  public function getDepth(  )
  {
    return $this->_depth;
  }

  /** Generic accessor.
   *
   * @param string $var
   *
   * @return mixed
   */
  public function __get( $var )
  {
    return $this->_varHolder->$var;
  }

  /** Generic modifier.
   *
   * @param string $var
   * @param mixed  $val
   *
   * @return mixed $val
   */
  public function __set( $var, $val )
  {
    return $this->_varHolder->$var = $val;
  }

  /** Generic isset() handler.
   *
   * @param string $var
   *
   * @return bool
   */
  public function __isset( $var )
  {
    return isset($this->_varHolder->$var);
  }

  /** Generic unset() handler.
   *
   * @param string $var
   *
   * @return void
   */
  public function __unset( $var )
  {
    unset($this->_varHolder->$var);
  }
}