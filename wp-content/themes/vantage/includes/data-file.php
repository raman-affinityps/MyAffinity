<?php
 //if ( ! defined( 'ABSPATH' ) ) exit;
class AnalyticsData 
{

    var $startDate  = '';
    var $endDate    = '';
    var $otherOpt   = array();
    var $metrix     ='';
    var $analytics;
    protected $htmlObject;  
    public function __construct($parameter) 
    {
        $this->analytics = $this->getService();
        $this->htmlObject=new TableFormat($parameter);		 
    }
  
    // Creates and returns the Analytics service object.
    public function getService() 
    {
        // Load the Google API PHP Client Library.
        require_once get_template_directory().'/includes/google-api-php-client/src/Google/autoload.php';
        // Use the developers console and replace the values with your
        // service account email, and relative location of your key file.
        $service_account_email = SERVICE_ACCOUNT_EMAIL;
        $key_file_location     = KEY_FILE_LOCATION;

        // Create and configure a new client object.
        $client                = new Google_Client();
        $analytics             = new Google_Service_Analytics($client);

        // Read the generated client_secrets.p12 key.
        $key                   = file_get_contents($key_file_location);
        $cred                  = new Google_Auth_AssertionCredentials(
                                    $service_account_email,
                                    array(Google_Service_Analytics::ANALYTICS_READONLY),
                                    $key
                                    );
        $client->setAssertionCredentials($cred);
        if($client->getAuth()->isAccessTokenExpired()) 
        {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }
        return $analytics;
    }
    /**
    * @param startDate = Start Date 
    * @param endDate = End Date
    * @param metrix = Metrix (data of users/pages)
    * @param otherOpt = diminsion (get data accroeding to pagePath, mint)
    *                   Fillter (show data after placed fillter )
    * NOTE:- 1) Date format in YYYY-MM-DD
    *        2) OtherOpt must be array  
    */  
    public function getGaData($startDate,$endDate,$metrixArray,$otherOpt,$dataformat,$ajax) 
    {	
	foreach($metrixArray as $key=>$metrix)
        {
            $this->metrix    = ($metrix!='')?$metrix:ANALYTIC_DEFAULT_METRIX;
            $this->startDate = ($startDate!='')?$startDate:ANALYTIC_DEFAULT_START_DATE;
            $this->endDate   = ($endDate!='')?$endDate:ANALYTIC_DEFAULT_END_DATE;
            if($dataformat=='realTimeVisit')
            {
                //$result[$key] ="";
                 $result[$key] = $this->analytics->data_realtime->get(ANALYTIC_ID, $this->startDate, $this->endDate,$this->metrix,$otherOpt);
                
            }
            else
            {
                if(is_array($otherOpt) && count($otherOpt)>0) 
                {                 
                    $result[$key] = $this->analytics->data_ga->get(ANALYTIC_ID, $this->startDate, $this->endDate,$this->metrix,$otherOpt);

                } 
                else
                {
                    $result[$key] = $this->analytics->data_ga->get(ANALYTIC_ID, $this->startDate, $this->endDate,$this->metrix);
                }
            }
	}
        if(count($result)<2)
        {
            $this->htmlObject->htmlstructure($result["0"]['rows'],$dataformat,$ajax);
        }
        else
        {
            $this->htmlObject->htmlstructure($result,$dataformat,$ajax);
        }		
    }   
    /**
    * @param metrix = Metrix (data of users/pages)
    * @param otherOpt = diminsion (get data accroeding to pagePath, mint)
    *                   Fillter (show data after placed fillter )
    * NOTE:- 1) OtherOpt must be array     
    */  
    public function getRealTimeData($metrix,$otherOpt,$dateformat) 
    {
        $this->metrix = ($metrix!='')?$metrix:ANALYTIC_DEFAULT_REALTIME_METRIX;
        if(is_array($otherOpt) && count($otherOpt)>0) 
        {
            $result = $this->analytics->data_realtime->get(ANALYTIC_ID,$this->metrix,$otherOpt);
        } 
        else 
        { 
            $result = $this->analytics->data_realtime->get(ANALYTIC_ID,$this->metrix);
        }      
        print_r($result['rows']); exit;
        $this->htmlObject->htmlstructure($result['rows'],$dataformat);
    }

}
?>
