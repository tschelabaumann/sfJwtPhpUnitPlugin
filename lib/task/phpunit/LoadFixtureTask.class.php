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

/** Loads test fixtures into a database of the user's choosing.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task.phpunit
 */
class LoadFixtureTask extends BasePhpunitTask
{
  const
    DEFAULT_ENV = 'test';

  protected
    $_env,
    $_fixtures,
    $_plugin,
    $_rebuild;

  public function configure(  )
  {
    parent::configure();

    $this->addArguments(array(
      new sfCommandArgument(
        'fixture',
        sfCommandArgument::REQUIRED | sfCommandArgument::IS_ARRAY,
        'Specify the test fixtures to load, similarly to calling $this->loadFixture(...) in a test case.'
      )
    ));

    $this->addOptions(array(
      new sfCommandOption(
        'plugin',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Specify the plugin that owns the fixture files.  This value can be overridden by prepending the plugin name and a colon to each fixture file path.'
      ),

      new sfCommandOption(
        'application',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'Specify the application configuration.',
        $this->getFirstApplication()
      ),

      new sfCommandOption(
        'env',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        sprintf(
          'Specify the environment.  Any value other than "%s" will require confirmation.',
            self::DEFAULT_ENV
        ),
        self::DEFAULT_ENV
      ),

      new sfCommandOption(
        'rebuild',
        null,
        sfCommandOption::PARAMETER_NONE,
        'Rebuild the database before loading test fixtures.  This requires confirmation.'
      ),

      new sfCommandOption(
        'no-confirmation',
        null,
        sfCommandOption::PARAMETER_NONE,
        'Suppress confirmation prompts.'
      )
    ));

    $this->name = 'load-fixture';
    $this->briefDescription = 'Loads test data fixtures into the database.';

    $this->detailedDescription = <<<END
Loads test data fixtures into the database.

Use this to load test data for spikes or to view the results of test scenarios
  in a browser.
END;
  }

  /**
   * @param array $args
   * @param array $opts
   *
   * @return void
   */
  public function execute( $args = array(), $opts = array() )
  {
    /* Validate incoming parameters. */
    if( ! $this->_importParams($this->_consolidateInput($args, $opts)) )
    {
      return;
    }

    $state = new Test_State($this->configuration);

    if( $this->_rebuild )
    {
      $state->flushDatabase();

      $this->logSection('rebuild', 'Database rebuilt.');
    }

    $loader = new Test_FixtureLoader($this->configuration);

    foreach( $this->_fixtures as $fixture )
    {
      $desc = sprintf(
        'fixture "%s" from %s',
          $fixture['path'],
          ($fixture['plugin'] ? "plugin {$fixture['plugin']}" : 'project')
      );

      $this->logSection('fixture', sprintf('Loading %s...', $desc));
      $loader->loadFixture($fixture['path'], false, $fixture['plugin']);
      $this->logSection('fixture', sprintf('Loaded %s.', $desc));
    }
  }

  /** Import parameters into the task's properties.
   *
   * @param $params array
   *
   * @return bool Returns false if the task should exit.
   */
  protected function _importParams( array $params )
  {
    /* Check --env first, since that determines if/how we should continue. */
    switch( $params['env'] )
    {
      case self::DEFAULT_ENV:
      case '':
        $this->_env = self::DEFAULT_ENV;
        break;

      default:
        if( empty($params['no-confirmation']) )
        {
          $confirm = $this->askConfirmation(sprintf(
            'You are about to load data into the database for the "%s" environment.  Are you you want to do this?',
              $params['env']
          ));

          if( ! $confirm )
          {
            $this->log('Operation canceled.');
            return false;
          }
        }

        $this->_env = $params['env'];
        break;
    }

    /* Check --plugin. */
    if( ! empty($params['plugin']) )
    {
      $this->_plugin =
        $this->_getPluginConfiguration($params['plugin'])->getName();
    }

    /* Finally, load fixture arguments. */
    foreach( $params['fixture'] as $path )
    {
      $exploded = explode(':', $path, 2);

      if( isset($exploded[1]) )
      {
        $this->_fixtures[] = array(
          'plugin'  => $this->_getPluginConfiguration($exploded[0])->getName(),
          'path'    => $exploded[1]
        );
      }
      else
      {
        $this->_fixtures[] = array(
          'plugin'  => $this->_plugin,
          'path'    => $path
        );
      }
    }

    $this->_rebuild = (! empty($params['rebuild']));

    return true;
  }
}