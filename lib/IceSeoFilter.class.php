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
 * @author Yanko Simeonoff <ysimeonoff@collectorsquest.com>
 * @since 5/8/12
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
      /* @var $context sfContext */
      $context = $this->getContext();

      /* @var $request sfWebRequest */
      $request = $context->getRequest();

      /* @var $response sfWebResponse */
      $response = $context->getResponse();

      $module = $context->getModuleName();
      $action = $context->getActionName();
      $config = array('appendTitle' => '', 'separator' => ' :: ');

      if ($file = $context->getConfigCache()->checkConfig('config/seo.yml', true))
      {
        sfConfig::add(include($file));
      }

      $seo = sfConfig::get('seo');

      if (!empty($seo['_config']))
      {
        $config = array_merge($config, $seo['_config']);
      }

      /* @var $cache sfCache */
      $cache = cqContext::getInstance()->getViewCacheManager()
        ? cqContext::getInstance()->getViewCacheManager()->getCache()
        : new sfNoCache();

      // Initialize the cache key
      $cache_key = null;

      // Is it a Model Object route?
      if (isset($seo[$module][$action]['model']))
      {
        /** @var $object Collectible */
        $object = method_exists($request->getAttribute('sf_route'), 'getObject')
          ? $request->getAttribute('sf_route')->getObject() : null;
        if (is_object($object))
        {
          $cache_key = sprintf('/objects/%s/%s/seo/', get_class($object), $object->getId());
          if (method_exists($object, 'getCreatedAt') && (time() - strtotime($object->getCreatedAt())) < 86400)
          {
            $cache_key = false;
          }
        }
      }
      else
      {
        $object = null;
      }

      // Initialize some of the variables
      $title = $meta_title = $meta_description = $meta_keywords = array();

      // Title
      if (!$cache_key || !($title = $cache->get($cache_key . 'title')))
      {
        if (!empty($seo[$module][$action]['title']))
        {
          $title = $seo[$module][$action]['title'];

          if ($object && true === (boolean) $seo[$module][$action])
          {
            $title = preg_replace('/%count(\w+)%/e', '$object->count$1()', $title);
            $title = preg_replace('/%(\w+)%/e', '$object->get$1()', $title);
          }

          if (is_array($title))
          {
            $title = array_reverse($title);

            $i = count($title); // indicates the last iteration
            foreach ($title as $new_title)
            {
              if (($new_title && strlen($new_title) <= 70) || --$i == 0)
              {
                //if it is the last possible option - strip title
                $title = substr($new_title, 0, 69);
                break;
              }
            }
          }
        }
        else if (!empty($seo[$module]['title']))
        {
          $title = !empty($seo[$module]['title']) ? $seo[$module]['title'] : '';
        }

        /**
         * Cache the page Title
         */
        if ($cache_key && !empty($title))
        {
          $cache->set($cache_key . 'title', $title);
        }
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

      // Meta title
      if (!$cache_key || !($meta_title = $cache->get($cache_key . 'meta_title')))
      {
        if (!empty($seo[$module][$action]['meta_title']))
        {
          $meta_title = $seo[$module][$action]['meta_title'];
          if ($object && true === (boolean) $seo[$module][$action])
          {
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

        /**
         * Cache the Meta Title
         */
        if ($cache_key)
        {
          $cache->set($cache_key . 'meta_title', $meta_title, 86400);
        }
      }

      if ($meta_title)
      {
        if ($config['appendTitle'])
        {
          $meta_title .= $config['separator'] . $config['appendTitle'];
        }

        // There is SEO meta name="title" set
        $response->addMeta('title', $meta_title);
      }

      // Description
      if (!$cache_key || !($meta_description = $cache->get($cache_key . 'meta_description')))
      {
        if (!empty($seo[$module][$action]['description']))
        {
          $meta_description = $seo[$module][$action]['description'];

          if ($object && true === (boolean) $seo[$module][$action])
          {
            $meta_description = preg_replace('/%count(\w+)%/e', '$object->count$1()', $meta_description);
            $meta_description = preg_replace('/%(\w+)%/e', '$object->get$1()', $meta_description);
          }

          if (is_array($meta_description))
          {
            $meta_description = array_reverse($meta_description);

            $i = count($meta_description); // indicates the last iteration
            foreach ($meta_description as $new_description)
            {
              // Remove HTML tags
              $new_description = strip_tags($new_description);

              if (($new_description && strlen($new_description) <= 156) || --$i == 0)
              {
                // If it is the last possible option - strip description
                $meta_description = IceStatic::truncateText($new_description, 156, '...', true);
                break;
              }
            }
          }
        }
        else if (!empty($seo[$module]['description']))
        {
          $meta_description = !empty($seo[$module]['description']) ? $seo[$module]['description'] : '';
        }

        if ($cache_key && !empty($meta_description))
        {
          $cache->set($cache_key . 'meta_description', $meta_description, 86400);
        }
      }


      if (!empty($meta_description))
      {
        // There is SEO description set
        $response->addMeta('description', $meta_description);
      }

      // Keywords
      if (!$cache_key || !($meta_keywords = $cache->get($cache_key . 'meta_keywords')))
      {
        if (!empty($seo[$module][$action]['keywords']))
        {
          $meta_keywords = $seo[$module][$action]['keywords'];
          if ($object && true === (boolean) $seo[$module][$action])
          {
            $meta_keywords = preg_replace('/%count(\w+)%/e', '$object->count$1()', $meta_keywords);
            $meta_keywords = preg_replace('/%(\w+)%/e', '$object->get$1()', $meta_keywords);
          }
        }
        else if (!empty($seo[$module]['keywords']))
        {
          $meta_keywords = !empty($seo[$module]['keywords']) ? $seo[$module]['keywords'] : '';
        }

        /**
         * Cache the Meta Keywords
         */
        if ($cache_key && !empty($meta_keywords))
        {
          $cache->set($cache_key . 'meta_keywords', $meta_keywords, 86400);
        }
      }

      if (!empty($meta_keywords))
      {
        // There is SEO keywords set
        $response->addMeta('keywords', $meta_keywords);
      }
    }

    $filterChain->execute();
  }

}
