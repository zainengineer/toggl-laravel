<?php
require_once  __DIR__ . '/kint.php';

\Kint::$file_link_format = 'http://localhost:8091/?message=%f:%l';
\Kint::$max_depth =4;
/**
 *

 to put it raw in any place

 require_once '/var/www/magento/pub/zain_custom/lib/kint_inc.php';
\Kint::$max_depth =3;
!d($saveOrder);
die;

 */