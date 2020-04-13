<?php

namespace Paydirekt\Client;
require 'src/Paydirekt/Client/Checkout/CheckoutClient.php';
require 'src/Paydirekt/Client/Rest/EndpointConfiguration.php';
require 'src/Paydirekt/Client/Utility/Base64Url.php';
require 'src/Paydirekt/Client/Security/Hmac.php';
require 'src/Paydirekt/Client/Rest/GetRequestBuilder.php';
require 'src/Paydirekt/Client/Rest/PostRequestBuilder.php';
require 'src/Paydirekt/Client/Rest/RequestExecutor.php';
require 'src/Paydirekt/Client/Rest/RequestBuilderFactory.php';
require 'src/Paydirekt/Client/Security/UUID.php';
require 'src/Paydirekt/Client/Security/Random.php';
require 'src/Paydirekt/Client/Security/Nonce.php';
require 'src/Paydirekt/Client/Security/SecurityClient.php';

use Paydirekt\Client\Checkout\CheckoutClient;
use Paydirekt\Client\Rest\EndpointConfiguration;
use Paydirekt\Client\Security\SecurityClient;

class PayDirekt
{
    private $accessToken;
    private $checkoutClient;
    
    public function setUp()
    {
      // const API_KEY = "e81d298b-60dd-4f46-9ec9-1dbc72f5b5df";
      $apiKey = getenv('PAY_DIREKT_API_KEY') ?: "e81d298b-60dd-4f46-9ec9-1dbc72f5b5df";
      // const API_SECRET = "GJlN718sQxN1unxbLWHVlcf0FgXw2kMyfRwD0mgTRME=";
      $secretKey = getenv('PAY_DIREKT_SECRET_KEY') ?: "GJlN718sQxN1unxbLWHVlcf0FgXw2kMyfRwD0mgTRME=";
      $securityClient = new SecurityClient(EndpointConfiguration::getTokenObtainEndpoint(),
        $apiKey,
        $secretKey,
        EndpointConfiguration::getCaFile());
      $accessToken = $securityClient->getAccessToken();
      $this->accessToken = $accessToken['access_token'];

      $this->checkoutClient = CheckoutClient::withStandardEndpoint();
    }

    public function minimalCheckout()
    {
      $checkoutRequest = json_encode(RequestMocks::minimalCheckoutRequest());
      $checkout = $this->checkoutClient->createCheckout($checkoutRequest, $this->accessToken);

      // $this->assertCreatedCheckoutValid($checkout, RequestMocks::minimalCheckoutRequest());
    }

    public function directSaleCheckout($checkoutRequest)
    {
      //return $checkoutRequest;
      $checkout = $this->checkoutClient->createCheckout($checkoutRequest, $this->accessToken);
      return $checkout;
    }
    public function getCheckout($checkoutId)
    {
      $checkout = $this->checkoutClient->getCheckout($checkoutId, $this->accessToken);
      return $checkout;
    }
}

$pay_direkt = new PayDirekt();
$pay_direkt->setUp();

if ($argv[1] == 'post') {
    $checkoutRequest = stream_get_contents(fopen("php://stdin", "r")); # DK: that's how you do it: cat in.json | x.php
    $ret = $pay_direkt->directSaleCheckout($checkoutRequest);
    echo json_encode($ret, JSON_PRETTY_PRINT);
} else if ($argv[1] == 'get') {
    echo json_encode($pay_direkt->getCheckout($argv[2]), JSON_PRETTY_PRINT);
}



// $checkoutRequest = file_get_contents($fn);
// $x = $pay_direkt->DirectSaleCheckout($argv[1]);
