<?php 

/**
 * 
 * Paytm web checkout integration service
 * sources-
 * https://github.com/Paytm-Payments/Paytm_Web_Sample_Kit_PHP
 * 
*/

namespace App\Service;

/**
 * Paytm lib encdec_paytm
*/
require_once(app_path(). "/Service/paytm_lib/encdec_paytm.php");

/**
 * For fetching Paytm $_GET callback requests 
*/
use Illuminate\Http\Request;
use Session;
use URL;

class PaytmPayment 
{
    private $_paytm_env;
    private $_api_paytm;
    private $_callback_url;

    /**
     * PaytmPayment constructor.
     *
     */
    public function __construct()
    {
        // set paytm config
        $this->_paytm_env = setting('apps.paytm_production') ? 'PROD' : 'TEST';

        // TEST environment
        $this->_api_paytm = [
            'query_new_url' => 'https://securegw-stage.paytm.in/merchant-status/getTxnStatus',
            'txn_url'       => 'https://securegw-stage.paytm.in/theia/processTransaction',
        ];
        
        // PROD environment
        if ($this->_paytm_env == 'PROD') 
        {
            $this->_api_paytm = [
                'query_new_url' => 'https://securegw.paytm.in/merchant-status/getTxnStatus',
                'txn_url'       => 'https://securegw.paytm.in/theia/processTransaction',
            ];
        }

        // set callback url
        $this->_callback_url = '/checkout/paytm/callback';

        // set paytm config
        $this->config_paytm();
    }

    /**
     * Set environment variables for 
     * encdec_paytm file
    */
    private function config_paytm()
    {
        define('PAYTM_ENVIRONMENT', $this->_paytm_env);
        define('PAYTM_MERCHANT_KEY', setting('apps.paytm_merchant_key'));
        define('PAYTM_MERCHANT_MID', setting('apps.paytm_merchant_id'));
        define('PAYTM_MERCHANT_WEBSITE', setting('apps.paytm_merchant_website'));
        define('PAYTM_STATUS_QUERY_URL', $this->_api_paytm['query_new_url']);
        define('PAYTM_STATUS_QUERY_NEW_URL', $this->_api_paytm['query_new_url']);
        define('PAYTM_TXN_URL', $this->_api_paytm['txn_url']);
        define('PAYTM_REFUND_URL', '');

        return true;
    }

    // 1. Validate and create new order request for single item only
    public function create_order($order = [])
    {
        // required params
        if( 
            empty($order['price']) ||
            empty($order['order_number'])
        ) 
            return ['error' => 'Missing parameters', 'status' => false];

        // Paytm API verification 
        $paramList                      = [];
        $paramList["MID"]               = setting('apps.paytm_merchant_id');
        $paramList["ORDER_ID"]          = $order['order_number'];
        $paramList["CUST_ID"]           = session('payment_method')['customer_email'];
        $paramList["INDUSTRY_TYPE_ID"]  = setting('apps.paytm_industry_type');
        $paramList["CHANNEL_ID"]        = setting('apps.paytm_channel');
        $paramList["TXN_AMOUNT"]        = $order['price'];
        $paramList["WEBSITE"]           = setting('apps.paytm_merchant_website');
        $paramList["CALLBACK_URL"]      = url($this->_callback_url);

        
        // Paytm Order validation checksum
        //Here checksum string will return by getChecksumFromArray() function.
        $paramList["CHECKSUMHASH"]      = getChecksumFromArray($paramList, setting('apps.paytm_merchant_key'));
        
        
        // generate GET params
        $params = "?";
        $i      = 1;
        foreach($paramList as $key => $val)
        {
            // IMPORTANT!!! urlencode convert the url to a $_GET URL
            $params .= "$key=".urlencode($val);

            // skip & at the end
            if($i < count($paramList))
                $params .= "&";

            $i++;
        }
        
        // generate redirect url
        $redirect_url                   = $this->_api_paytm['txn_url'].$params;

        //  this is just for filter out fake request on callback url
        // and is used in final transaction status check
        session(['paytm_order_id' => $order['order_number']]);

        
        // url: redirect to paytm for checkout  
        return ['url' => $redirect_url, 'status' => true];
    }

