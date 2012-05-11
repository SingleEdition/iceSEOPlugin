<?php

class IcePropelBreadcrumbs
{

  protected $config = null;
  protected $module = null;
  protected $action = null;
  protected $breadcrumbs = array();
  /* @var $context sfContext */
  protected $context = null;

  public function __construct($context, $module, $action)
  {
    $this->context = $context;
    $this->module  = $module;
    $this->action  = $action;

    $this->getConfig();
    $this->buildBreadcrumbs();
  }

  public function getConfig()
  {
    if ($this->config == null)
    {
      if ($file = $this->getContext()->getConfigCache()->checkConfig('config/breadcrumbs.yml', true))
      {
        sfConfig::add(include($file));
      }

      $this->config = sfConfig::get('ice_breadcrumbs');
    }

    return $this->config;
  }

  public function getBreadcrumbs()
  {
    return $this->breadcrumbs;
  }

  public function getSeparator()
  {
    $config = $this->getConfig();
    return isset($config['_separator']) ? $config['_separator'] : '>';
  }

  protected function buildBreadcrumb($item)
  {
    if ($item instanceof IceBreadcrumbsItem)
    {
      return $item;
    }

    $request    = $this->getContext()->getRequest();
    $controller = $this->getContext()->getController();

    if (isset($item['model']) && (boolean)$item['model'] === true)
    {
      $object = $request->getAttribute('sf_route')->getObject();

      if (isset($item['subobject']))
      {
        $subobject    = call_user_func(array($object, 'get' . $item['subobject']));
        $route_object = $subobject;
      }
      else
      {
        $route_object = $object;
      }

      $name  = preg_replace('/%(\w+)%/e', '$object->get$1()', $item['name']);
      $title = !empty($item['title']) ? preg_replace('/%(\w+)%/e', '$object->get$1()', $item['title']) : $name;
      $url   = !empty($item['route']) ?
          $controller->genUrl(array(
            'sf_route'   => $item['route'],
            'sf_subject' => $route_object
          )) :
          null;

      $breadcrumb = new IceBreadcrumbsItem($name, $url, array('title' => $title,));
    }
    else
    {
      $name  = preg_replace('/%(\w+)%/e', '$object->get$1()', $item['name']);
      $title = !empty($item['title']) ? preg_replace('/%(\w+)%/e', '$object->get$1()', $item['title']) : $name;
      $url   = isset($item['route']) ? $controller->genUrl($item['route']) : null;

      $name = $this->switchCase($name, $this->getCaseForItem($item));

      $breadcrumb = new IceBreadcrumbsItem($name, $url, array('title'=> $title));
    }

    return $breadcrumb;
  }

  protected function buildBreadcrumbs()
  {
    if (isset($this->config[$this->module]['_prepend']))
    {
      foreach ($this->config[$this->module]['_prepend'] as $item)
      {
        $this->breadcrumbs[] = $this->buildBreadcrumb($item);
      }
    }

    if (isset($this->config[$this->module]) && isset($this->config[$this->module][$this->action]))
    {
      $breadcrumbs_struct = $this->config[$this->module][$this->action];
    }
    else
    {
      $breadcrumbs_struct = array();
    }

    if (count($breadcrumbs_struct) > 0)
    {
      foreach ($breadcrumbs_struct as $item)
      {
        $this->breadcrumbs[] = $this->buildBreadcrumb($item);
      }
    }
    else
    {
      $lost = isset($this->config['_lost']) ? $this->config['_lost'] : '';

      $this->breadcrumbs = array(
        $this->buildBreadcrumb(
          array(
            'name' => $lost,
            'url'  => null
          ))
      );
    }


    if (isset($this->config['_root']))
    {
      array_unshift($this->breadcrumbs, $this->buildBreadcrumb($this->config['_root']));
    }

    $this->breadcrumbs = array_merge($this->breadcrumbs, IceBreadcrumbs::getInstance($this->getContext())->getItems());
  }

  protected function getCaseForItem($item)
  {
    $case = isset($item['case']) ? $item['case'] : null;

    if ($case == null)
    {
      $config = $this->getConfig();
      $case   = isset($config['_default_case']) ? $config['_default_case'] : null;
    }

    return $case;
  }

  protected function switchCase($name, $case)
  {
    switch ($case)
    {
      case 'ucfirst':
        $name = ucfirst(mb_strtolower($name, 'UTF-8'));
        break;

      case 'lcfirst':
        $name = lcfirst(mb_strtolower($name, 'UTF-8'));
        break;

      case 'strtolower':
        $name = mb_strtolower($name, 'UTF-8');
        break;

      case 'strtoupper':
        $name = mb_strtoupper($name, 'UTF-8');
        break;

      case 'ucwords':
        $name = ucwords(mb_strtolower($name, 'UTF-8'));
        break;
    }

    return $name;
  }

  /**
   * @return \sfContext
   */
  public function getContext()
  {
    return $this->context;
  }

}
