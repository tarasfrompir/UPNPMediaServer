<?php
$dictionary = array(
 'UPNPMediaServer_MODULE_NAME' => 'Простое устройство UPNP Медиа сервер',
);
foreach ($dictionary as $k => $v) {
 if (!defined('LANG_' . $k)) {
  define('LANG_' . $k, $v);
 }
}
