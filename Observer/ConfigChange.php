<?php 
namespace Markant\Bring\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigChange implements ObserverInterface
{
    //const XML_PATH_FAQ_URL = 'carriers/bring/title';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     */
    public function __construct(
        RequestInterface $request,
        WriterInterface $configWriter
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;

    }

    // public function getGeneralConfig($code, $storeId = null)
    // {
    //     return $this->getConfigValue(self::XML_PATH_HELLOWORLD . $code, $storeId);
    // }

    public function execute(EventObserver $observer)
    {
        
        $bringParams = $this->request->getParam('groups');        

        $title = (isset($bringParams['bring']['fields']['organization']['value']))?$bringParams['bring']['fields']['organization']['value']:""; 
        // $default_customer=$bringParams['bring']['groups']['calculation']['fields']['default_customer']['value'][0]; 
        // $customer=$bringParams['bring']['fields']['mybring_client_uid']['value'];
        // $title = "Ali here";
        //print_r($title);die();
        //$this->configWriter->save('carriers/bring/title', $title);
        $version='2.3.2';
        $customer=(isset($bringParams['bring']['groups']['calculation']['fields']['default_customer']['value'][0]))?$bringParams['bring']['groups']['calculation']['fields']['default_customer']['value'][0]:""; 
        $organization=$title;
        // $customer='111111';
        // $email=$bringParams['bring']['groups']['booking']['groups']['origin']['fields']['email']['value'];
        $email=(isset($bringParams['bring']['fields']['mybring_client_uid']['value']))?$bringParams['bring']['fields']['mybring_client_uid']['value']:"";
        $data = array(
            'domain' => $_SERVER['HTTP_HOST'],
            'ip' => $_SERVER['SERVER_ADDR'],
            'version' => $version,
            'orgnr' => $organization,
            'cunr' => $customer,
            'email' => $email,
        );

        $payload = json_encode($data);
        

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://pingback.markant.no/api/req/install/s22hsMviIN",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>$payload,
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Accept: application/json"
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $this;
    }
}