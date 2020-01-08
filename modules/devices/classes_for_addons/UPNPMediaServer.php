<?php

class UPNPMediaserver
{
    public function __construct($upnpaddress,$upnpcontroll) {
        $this->upnpaddress = $upnpaddress;
        $this->upnpcontroll  = $this->upnpcontroll;
        if (!$this->upnpcontroll) {
	    $this->searchupnpcontroll($this->upnpaddress);
		}
		$this->baseUrl = $this->baseFormUrl($this->upnpcontroll);
			
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->upnpcontroll);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			libxml_use_internal_errors(true); 
			$xml = simplexml_load_string($content);
			curl_close($ch);

		if (!$xml) {
			$this->searchupnpcontroll($this->upnpaddress);
		}

        foreach($xml->device->serviceList->service as $service){
            if($service->serviceId == 'urn:upnp-org:serviceId:ContentDirectory'){
                $this->ctrlurl = ($this->baseUrl.$service->controlURL);
		$this->service_type =''.$service->serviceType;
            }
        }
    }

    //BrowseDirectChildren or BrowseMetadata
    public function browse($base = '0', $browseflag = 'BrowseDirectChildren', $start = 0, $count = 0)   {
        $alldirectories = $this->browsexml($base, $browseflag, $start, $count);
        $allfiles = array();
        foreach ( $alldirectories as $dirname ) {
            // получили список директорий
            $response = $this->browsexml($base = $dirname['id'], $browseflag = 'BrowseDirectChildren', $start = 0, $count = 0);
            if ($response){
                $alldirectories = array_merge($alldirectories, $response);
            }
        }
	//DebMes($alldirectories);
	return $alldirectories;
        
    }
 
   //запрос на получение хмл файла от устройства списком папок с их ИД
    public function browsexml($base = '0', $browseflag = 'BrowseDirectChildren', $start = 0, $count = 0) {
        libxml_use_internal_errors(true); //is this still needed?
        $args = array('ObjectID'=>$base, 'BrowseFlag'=>$browseflag, 'Filter'=>'*', 'StartingIndex'=>$start, 'RequestedCount'=>$count, 'SortCriteria'=>'');
        $response = $this->sendRequestToDevice('Browse', $args, $type = 'ContentDirectory');
		//DebMes($response);
        if($response){
            $doc = new \DOMDocument();
            $doc->loadXML($response);
            $result = $doc->getElementsByTagName('Result')[0]->nodeValue;
            $doc->loadXML($result);
	        //$doc->save("test.xml");
            $containers = $doc->getElementsByTagName('container');
            $directories = array();
            foreach($containers as $container){
                foreach($container->attributes as $attr){
					if($attr->name == 'id'){
                        $id = $attr->nodeValue;
                    }
                    $directories[$id]['id'] = $id;
                }
			}
            return $directories;
        }
        return false;
    }

 //запрос на получение хмл файла от устройства списком папок с их ИД
    public function browsexmlfiles($id = '0') {
        libxml_use_internal_errors(true); //is this still needed?
        $args = array( 'ObjectID'=>$id, 'BrowseFlag'=>'BrowseDirectChildren', 'Filter'=>'*', 'StartingIndex'=>'0', 'RequestedCount'=>'0', 'SortCriteria'=>'');
        $response = $this->sendRequestToDevice('Browse', $args);
        $doc = new \DOMDocument();
        $doc->loadXML($response);
        $result = $doc->getElementsByTagName('Result')[0]->nodeValue;
        $doc->loadXML($result);
        //$doc->save("test".$id.".xml");
        $files = array();
        $items = $doc->getElementsByTagName('item');
        foreach($items as $i=>$item){
            $link=$item->getElementsByTagName( "res" );
            $link = $link->item(0)->nodeValue;
            $title=$item->getElementsByTagName( "title" );
            $title = $title->item(0)->nodeValue;   
            $creator=$item->getElementsByTagName( "creator" );
            $creator = $creator->item(0)->nodeValue;
            $genre=$item->getElementsByTagName( "genre" );
            $genre = $genre->item(0)->nodeValue;      
            $files[$i]['genre'] = $genre;
            $files[$i]['link'] = $link;
            $files[$i]['title'] = $title;
            $files[$i]['creator'] = $creator;
        }
        return $files;
    }
