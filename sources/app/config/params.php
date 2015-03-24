<?php

$sn = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "localhost" );
$container->setParameter('SERVER_NAME', $sn);