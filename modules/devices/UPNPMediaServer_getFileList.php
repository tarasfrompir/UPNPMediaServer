<?php

require_once(dirname(__FILE__)."/classes_for_addons/UPNPMediaServer.php");

// массивы для определения типов файлов
$video = [".3g2",".3gp",".3gp2",".3gpp",".3gpp2",".asf",".asx",".avi",".bin",".dat",".drv",".f4v",".flv",".gtp",".h264",".m4v",".mkv",".mod",".moov",".mov",".mp4",".mpeg",".mpg",".mts",".rm",".rmvb",".spl",".srt",".stl",".swf",".ts",".vcd",".vid",".vid",".vid",".vob",".webm",".wm",".wmv",".yuv", ".wtv"];
$audio = [".aac",".ac3",".aif",".aiff",".amr",".aob",".ape",".asf",".aud",".aud",".aud",".aud",".awb",".bin",".bwg",".cdr",".flac",".gpx",".ics",".iff",".m",".m3u",".m3u8",".m4a",".m4b",".m4p",".m4r",".mid",".midi",".mod",".mp3",".mp3",".mp3",".mpa",".mpp",".msc",".msv",".mts",".nkc",".ogg",".ps",".ra",".ram",".sdf",".sib",".sln",".spl",".srt",".srt",".temp",".vb",".wav",".wav",".wave",".wm",".wma",".wpd",".xsb",".xwb"];

$upnpaddress = $this->getProperty("UPNPADDRESS");
$upnpcontroll = $this->getProperty("UPNP_CONTROL_ADDRESS");
$upnpmediaserver = new UPNPMediaserver($upnpaddress, $upnpcontroll);
//если нету контроладреса то вносим его
if (!$upnpcontroll){
	$this->setProperty('UPNP_CONTROL_ADDRESS', $upnpmediaserver->searchupnpcontroll($upnpaddress));
}
// сканируем файлы
$directories = $upnpmediaserver->browse();
$count=0;
// очищаем данные файлов текущего сервера
SQLExec("DELETE FROM mediaservers_playlist WHERE LINKED_OBJECT='".$this->description."'");
//SQLExec("TRUNCATE TABLE mediaservers_playlist");

foreach($directories as $list){
    $files = $upnpmediaserver->browsexmlfiles($list['id']);
    foreach($files as $file){
	    //DebMes ($file ['title']);
        //DebMes ($file ['title']);
        //DebMes ($file ['genre']);
        //DebMes ($file ['creator']);
        $tcode = mb_detect_encoding($file ['title']);
        //DebMes($tcode);
        if  ($file ['title'] ) {
            if ($tcode != 'UTF-8') {
                $file ['title'] = iconv($tcode, "UTF-8", $file ['title']);
            }
    	} else {
	        $file ['title'] = $file ['creator'];
	    }
        $Record = SQLSelectOne("SELECT * FROM mediaservers_playlist WHERE TITLE='".$file ['title']."'");
        $Record['TITLE'] = $file ['title'];
        $Record['URL_LINK'] = $file ['link'];
        $Record['DESCRIPTION'] = $file ['creator'];
	if ($file ['genre']) {
	    $Record['GENRE'] = $file ['genre'];
	} else {
            $Record['GENRE'] = 'None';
	}
        $ext_file = substr(strrchr($file ['link'], "."),0);
        if (in_array($ext_file, $video)) {
            $Record['TYPE'] = 'Видео';
        }else if (in_array($ext_file, $audio)) {
            $Record['TYPE'] = 'Аудио';
        }else {
            $Record['TYPE'] = 'Изображение';
        };
        $Record['LINKED_OBJECT'] = $this->description;
        SQLUpdateInsert('mediaservers_playlist', $Record);
        $count = $count+1;
        
    }
   }
$this->setProperty("havedfiles",$count);
echo ('получено файлов - '.$count);