    // 2. On return from gateway check if payment fail or success
    public function callback(Request $request)
    {
        /** 
         * This is a two step process to verify if payment successful or not
        */

        // 1. Verify checksumhash received in response to ensure that it has not been tampered
        $flag = $this->verify_checksumhash($request);
        if(!$flag['status'])
            return $flag;

        // 2. Verify transaction status with Transaction Status API via server to server call. 
        //    This protects you from scenarios where your account credentials are compromised 
        //     or request/response has been tampered
        $result = $this->verify_transaction($request);

        // on the basis of result, we can finally decide if payment success or failed
        // second check if payment failed
        if($result['STATUS'] == "TXN_SUCCESS")
        {
            // set success data
            $success = [
                'transaction_id'    => $result['TXNID'],
                'payer_reference'   => session('payment_method')['customer_email'],
                'message'           => $result['RESPCODE'].': '.$result['RESPMSG'],
                'status'            => true,
            ];
            
            return $success;
        }

        return [
            'error'         => $result['RESPCODE'].': '.$result['RESPMSG'], 
            // only for reference
            'transaction_id'=> $result['TXNID'],
            'status'        => false
        ];
    }   

    // Verify checksumhash
    private function verify_checksumhash(Request $request)
    {
        $payment_id = $request->has('TXNID') ? $request->input('TXNID') : null;

        // if in return not get PayerID and token, then payment cancelled
        if (!$request->has('CHECKSUMHASH') || !$request->has('TXNID') || empty(session('paytm_order_id')) ) 
        {
            return [
                'error'         => 'Payment cancelled!', 
                // only for reference
                'transaction_id'=> $payment_id,
                'status'        => false
            ];
        }

        // get the response data from Paytm
        $paytmChecksum      = $request->input('CHECKSUMHASH');
        $paramList          = $request->all();
        
        
        // Verify all parameters received from Paytm pg to your application. 
        // Like MID received from paytm pg is same as your application’s MID, TXN_AMOUNT and 
        // ORDER_ID are same as what was sent by you to Paytm PG for initiating transaction etc.
        // will return TRUE or FALSE string.
        $isValidChecksum    = verifychecksum_e($paramList, setting('apps.paytm_merchant_key'), $paytmChecksum); 

        // first check if checksum is compromised
        if(!$isValidChecksum)
        {
            return [
                'error'         => 'Payment cancelled!', 
                // only for reference
                'transaction_id'=> $payment_id,
                'status'        => false
            ];
        }
            
        // second check if payment failed
        if($request->input('STATUS') == "TXN_SUCCESS")
        {
            // set success data
            $success = [
                'transaction_id'    => $payment_id,
                'payer_reference'   => session('payment_method')['customer_email'],
                'message'           => 'approved',
                'status'            => true,
            ];
            
            return $success;
        }

        return [
            'error'         => 'Payment failed!', 
            // only for reference
            'transaction_id'=> $payment_id,
            'status'        => false
        ];
    } 
    
    // Verify transaction status
    private function verify_transaction(Request $request)
    {
        // this is final level payment status test
        
        // Create an array having all required parameters for status query.
        $requestParamList   = [
            "MID"       => setting('apps.paytm_merchant_id'),
            "ORDERID"   => session('paytm_order_id')
        ];

        $StatusCheckSum     = getChecksumFromArray($requestParamList, setting('apps.paytm_merchant_key'));
        $requestParamList['CHECKSUMHASH'] = $StatusCheckSum;

        // Call the PG's getTxnStatusNew() function for verifying the transaction status.
        return getTxnStatusNew($requestParamList);
    }

}
