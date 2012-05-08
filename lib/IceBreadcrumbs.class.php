<?php

class IceBreadcrumbsItem
{

  protected $text;
  protected $uri;
  protected $title;

  /**
   * Constructor
   *
   * @param string $text
   * @param string $uri
   * @param string $title
   *
   * @return \IceBreadcrumbsItem
   */
  public function __construct($text, $uri = null, $title = null)
  {
    $this->text  = (string)$text;
    $this->uri   = (string)$uri;
    $this->title = (string)$title;
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
    return $this->title;
  }

  /**
   * Retrieve the title of the item
   *
   * @param string $v
   */
  public function setTitle($v)
  {
    $this->title = (string)$v;
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
   * @param string $title
   * @param string $route
   *
   */
  public function __construct(sfContext $context, $title = null, $route = null)
  {
    $this->context = $context;

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
      $this->items[] = new IceBreadcrumbsItem($text, $uri, $title);
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
        self::$instance->context = $context;
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
