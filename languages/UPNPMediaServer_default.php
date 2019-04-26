<?php
$dictionary = array(
 'UPNPMediaServer_MODULE_NAME' => 'Semple Device UPNP Media Server',
);
foreach ($dictionary as $k => $v) {
 if (!defined('LANG_' . $k)) {
  define('LANG_' . $k, $v);
 }
}
