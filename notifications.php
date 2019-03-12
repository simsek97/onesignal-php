<?php

class notifications {
    
    private $appId = "";
    private $apiKey = "";
    private $createNotificationUrl = "";
    private $headings = array();
    private $subtitle = array();
    private $contents = array();
    private $data = array();
    private $url = "";
    private $playerIds = array();
    private $oneSignalConfig = array();
    private $configDbTable = _ONESIGNAL_CONFIG_;

	private function getConfig()
	{
        global $dbi, $ySubeKodu;
            
		$oneSignalConfig = $dbi->where("schoolId", array(0, $ySubeKodu), "IN")->orderBy("schoolId", "desc")->getOne($this->configDbTable);
        return $oneSignalConfig;
	}

	private function convertLanguage()
	{
		global $currentlang;
		
        switch ($currentlang)
        {
			case "turkish": 
				return "tr"; 
				break;
				
			case "arabic": 
				return "ar"; 
				break;
				
			case "chinese": 
				return "zh-Hans"; 
				break;
				
			case "french": 
				return "fr"; 
				break;
				
			case "german": 
				return "de"; 
				break;
				
			case "russian": 
				return "ru"; 
				break;
				
			case "spanish": 
				return "es"; 
				break;
				
			case "english": 
			default: 
				return "en"; 
				break;
        }            
	}

	function setDbConfigTable($table)
	{
		$this->configDbTable = $table;
	}

	function setHeadings($headings)
	{
		$oneSignalLang = $this->convertLanguage();
		
		$this->headings[$oneSignalLang] = $headings;
		if($oneSignalLang != "en") $this->headings["en"] = $headings;
	}

	function setSubtitle($subtitle)
	{
		$oneSignalLang = $this->convertLanguage();
		
        $this->subtitle[$oneSignalLang] = $subtitle;
		if($oneSignalLang != "en") $this->subtitle["en"] = $subtitle;
	}

	function setContents($content)
	{
		$oneSignalLang = $this->convertLanguage();
        
        $this->contents[$oneSignalLang] = $content;
		if($oneSignalLang != "en") $this->contents["en"] = $content;
	}

	function setData($data)
	{
        $this->data = $data;
	}

	function setUrl($url)
	{
        $this->url = $url;
	}

	function setPlayerIds($playerIds)
	{
		$this->playerIds = $playerIds;
	}
	
	function createNotification()
    {
        global $dbi, $ySubeKodu, $SmartClassLogo, $SmartClassFavicon, $site_logo, $site_favicon, $arraySubeler;
            
		$this->oneSignalConfig = $this->getConfig();
        
        $this->appId = $this->oneSignalConfig["oneSignalAppId"];
        $this->apiKey = $this->oneSignalConfig["oneSignalApiKey"];
        $this->createNotificationUrl = $this->oneSignalConfig["oneSignalApiUrl"];

		$chrome_web_icon = 
		$restData = array(
			"app_id"				=> $this->appId,
			"include_player_ids"	=> $this->playerIds,
			"contents"				=> $this->contents,
			"ios_badgeType"			=> "Increase",
			"ios_badgeCount"		=> "1",
			"chrome_web_icon"		=> !empty($ySubeKodu) ? $arraySubeler[$ySubeKodu]["favicon"] : (!empty($site_favicon) ? $site_favicon : $SmartClassFavicon),
			"chrome_web_image"		=> !empty($ySubeKodu) ? $arraySubeler[$ySubeKodu]["kucukLogo"] : (!empty($site_logo) ? $site_favicon : $SmartClassLogo),
		);

		//add title
		if(sizeof($this->headings) > 0) $restData["headings"] = $this->headings;
		
		//add subtitle
		if(sizeof($this->subtitle) > 0) $restData["subtitle"] = $this->subtitle;
		
		//add data
		if(sizeof($this->data) > 0) $restData["data"] = $this->data;

		//add url
		if(!empty($this->url)) $restData["url"] = $this->url;

		//convert to json
		$restData = json_encode($restData);
		
		//send data	
		$createNotification = $this->postViaCurl($this->createNotificationUrl, $restData, $this->apiKey);

		//get result
		$result = json_decode($createNotification);
		
		return $result;
	}
	
	private function postViaCurl($url, $data, $apiKey)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Authorization: Basic ' . $apiKey));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		$result = curl_exec($curl);
		curl_close ($curl);
		
		return $result;
	}
}

?>
