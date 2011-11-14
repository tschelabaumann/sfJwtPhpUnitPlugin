<?php
/** A Test_Browser_Listener that allows a test to inject arbitrary event
 *    listeners into the browser's event dispatcher.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.listener
 */
class Test_Browser_Listener_Callback
  implements Test_Browser_Listener
{
  protected
      $_event     = null
    , $_callbacks = array();

  /** Init the class instance.
   *
   * @param $event    string
   * @param $callback callback,...
   */
  public function __construct( $event, $callback/*, ... */ )
  {
    $args = func_get_args();

    $this->_event     = array_shift($args);
    $this->_callbacks = $args;
  }

  /** Returns the events that this listener should be registered for.
   *
   * @return array
   */
  public function getEventNames()
  {
    return array($this->_event);
  }

  /** Invokes the listener.
   *
   * @param sfEvent $event
   *
   * @return void
   */
  public function invoke( sfEvent $event )
  {
    foreach( $this->_callbacks as $callback )
    {
      call_user_func($callback, $event);
    }
  }
}