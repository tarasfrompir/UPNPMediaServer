<?php
$dictionary = array(
 'UPNPMediaServer_MODULE_NAME' => 'Простий Пристрій - UPNP Медіа Сервер',
);
foreach ($dictionary as $k => $v) {
 if (!defined('LANG_' . $k)) {
  define('LANG_' . $k, $v);
 }
}
