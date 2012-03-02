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

/** Maintains global state for test cases.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_State
{
  static private
    $_dbRebuilt,
    $_dbFlushTree,
    $_configs;

  /** @var ProjectConfiguration */
  protected $_configuration;

  /** Inits the class instance.
   *
   * @param $configuration ProjectConfiguration
   */
  public function __construct( ProjectConfiguration $configuration )
  {
    $this->_configuration = $configuration;
  }

  /** Flush the database and reload base fixtures.
   *
   * @param bool $rebuild
   *  true:   The database will be dropped and rebuilt.
   *  false:  The method will try just to flush the data.
   *
   * Note that the first time flushDatabase() is called (per execution), the
   *  database will be rebuilt regardless of $rebuild.
   *
   * @return static
   *
   * @todo Move Doctrine-specific functionality into separate class.
   */
  public function flushDatabase( $rebuild = false )
  {
    if( $db = $this->getDatabaseConnection() )
    {
      /* The first time we run a test case, drop and rebuild the database.
       *
       * After that, we can simply truncate all tables for speed.
       */
      if( empty(self::$_dbRebuilt) or $rebuild )
      {
        /* Don't try to drop the database unless it exists. */
        $name = $this->getDatabaseName();
        /** @noinspection PhpUndefinedFieldInspection */
        if( $name and $db->import->databaseExists($name) )
        {
          $db->dropDatabase();
        }

        $db->createDatabase();

        Doctrine_Core::loadModels(
          sfConfig::get('sf_lib_dir').'/model/doctrine',
          Doctrine_Core::MODEL_LOADING_CONSERVATIVE
        );
        Doctrine_Core::createTablesFromArray(Doctrine_Core::getLoadedModels());

        self::$_dbRebuilt = true;
      }
      else
      {
        /* Determine the order we need to load models. */
        if( ! isset(self::$_dbFlushTree) )
        {
          /** @noinspection PhpUndefinedFieldInspection */
          $models = $db->unitOfWork->buildFlushTree(
            Doctrine_Core::getLoadedModels()
          );
          self::$_dbFlushTree = array_reverse($models);
        }

        /* Delete records, paying special attention to SoftDelete. */
        foreach( self::$_dbFlushTree as $model )
        {
          $table = Doctrine_Core::getTable($model);

          if( $table->hasTemplate('SoftDelete') )
          {
            /** @var $record Doctrine_Template_SoftDelete */
            foreach( $table->createQuery()->execute() as $record )
            {
              $record->hardDelete();
            }
          }

          $table->createQuery()->delete()->execute();
        }
      }
    }

    return $this;
  }

  /** Removes anything in the uploads directory.
   *
   * @return static
   */
  public function flushUploads(  )
  {
    $Filesystem = new sfFilesystem();
    $Filesystem->remove(
      sfFinder::type('any')->in(sfConfig::get('sf_upload_dir'))
    );

    return $this;
  }

  /** Restores all sfConfig values to their state before the current test was
   *   run.
   *
   * @return static
   */
  public function flushConfigs(  )
  {
    if( isset(self::$_configs) )
    {
      sfConfig::clear();
      sfConfig::add(self::$_configs);
    }
    else
    {
      self::$_configs = sfConfig::getAll();
    }

    return $this;
  }

  /** Gets the Doctrine connection, initializing it if necessary.
   *
   * @return Doctrine_Connection
   */
  protected function getDatabaseConnection(  )
  {
    if( sfConfig::get('sf_use_database') )
    {
      try
      {
        return Doctrine_Manager::connection();
      }
      catch( Doctrine_Connection_Exception $e )
      {
        new sfDatabaseManager(sfContext::getInstance()->getConfiguration());
        return Doctrine_Manager::connection();
      }
    }

    return null;
  }

  /** Returns the name of the Doctrine database.
   *
   * @return string
   */
  protected function getDatabaseName(  )
  {
    $db = $this->getDatabaseConnection();

    /* Why oh why does Doctrine_Connection not do this for us? */
    if( ! $dsn = $db->getOption('dsn') )
    {
      throw new RuntimeException(sprintf(
        'Doctrine connection "%s" does not have a DSN!',
          $db->getName()
      ));
    }

    /** @noinspection PhpParamsInspection */
    $info = $db->getManager()->parsePdoDsn($dsn);
    return (isset($info['dbname']) ? $info['dbname'] : null);
  }
}