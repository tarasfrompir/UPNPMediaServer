<?php

class UPNPMediaServer extends module {
/**
* SUPNPMediaServer
*
* Module class constructor
*
* @access private
*/
  
function __construct() {
  $this->name="UPNPMediaServer";
  @include_once(ROOT . 'languages/' . $this->name . '_' . SETTINGS_SITE_LANGUAGE . '.php');
  $this->title=LANG_UPNPMediaServer_MODULE_NAME;
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
  $rec = SQLSelectOne("SELECT * FROM project_modules WHERE NAME = '" . $this->name . "'");
  $rec['HIDDEN'] = 1;
  SQLUpdate('project_modules', $rec);

  // запускаем цикл
  //setGlobal('cycle_upnpeventsControl','start'); //- запуск
  //setGlobal('cycle_pingControl','stop'); - Остановка
  //setGlobal('cycle_pingControl','start'); - запуск
  //setGlobal('cycle_pingControl','restart'); - рестарт
  //setGlobal('cycle_pingDisabled','1'); - Для запрета автозапуска (по-умолчанию он всегда разрешён)
  //setGlobal('cycle_pingAutoRestart','1'); - Для включения авто-восстановления (по-умолчанию он всегда выключен)
 }
/**
* Uninstall
*
* Module uninstall routine
*
*/
 function uninstall() {
  //setGlobal('cycle_upnpeventsControl','stop'); //- остановка цикла
  // дожидаемся остановки цикла
  //sleep (2);
  // удаляем файлы модуля-дополнения
  if ($file = fopen(DIR_MODULES.'/UPNPMediaServer/file_list.txt', "r")) {
    while(!feof($file)) {
        $line = preg_replace('/\p{Cc}+/u', '', fgets($file));
        @unlink(realpath(ROOT.$line));
        DebMes (ROOT.$line);
    }
    fclose($file);
  }
  // удаляем методы и класс устройства
   $rec = SQLSelectOne("SELECT * FROM classes WHERE TITLE = '" . $this->name . "'");
   if ($rec['ID']) {
     SQLExec("DELETE FROM methods WHERE CLASS_ID='".$rec['ID']."'");
     SQLExec("DELETE FROM classes WHERE TITLE='".$this->name . "'");
   }
    // delete all tables 
  SQLExec('DROP TABLE IF EXISTS upnpmediaservers_playlist');

  parent::uninstall();
 }
  /**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
 upnpmediaservers_playlist: ID int(255) unsigned NOT NULL auto_increment
 upnpmediaservers_playlist: TITLE varchar(100) NOT NULL DEFAULT ''
 upnpmediaservers_playlist: DESCRIPTION varchar(300) NOT NULL DEFAULT ''
 upnpmediaservers_playlist: GENRE varchar(50) NOT NULL DEFAULT ''
 upnpmediaservers_playlist: URL_LINK varchar(250) NOT NULL DEFAULT ''
 upnpmediaservers_playlist: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 mediaservers_playlist: FAVORITE int(3) unsigned NOT NULL DEFAULT 0 
 
EOD;
  parent::dbInstall($data);
 }
}
