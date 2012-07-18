<?php

class IceBreadcrumbsItem
{

  protected
      $text, $uri,
      $options = array();

  /**
   * Constructor
   *
   * @param string $text
   * @param string $uri
   * @param array $options
   *
   * @return \IceBreadcrumbsItem
   */
  public function __construct($text, $uri = null, array $options = array())
  {
    $this->text    = (string)$text;
    $this->uri     = (string)$uri;
    $this->options = $options;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return (string)$this->text;
  }

  /**
   * Retrieve the uri of the item
   *
   * @return string
   */
  public function getUri()
  {
    return $this->uri;
  }

  /**
   * Retrieve the text of the item
   *
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }

  /**
   * Retrieve the text of the item
   *
   * @param string $v
   */
  public function setText($v)
  {
    $this->text = (string)$v;
  }

  /**
   * Retrieve the title of the item
   *
   * @return string
   */
  public function getTitle()
  {
    return isset($this->options['title']) ? $this->options['title'] : null;
  }

  /**
   * Retrieve the title of the item
   *
   * @param string $v
   */
  public function setTitle($v)
  {
    $this->options['title'] = (string)$v;
  }

  /**
   * Retrieve the options of the item
   *
   * @return array
   */
  public function getOptions()
  {
    return $this->options;
  }
}

class IceBreadcrumbs
{

  /**
   * @var IceBreadcrumbs
   */
  static protected $instance = null;

  /* @var $items IceBreadcrumbsItem[] */
  protected $items = array();
  protected $is_full = false;
  protected $read_only = false;

  /* @var $context sfContext */
  private $context = null;

  /**
   * Constructor
   *
   * @param sfContext $context
   * @param boolean $createRoot
   * @param string $title
   * @param string $route
   *
   */
  public function __construct(sfContext $context, $createRoot = false, $title = null, $route = null)
  {
    $this->context = $context;

    if ($createRoot)
    {
      if (is_null($title))
      {
        $title = '<span class="sprites home">&nbsp;</span>';
      }
      if (is_null($route))
      {
        $route = '@homepage';
      }
      $this->setRoot($title, $route);
    }
  }

  /**
   * Add an item
   *
   * @param string  $text
   * @param string  $uri
   * @param string  $title
   * @param boolean $is_last
   *
   * @return IceBreadcrumbs for fluid interface
   */
  public function addItem($text, $uri = null, $title = null, $is_last = false)
  {
    if ($this->read_only !== true && $this->is_full !== true)
    {
      // We want to make sure index "0" is only used for the Root node
      $i = count($this->items) + 1;

      $this->items[$i] = new IceBreadcrumbsItem($text, $uri, array('title'=>$title));
      $this->items   = array_unique($this->items);

      $this->save();
    }

    if ($is_last === true)
    {
      $this->is_full = true;
    }

    return $this;
  }

  /**
   * Delete all existings items
   *
   * @return IceBreadcrumbs for fluid interface
   */
  public function clearItems()
  {
    if ($this->read_only !== true)
    {
      $this->items = array();
      $this->save();
    }

    return $this;
  }

  /**
   * Get the unique IceBreadcrumbs instance (singleton)
   *
   * @param \sfContext $context
   * @return \IceBreadcrumbs
   */
  public static function getInstance(sfContext $context)
  {
    if (self::$instance === null)
    {
      if (!self::$instance = $context->getRequest()->getParameter('IceBreadcrumbs'))
      {
        self::$instance = new IceBreadcrumbs($context);
        self::$instance->save();
      }
    }

    return self::$instance;
  }

  /**
   * Retrieve an array of IceBreadcrumbsItem
   *
   * @param  int $offset
   * @return IceBreadcrumbsItem[]
   */
  public function getItems($offset = 0)
  {
    return array_slice($this->items, $offset);
  }

  /**
   * Redefine the root item
   *
   * @param string $text
   * @param string $uri
   *
   * @return IceBreadcrumbs for fluid interface
   */
  public function setRoot($text, $uri)
  {
    $this->items[0] = new IceBreadcrumbsItem($text, $uri);
    ksort($this->items, SORT_NUMERIC);
    $this->save();
  }

  /**
   * Save IceBreadcrumbs instance as response parameter (allows action caching)
   */
  protected function save()
  {
    $this->getContext()->getRequest()->setParameter('IceBreadcrumbs', $this);
  }

  /**
   * @return sfContext
   */
  public function getContext()
  {
    return $this->context;
  }


}
