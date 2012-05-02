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
 * Filename: BaseiceBreadcrumbsModuleComponents.class.php
 *
 * @package Collectorsquest
 * @subpackage plugin
 * @author Yanko Simeonoff <ysimeonoff@collectorsquest.com>
 * @since 5/2/12
 * Id: $Id$
 */
class BaseiceBreadcrumbsModuleComponents extends sfComponents
{

  public function executeBreadcrumbs()
  {
    $module = $this->getContext()->getModuleName();
    $action = $this->getContext()->getActionName();

    $ice_propel_breadcrumbs = new IcePropelBreadcrumbs($module, $action);
    $this->breadcrumbs      = $ice_propel_breadcrumbs->getBreadcrumbs();
    $this->separator        = $ice_propel_breadcrumbs->getSeparator();
  }
}
