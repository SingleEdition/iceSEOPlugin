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

      // Is it a Model Object route?
      if (isset($seo[$module][$action]['model'])) {
        $object = $request->getAttribute('sf_route')->getObject();
      } else {
        $object = null;
      }

      // Initialize some of the variables
      $title = $meta_title = $description = null;
      $keywords = array();

      // Title
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

          $i = sizeof($title)-1; //used to indicate the last iteration
          foreach ($title as $new_title)
          {
            if (strlen($new_title) <= 70 || $i==0)
            {
              //if it is the last possible option - strip title
              $title = substr($new_title, 0, 69);
              break;
            }
            $i--;
          }
        }
      }
      else if (!empty($seo[$module]['title']))
      {
        $title = !empty($seo[$module]['title']) ? $seo[$module]['title'] : '';
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

        if ($object && true === (boolean) $seo[$module][$action])
        {
          $description = preg_replace('/%count(\w+)%/e', '$object->count$1()', $description);
          $description = preg_replace('/%(\w+)%/e', '$object->get$1()', $description);
        }

        if (is_array($description))
        {
          $description = array_reverse($description);

          $i = sizeof($description)-1; //used to indicate the last iteration
          foreach ($description as $new_description)
          {
            if ($object instanceof Collector)
            {
              // if user has no about text we want to display default info
              $about_me = $object->getProfileAboutMe();
              if ($about_me=='')
              {
                //assign default value to new description
                $new_description = $description[sizeof($description)-1];
                //remove HTML tags
                $new_description = strip_tags($new_description);
                //if it is the last possible option - strip description
                $description = substr($new_description, 0, 155);
                break;
              }

              // if user has a few collectibles we don't want to display them
              $collectibles = $object->countCollectiblesInCollections();
              if (strpos ($new_description, '' . $collectibles) && $collectibles < 25)
              {
                $i--;
                continue;
              }
            }

            if (strlen($new_description) <= 156 || $i==0)
            {
              //remove HTML tags
              $new_description = strip_tags($new_description);
              //if it is the last possible option - strip description
              $description = substr($new_description, 0, 155);
              break;
            }
            $i--;
          }
        }
      }
      else if (!empty($seo[$module]['description']))
      {
        $description = !empty($seo[$module]['description']) ? $seo[$module]['description'] : '';
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
        if ($object && true === (boolean) $seo[$module][$action])
        {
          $keywords = preg_replace('/%count(\w+)%/e', '$object->count$1()', $keywords);
          $keywords = preg_replace('/%(\w+)%/e', '$object->get$1()', $keywords);
        }
      }
      else if (!empty($seo[$module]['keywords']))
      {
        $keywords = !empty($seo[$module]['keywords']) ? $seo[$module]['keywords'] : '';
      }

      if ($keywords)
      {
        // There is SEO keywords set
        $response->addMeta('keywords', $keywords);
      }
    }

    $filterChain->execute();
  }

}
