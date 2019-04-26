<?php
 $this->device_types['UPNPMediaServer'] = array(
        'TITLE'=>'UPNP Медиасервер',
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'UPNPMediaServer',
        'PROPERTIES'=>array(
            'groupEco'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_ECO,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupEco'),
            'groupEcoOn'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_ECO_ON,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupEcoOn'),
            'groupSunrise'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_SUNRISE,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupSunrise'),
            'groupSunset'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_SUNSET,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupSunset'),
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIsActivity'),
            //my adds
            'UPNPADDRESS'=>array('DESCRIPTION'=>'IP адрес UPNP устройства', '_CONFIG_TYPE'=>'text', 'KEEP_HISTORY'=>0, 'DATA_KEY'=>1),
            'UPNPCONTROLL'=>array('DESCRIPTION'=>'Адрес управления UPNP устройством', 'KEEP_HISTORY'=>0, 'DATA_KEY'=>1),
			'getFileList'=>array('DESCRIPTION'=>'При изменении Получает список файлов на устройстве', 'KEEP_HISTORY'=>1, 'ONCHANGE'=>'getFileList', 'DATA_KEY'=>1),
       ),
        'METHODS'=>array(
            'getFileList'=>array('DESCRIPTION'=>'Получает список файлов на устройстве'),

        )
);
