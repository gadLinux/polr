<?php
namespace App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Redirect;
use App\Helpers\Session;
use Piwik\PiwikTracker;
use App\Models\Link;

class PiwikHelper {
    static private function getCountry($ip) {
        $country_iso = geoip()->getLocation($ip)->iso_code;
        return $country_iso;
    }

    static private function getHost($url) {
        // Return host given URL; NULL if host is
        // not found.
        return parse_url($url, PHP_URL_HOST);
    }
    
    static private function setUniqueId(\PiwikTracker $piwikTracker) {
    	$piwikId = session('piwikId');
    	if($piwikId === null) {
    		// We set an unique Id
    		$piwikTracker->setNewVisitorId();
    		session(['piwikId'=> $piwikTracker->getVisitorId()]);
    	}else{
    		$piwikTracker->setUserId($piwikId);
    	}
    }
    

    static public function recordClick(Link $link, Request $request) {

    	
    	$ip = $request->ip();
    	$referer = $request->server('HTTP_REFERER');
    	
    	if(!(env('PIWIK_ANALYTICS_API_URL')===null) 
    		&& !(env('PIWIK_ANALYTICS_API_IDSITE')===null)
    		&& !(env('PIWIK_ANALYTICS_API_URL')===null)){
		    	/**
		         * Given a Link model instance and Request object, process post click operations.
		         * @param Link model instance $link
		         * @return boolean
		         */
    			$piwikTracker= new \PiwikTracker($idSite=env('PIWIK_ANALYTICS_API_IDSITE'), $apiUrl=env('PIWIK_ANALYTICS_API_URL'));
		    	
		    	self::setUniqueId($piwikTracker);
		    	// Specify an API token with at least Admin permission, so the Visitor IP address can be recorded
		    	// Learn more about token_auth: https://piwik.org/faq/general/faq_114/
		    	$piwikTracker->setTokenAuth(env('PIWIK_ANALYTICS_API_TOKEN'));
		
		    	$piwikTracker->setIp($ip);
		    	$piwikTracker->setUrlReferrer($referer);
		    	
		    	//$piwikTracker->setCountry(self::getCountry($ip));
		    	$piwikTracker->setUserAgent($request->server('HTTP_USER_AGENT'));
		    	
		    	
		    	// Sends Tracker request via http
		    	$piwikTracker->doTrackPageView($request->server('SCRIPT_URI'));
		    	
		    	// Sends tracker via http
		    	$piwikTracker->doTrackAction($link->long_url, 'link');
		
		        /*
		        $click = new Click;
		        $click->link_id = $link->id;
		        $click->ip = $ip;
		        $click->country = self::getCountry($ip);
		        $click->referer = $referer;
		        $click->referer_host = ClickHelper::getHost($referer);
		        $click->user_agent = $request->server('HTTP_USER_AGENT');
		        $click->save();
				*/
	        return true;
    		}else{
    			return false;
    		}
    }
}
