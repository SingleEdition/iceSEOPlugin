<?php

/** @var $breadcrumbs array */
$breadcrumbsCount = count($breadcrumbs);
foreach ($breadcrumbs as $key => $breadcrumb)
{
  if (!is_null($breadcrumb['url']))
  {
    echo link_to($breadcrumb['name'], $breadcrumb['url'], array('title'=> $breadcrumb['title']));
  }
  else
  {
    echo $breadcrumb['name'];
  }

  if ($key < $breadcrumbsCount - 1)
  {
    /** @var $separator string */
    echo $separator;
  }
}
