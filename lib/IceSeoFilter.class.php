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
   * @param $filterChain sfFilterChain
   */
  public function execute($filterChain)
  {
    if ($this->isFirstCall())
    {
      /** @var $context sfContext */
      $context = $this->getContext();

      /* @var $request sfWebRequest */
      $request = $context->getRequest();

      /* @var $response sfWebResponse */
      $response = $context->getResponse();

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
      $title = $meta_title = $description = '';
      $keywords = array();

      // Title
      if (!empty($seo[$module][$action]['title']))
      {
        $title = $seo[$module][$action]['title'];
        if (
          isset($seo[$module][$action]['model']) &&
          true === (boolean) $seo[$module][$action]
        ) {
          $object = $request->getAttribute('sf_route')->getObject();
          $title = preg_replace('/%count(\w+)%/e', '$object->count$1()', $title);
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
        if ($config['appendTitle'])
        {
          $title .= $config['separator'] . $config['appendTitle'];
        }
        // There is SEO title set
        $response->setTitle($title);
      }

      //Meta title
      if (!empty($seo[$module][$action]['meta_title']))
      {
        $meta_title = $seo[$module][$action]['meta_title'];
        if (
          isset($seo[$module][$action]['model']) &&
          true === (boolean) $seo[$module][$action]
        ) {
          $object = $request->getAttribute('sf_route')->getObject();
          $meta_title = preg_replace('/%count(\w+)%/e', '$object->count$1()', $meta_title);
          $meta_title = preg_replace('/%(\w+)%/e', '$object->get$1()', $meta_title);
        }
      }
      else if (!empty($seo[$module]['meta_title']))
      {
        $meta_title = !empty($seo[$module]['meta_title']) ? $seo[$module]['meta_title'] : '';
      }
      else
      {
        $meta_title = $title;
      }

      if ($meta_title)
      {
        if ($config['appendTitle'])
        {
          $meta_title .= $config['separator'] . $config['appendTitle'];
        }
        //There is SEO meta name="title" set
        $response->addMeta('title', $meta_title);
      }

      //Description
      if (!empty($seo[$module][$action]['description']))
      {
        $description = $seo[$module][$action]['description'];
        if (
          isset($seo[$module][$action]['model']) &&
          true === (boolean) $seo[$module][$action]
        ) {
          $object = $request->getAttribute('sf_route')->getObject();
          $description = preg_replace('/%count(\w+)%/e', '$object->count$1()', $description);
          $description = preg_replace('/%(\w+)%/e', '$object->get$1()', $description);
        }
      }
      else if (!empty($seo[$module]['description']))
      {
        $description = !empty($seo[$module]['description']) ? $seo[$module]['description'] : '';
      }
      else
      {
        $description = '';
      }

      if ($description)
      {
        // There is SEO description set
        $response->addMeta('description', $description);
      }

      // Keywords
      if (!empty($seo[$module][$action]['keywords']))
      {
        $keywords = $seo[$module][$action]['keywords'];
        if (
          isset($seo[$module][$action]['model']) &&
          true === (boolean) $seo[$module][$action]
        ) {
          $object = $request->getAttribute('sf_route')->getObject();
          $keywords = preg_replace('/%count(\w+)%/e', '$object->count$1()', $keywords);
          $keywords = preg_replace('/%(\w+)%/e', '$object->get$1()', $keywords);
        }
      }
      else if (!empty($seo[$module]['keywords']))
      {
        $keywords = !empty($seo[$module]['keywords']) ? $seo[$module]['keywords'] : '';
      }
      else
      {
        $keywords = '';
      }

      if ($keywords)
      {
        // There is SEO keywords set
        $response->addMeta('keywords', $keywords);
      }

      d($module, $action, $title, $description, $keywords);
    }

    $filterChain->execute();
  }

}
