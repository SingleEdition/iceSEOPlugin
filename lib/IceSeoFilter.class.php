<?php
/**
 * Copyright 2012 Collectors' Quest, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * Filename: IceSeoFilter.class.php
 *
 * Put some description here
 *
 * @author Yanko Simeonoff <ysimeonoff@collectorsquest.com>
 * @since 5/8/12
 * Id: $Id$
 *
 * @todo Add tests
 */

class IceSeoFilter extends sfFilter
{

  /**
   * @param $chain sfFilterChain
   */
  public function execute($chain)
  {
    if ($this->isFirstCall())
    {
      $context = $this->getContext();
      /* @var $request sfWebRequest */
      $request = $context->getRequest();
      /* @var $response sfWebResponse */
      $response = $context->getResponse();
      $uri   = $request->getPathInfo();
      $route = $context->getRouting()->getCurrentInternalUri(true);
      $routeExpanded = $context->getRouting()->parse($context->getRouting()->getCurrentInternalUri(false));
      $module = $context->getModuleName();
      $action = $context->getActionName();
      $config = array('appendTitle'=>'', 'separator'=>' :: ');

      if ($file = $context->getConfigCache()->checkConfig('config/seo.yml', true))
      {
        sfConfig::add(include($file));
      }

      $seo = sfConfig::get('seo');

      if (!empty($seo['_config']))
      {
        $config = array_merge($config, $seo['_config']);
      }
      $appendTitle = !empty($config['appendTitle']) ? $config['appendTitle'] : '';
      $title = $description = '';
      $keywords = array();

      //Title
      if (!empty($seo[$module][$action]['title']))
      {
        $title = $seo[$module][$action]['title'];
        if (isset($seo[$module][$action]['model']) && true === (boolean)$seo[$module][$action])
        {
          $object = $request->getAttribute('sf_route')->getObject();
          $title = preg_replace('/%(\w+)%/e', '$object->get$1()', $title);
        }
      }
      else if (!empty($seo[$module]['title']))
      {
        $title = !empty($seo[$module]['title']) ? $seo[$module]['title'] : '';
      }
      else
      {
        $title = '';
      }

      if ($title)
      {
        if ($appendTitle)
        {
          $title .= $config['separator'] . $appendTitle;
        }
        //There is SEO title set
        $response->addMeta('title', $title);
      }
    }

    $chain->execute();
  }

}
