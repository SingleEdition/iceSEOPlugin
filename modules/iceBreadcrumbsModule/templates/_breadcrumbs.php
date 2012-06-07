<?php
/**
 * @var $breadcrumbs IceBreadcrumbsItem[]
 */
/** @var $breadcrumbsCount integer */
$breadcrumbsCount = count($breadcrumbs);

$index = 0;
foreach ($breadcrumbs as $key => $breadcrumb)
{
  if (($url = $breadcrumb->getUri()) && ++$index < $breadcrumbsCount)
  {
    echo link_to(
      cqStatic::reduceText($breadcrumb->getText(), 40, '[...]'),
      $url, array('title'=> $breadcrumb->getTitle())
    );
  }
  else
  {
    echo '<span title="', $breadcrumb->getText(), '">',
         cqStatic::reduceText($breadcrumb->getText(), 40, '[...]'),
         '</span>';
  }

  if ($key < $breadcrumbsCount - 1)
  {
    /** @var $separator string */
    echo $separator;
  }
}
