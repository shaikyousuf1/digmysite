<?php
$domain = $_GET['domain'];
$w      = new DomainAge();
echo $w->age($domain);

class DomainAge
{
    private $WHOIS_SERVERS = array(
        "com" => array("whois.verisign-grs.com", "/Creation Date:(.*)/"), 
        "net" => array("whois.verisign-grs.com", "/Creation Date:(.*)/"), 
        "org" => array("whois.pir.org", "/Creation Date:(.*)/"), 
        "info" => array("whois.afilias.info", "/Created On:(.*)/"), 
        "biz" => array("whois.neulevel.biz", "/Domain Registration Date:(.*)/"), 
        "us" => array("whois.nic.us", "/Domain Registration Date:(.*)/"), 
        "uk" => array("whois.nic.uk", "/Registered on:(.*)/"), 
        "ca" => array("whois.cira.ca", "/Creation date:(.*)/"), 
        "tel" => array("whois.nic.tel", "/Domain Registration Date:(.*)/"), 
        "ie" => array("whois.iedr.ie", "/registration:(.*)/"), 
        "it" => array("whois.nic.it", "/Created:(.*)/"), 
        "cc" => array("whois.nic.cc", "/Creation Date:(.*)/"), 
        "ws" => array("whois.nic.ws", "/Domain Created:(.*)/"), 
        "sc" => array("whois2.afilias-grs.net", "/Created On:(.*)/"), 
        "mobi" => array("whois.dotmobiregistry.net", "/Created On:(.*)/"), 
        "pro" => array("whois.registrypro.pro", "/Created On:(.*)/"), 
        "edu" => array("whois.educause.net", "/Domain record activated:(.*)/"), 
        "tv" => array("whois.nic.tv", "/Creation Date:(.*)/"), 
        "travel" => array("whois.nic.travel", "/Domain Registration Date:(.*)/"), 
        "in" => array("whois.inregistry.net", "/Created On:(.*)/"), 
        "me" => array("whois.nic.me", "/Domain Create Date:(.*)/"), 
        "cn" => array("whois.cnnic.cn", "/Registration Date:(.*)/"), 
        "asia" => array("whois.nic.asia", "/Domain Create Date:(.*)/"), 
        "ro" => array("whois.rotld.ro", "/Registered On:(.*)/"), 
        "aero" => array("whois.aero", "/Created On:(.*)/"), 
        "nu" => array("whois.nic.nu", "/created:(.*)/")
    );

    public function age($domain)
    {
        
        $domain = trim($domain); //remove space from start and end of domain
        if (substr(strtolower($domain), 0, 7) == "http://")
            $domain = substr($domain, 7); // remove http:// if included
        if (substr(strtolower($domain), 0, 4) == "www.")
            $domain = substr($domain, 4); //remove www from domain
        if (preg_match("/^([-a-z0-9]{2,100}).([a-z.]{2,8})$/i", $domain)) {
            $domain_parts = explode(".", $domain);
            $tld          = strtolower(array_pop($domain_parts));
            if (!$server = $this->WHOIS_SERVERS[$tld][0]) {
                return false;
            }
            $res = $this->QueryWhoisServer($server, $domain);
            if (preg_match($this->WHOIS_SERVERS[$tld][1], $res, $match)) {
                date_default_timezone_set('UTC');
                $time  = time() - strtotime($match[1]);
                $years = floor($time / 31556926);
                $days  = floor(($time % 31556926) / 86400);
                if ($years == "1") {
                    $y = "1 year";
                } else {
                    $y = $years . " years";
                }
                if ($days == "1") {
                    $d = "1 day";
                } else {
                    $d = $days . " days";
                }
                return "$y, $d";
            } else
                return false;
        } else
            return false;
    }
    
    private function QueryWhoisServer($whoisserver, $domain)
    {
        $port    = 43;
        $timeout = 10;
        $fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout) or die("Socket Error " . $errno . " - " . $errstr);
        //if($whoisserver == "whois.verisign-grs.com") $domain = "=".$domain; // whois.verisign-grs.com requires the equals sign ("=") or it returns any result containing the searched string.
        fputs($fp, $domain . "\r\n");
        $out = "";
        while (!feof($fp)) {
            $out .= fgets($fp);
        }
        fclose($fp);
        
        $res = "";
        if ((strpos(strtolower($out), "error") === FALSE) && (strpos(strtolower($out), "not allocated") === FALSE)) {
            $rows = explode("\n", $out);
            foreach ($rows as $row) {
                $row = trim($row);
                if (($row != '') && ($row{0} != '#') && ($row{0} != '%')) {
                    $res .= $row . "\n";
                }
            }
        }
        return $res;
    }
}
?>