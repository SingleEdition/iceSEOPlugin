<?php
/**
 * @var $breadcrumbs IceBreadcrumbsItem[]
 */
/** @var $breadcrumbsCount integer */
$breadcrumbsCount = count($breadcrumbs);

foreach ($breadcrumbs as $key => $breadcrumb)
{
  if ($url = $breadcrumb->getUri())
  {
    echo link_to($breadcrumb->getText(), $url, array('title'=> $breadcrumb->getTitle()));
  }
  else
  {
    echo $breadcrumb->getText();
  }

  if ($key < $breadcrumbsCount - 1)
  {
    /** @var $separator string */
    echo $separator;
  }
}
