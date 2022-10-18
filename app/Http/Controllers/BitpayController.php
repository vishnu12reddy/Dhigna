<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use BitPayKeyUtils\KeyHelper\PrivateKey;
use BitPayKeyUtils\Storage\EncryptedFilesystemStorage;
use Symfony\Component\Yaml\Yaml;
use BitPaySDK\Model\Invoice\Invoice;
use BitPaySDK\Model\Invoice\Buyer;
use Exception;

class BitpayController extends Controller
{
    public $isProd = false; // Set to true if the environment for which the configuration file will be generated is Production.
    // Will be set to Test otherwise
    
    public $privateKeyname        = null; // Add here the name for your Private key
    public $generateMerchantToken = false; // Set to true to generate a token for the Merchant facade
    public $yourMasterPassword    = null; //Will be used to encrypt your PrivateKey
    public $generateJSONfile      = false; // Set to true to generate the Configuration File in Json format
    public $privateKey            = null;
    public $merchantToken         = null;
    public $payrolToken           = null;
    public $pairingCode           = null;
    public $baseUrl               = null;
    public $env                   = null;
    public $sin                   = null;
    public $publicKey             = null;
    public $bitpay                = null;

    public function __construct()
    {
        
        $this->privateKeyname     =  !empty(setting('apps.bitpay_key_name')) ? setting('apps.bitpay_key_name') :  'BitPay';
        $this->yourMasterPassword =  !empty(setting('apps.bitpay_encrypt_code')) ? setting('apps.bitpay_encrypt_code') :  'BitPay';
        $this->isProd             =  (int)setting('apps.bitpay_production');
        
        $this->privateKey      = new PrivateKey($this->privateKeyname);
        $this->storageEngine   = new EncryptedFilesystemStorage($this->yourMasterPassword);
        $this->baseUrl         = $this->isProd ? 'https://bitpay.com' : 'https://test.bitpay.com';
        $this->env             = $this->isProd ? 'Prod' : 'Test';

        if(!file_exists(public_path('BitPay.config.json')))
        {
            $this->generateMerchantToken = true;
            $this->generateJSONfile      = true;
        }

    }

    /**
     *  bitpay payment reqest
     */
    public function bitpayPaymentRequest($order = [], $currency = 'USD')
    {
        try
        {
            $this->generatePrivateKey();

            $this->generatePublicKey();

            // only for merchant user
            $resultData = $this->requestMerchantfacade();
            
            if(!empty($resultData))
            {
                if (array_key_exists('error', $resultData))
                {
                    return response()->json(['status' =>  false, 'message'=>$resultData['error']]);  
                }
            }
            
            $approved_url = $this->createJosnFile();
            
            if(!empty($approved_url))
            {
                return $approved_url;
            }
            // End

            $this->initializeClient();

            return $this->createInvoice($order, $currency);
        }
        catch(\Exception $ex)
        {
            return response()->json(['status' =>  false, 'message'=>$ex->getMessage()]);  
        }
    }

    /**
     *  bitpay payment response
     */

    public function bitpayPaymentResponse()
    {
        try
        {
            $invoiceId = session('invoiceId');
            $data      = [];
            $this->initializeClient();

            $invoice = $this->bitpay->getInvoice($invoiceId);
            
            $status = $invoice->getStatus();
            
            if($status == 'paid' || $status == 'confirmed' || $status == 'complete')
            {
                $data = [
                    'transaction_id'    => $invoice->getTransactions()[0]->txid,
                    'payer_reference'   => $invoice->getId(), // invoice id
                    'message'           => $invoice->getStatus(),
                    'status'            => true,
                ];
                
            }
            else
            {
                $data = [
                    // only for reference
                    'error'     => __('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed'), 
                    'status'    => false
                ];
                
            }
        }
        catch(Exception $ex)
        {
            $data = [
                // only for reference
                'error'     => $ex->getMessage(), 
                'status'    => false
            ];
        }
        
        return $data;
    }

    /**
     *  generate private key
     */

    public function generatePrivateKey()
    {
        try 
        {
            //  Use the EncryptedFilesystemStorage to load the Merchant's encrypted private key with the Master Password.
            $this->privateKey = $this->storageEngine->load($this->privateKeyname);
            
        } 
        catch (\Exception $ex) 
        {
            //  Check if the loaded keys is a valid key
                if (!$this->privateKey->isValid()) 
                {
                    $this->privateKey->generate();
                }
            
            //  Encrypt and store it securely.
            //  This Master password could be one for all keys or a different one for each Private Key
                $this->storageEngine->persist($this->privateKey);
            
        }    
    }

