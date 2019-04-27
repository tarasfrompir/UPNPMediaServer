<?php

require_once(dirname(__FILE__)."/classes_for_addons/UPNPMediaServer.php");

// массивы для определения типов файлов
$video = [".3g2",".3gp",".3gp2",".3gpp",".3gpp2",".asf",".asx",".avi",".bin",".dat",".drv",".f4v",".flv",".gtp",".h264",".m4v",".mkv",".mod",".moov",".mov",".mp4",".mpeg",".mpg",".mts",".rm",".rmvb",".spl",".srt",".stl",".swf",".ts",".vcd",".vid",".vid",".vid",".vob",".webm",".wm",".wmv",".yuv"];
$audio = [".aac",".ac3",".aif",".aiff",".amr",".aob",".ape",".asf",".aud",".aud",".aud",".aud",".awb",".bin",".bwg",".cdr",".flac",".gpx",".ics",".iff",".m",".m3u",".m3u8",".m4a",".m4b",".m4p",".m4r",".mid",".midi",".mod",".mp3",".mp3",".mp3",".mpa",".mpp",".msc",".msv",".mts",".nkc",".ogg",".ps",".ra",".ram",".sdf",".sib",".sln",".spl",".srt",".srt",".temp",".vb",".wav",".wav",".wave",".wm",".wma",".wpd",".xsb",".xwb"];

$upnpaddress = $this->getProperty("UPNPADDRESS");
$upnpcontroll = $this->getProperty("UPNPCONTROLL");
$upnpmediaserver = new UPNPMediaserver($upnpaddress, $upnpcontroll);
//если нету контроладреса то вносим его
if (!$upnpcontroll){
	$this->setProperty('UPNPCONTROLL', $upnpmediaserver->searchupnpcontroll($upnpaddress));
}
// сканируем файлы
$directories = $upnpmediaserver->browse();
$count=0;
// очищаем данные файлов текущего сервера
//SQLExec("DELETE FROM mediaservers_playlist WHERE LINKED_OBJECT='".$this->description."'");
//SQLExec("TRUNCATE TABLE mediaservers_playlist");

foreach($directories as $list){
    $files = $upnpmediaserver->browsexmlfiles($list['id']);
    foreach($files as $file){
	$title = mysql_real_escape_string($file ['title']);
        //DebMes ($file ['link']);
        //DebMes ($file ['title']);
        //DebMes ($file ['genre']);
        //DebMes ($file ['creator']);
        $Record = SQLSelectOne("SELECT * FROM upnpmediaservers_playlist WHERE TITLE='".$title."'");
        $Record['URL_LINK'] = $file ['link'];
        $tcode = mb_detect_encoding($title);
        $Record['TITLE'] = iconv($tcode, "UTF-8", $file ['title']);
        $Record['DESCRIPTION'] = $file ['creator'];
        $ext_file = substr(strrchr($file ['link'], "."),0);
        if (in_array($ext_file, $video)) {
            $Record['GENRE'] = 'Видео';
        }else if (in_array($ext_file, $audio)) {
            $Record['GENRE'] = 'Аудио';
        }else {
            $Record['GENRE'] = 'Изображения';
        };
        $Record['LINKED_OBJECT'] = $this->description;
        SQLUpdateInsert('upnpmediaservers_playlist', $Record);
        $count = $count+1;
        
    }
   }
$this->setProperty("havedfiles",$count);
echo ('получено файлов - '.$count);