private function sendRequestToDevice ($command, $arguments) {
        $body = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>'."\r\n";
        $body.= '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">';
        $body.= '<s:Body>';
        $body.= '<u:' . $command . ' xmlns:u="' . $this->service_type . '">';
        foreach($arguments as $arg => $value) {
            $body.= '<' . $arg . '>' . $value . '</' . $arg . '>';
        }

        $body.= '</u:' . $command . '>';
        $body.= '</s:Body>';
        $body.= '</s:Envelope>';
        $header = array(
            'Host: 127.0.0.1:80',
            'User-Agent: Majordomo/ver-x.x UDAP/2.0 Win/7',
            'Content-Length: ' . strlen($body) ,
            'Connection: close',
            'Content-Type: text/xml; charset="utf-8"',
            'SOAPAction: "' . $this->service_type . '#' . $command . '"',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ctrlurl );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST,           true );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
        $response = curl_exec($ch);
        curl_close($ch);;
        return $response;
    }
    // функция получения CONTROL_ADDRESS при его отсутствии или его ге правильности
    public function searchupnpcontroll($ip = '255.255.255.255') {
		if ($this->upnpcontroll) {
			return $this->upnpcontroll;
		}
        //create the socket
        $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, true);
        //search ver1
        $request  = 'M-SEARCH * HTTP/1.1'."\r\n";
        $request .= 'HOST: 239.255.255.250:1900'."\r\n";
        $request .= 'MAN: "ssdp:discover"'."\r\n";
        $request .= 'MX: 2'."\r\n";
        $request .= 'ST: urn:schemas-upnp-org:service:ContentDirectory:1'."\r\n";
        $request .= 'USER-AGENT: Majordomo/ver-x.x UDAP/2.0 Win/7'."\r\n";
        $request .= "\r\n";
        
        socket_sendto($socket, $request, strlen($request), 0, $ip, 1900);
		//search ver2
        $request  = 'M-SEARCH * HTTP/1.1'."\r\n";
        $request .= 'HOST: 239.255.255.250:1900'."\r\n";
        $request .= 'MAN: "ssdp:discover"'."\r\n";
        $request .= 'MX: 2'."\r\n";
        $request .= 'ST: urn:schemas-upnp-org:service:ContentDirectory:2'."\r\n";
        $request .= 'USER-AGENT: Majordomo/ver-x.x UDAP/2.0 Win/7'."\r\n";
        $request .= "\r\n";
        
        socket_sendto($socket, $request, strlen($request), 0, $ip, 1900);
        // send the data from socket
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>'5', 'usec'=>'0'));
        $response = array();
        do {
            $buf = null;
            if (($len = @socket_recvfrom($socket, $buf, 2048, 0, $ip, $port)) == -1) {
                echo "socket_read() failed: " . socket_strerror(socket_last_error()) . "\n";
            }
            if(!is_null($buf)){
                $messages = explode("\r\n", $buf);
                    foreach( $messages as $row ) {
                         if( stripos( $row, 'loca') === 0 ) {
                              $response = str_ireplace( 'location: ', '', $row );
                         }
                    }
            }
        } while(!is_null($buf));
        socket_close($socket);
        $this->upnpcontroll = str_ireplace("Location:", "", $response);
	//DebMes ($this->upnpcontroll);
	return $this->upnpcontroll;
    } 
	// получает айпи адрес с портом или без 
    private function baseFormUrl($url)
    {
        $url = parse_url($url);
        return $url['scheme'].'://'.$url['host'].':'.$url['port'];
    }
}