    /**
     *  Generate the public key from the private key every time (no need to store the public key)
     */
    public function generatePublicKey()
    {
        $this->publicKey = $this->privateKey->getPublicKey();
        
        // Derive the SIN from the public key
        $this->sin       = $this->publicKey->getSin()->__toString();
        
    }

    /**
     * Request a token for the Merchant facade
     */
    public function requestMerchantfacade()
    {
        if ($this->generateMerchantToken) 
        {
            $facade = 'merchant';

            $postData = json_encode(
                [
                    'id'     => $this->sin,
                    'facade' => $facade,
                ]);
                
                    
            $curlCli = curl_init($this->baseUrl . "/tokens");

            curl_setopt(
                $curlCli, CURLOPT_HTTPHEADER, [
                'x-accept-version: 2.0.0',
                'Content-Type: application/json',
                'x-identity'  => $this->publicKey->__toString(),
                'x-signature' => $this->privateKey->sign($this->baseUrl . "/tokens".$postData),
            ]);

            curl_setopt($curlCli, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curlCli, CURLOPT_POSTFIELDS, stripslashes($postData));
            curl_setopt($curlCli, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlCli, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($curlCli);
            
            $resultData = json_decode($result, true);
            curl_close($curlCli);

            /**
             * Example of a pairing Code returned from the BitPay API
             * which needs to be APPROVED on the BitPay Dashboard before being able to use it.
             **/
            $this->merchantToken = $resultData['data'][0]['token'];
            $this->pairingCode   = $resultData['data'][0]['pairingCode'];
            
            return  $resultData;
        }
    }

    /**
     *  create json file
     */
    public function createJosnFile()
    {
        if ($this->generateJSONfile) 
        {
            $config = [
                "BitPayConfiguration" => [
                    "Environment" => $this->env,
                    "EnvConfig"   => [
                        'Test' => [
                            "PrivateKeyPath"   => $this->isProd ? null :public_path($this->privateKeyname),
                            "PrivateKeySecret" => $this->isProd ? null : $this->yourMasterPassword,
                            "ApiTokens"        => [
                                "merchant" => $this->isProd ? null : $this->merchantToken,
                                "payroll"  => $this->isProd ? null : $this->payrolToken,
                            ],
                            
                        ],
                        'Prod' => [
                            "PrivateKeyPath"   => !$this->isProd ? null :public_path($this->privateKeyname),
                            "PrivateKeySecret" => !$this->isProd ? null : $this->yourMasterPassword,
                            "ApiTokens"        => [
                                "merchant" => !$this->isProd ? null : $this->merchantToken,
                                "payroll"  => !$this->isProd ? null : $this->payrolToken,
                            ],
                        ],
                    ],
                ],
            ];
            
            $json_data = json_encode($config, JSON_PRETTY_PRINT);
            
            file_put_contents('BitPay.config.json', $json_data);

            $approved_url = $this->baseUrl."/api-access-request?pairingCode=".$this->pairingCode;

            return response()->json(['status' =>  true,  'url'=> $approved_url]);  
        }
    }

    /**
     * you can initialize the client 
     */
    public function initializeClient()
    {
        
        $this->bitpay = \BitPaySDK\Client::create()->withFile(public_path('BitPay.config.json'));
    }

    /**
     *   create invoice 
     */
    public function createInvoice($order = [], $currency = 'USD')
    {
        $payment_method = session('payment_method');
        
        $invoiceData = new Invoice(number_format((float)($order['price']), 2, '.', ''), $currency);
        $buyer       = new Buyer();

        $buyer->setName($payment_method['customer_name']);
        $buyer->setEmail($payment_method['customer_email']);
        
        $invoiceData->setBuyer($buyer);
        
        $invoiceData->setRedirectURL(route('bitpay_response'));

        $invoice    = $this->bitpay->createInvoice($invoiceData);
    
        $invoiceUrl = $invoice->getURL();
        
        session(['invoiceId' => $invoice->getId()]);

        return response()->json(['status' =>  true,  'url'=> $invoiceUrl]); 
    }

}
