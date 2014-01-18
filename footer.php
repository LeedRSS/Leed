<?php

$tpl->assign('executionTime',number_format(microtime(true)-$start,3));
$html = $tpl->draw($view);
?>