<?php

require 'vendor/autoload.php'; 
use Stripe\Account; 
use Stripe\Stripe; 
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

function redirectToStripe($name)
{
    $uri = $_ENV['STRIPE_CONNECT_URL'] . $name;
    header("location: $uri"); 
}

function stripeCurlCommand($code)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://connect.stripe.com/oauth/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "client_secret=" .getenv('STRIPE_SECRET') . "&code=$code&grant_type=authorization_code");
    curl_setopt($ch, CURLOPT_POST, 1);

    $headers = array();
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    return json_decode(curl_exec($ch));
}


function loginToStripeDashboard($token)
{
    Stripe::setApiKey(getenv('STRIPE_SECRET')); 
    $account = Account::retrieve($token); 
    //This creates Stripe\LoginLink Object
    //Like so:
    // Stripe\LoginLink Object
    // (
    //     [object] => login_link
    //     [created] => 1549578166
    //     [url] => https://connect.stripe.com/express/fUMNINGoZhdd
    // )
    $object = $account->login_links->create(); 
    $uri = $object->url; 
    header("location: $uri"); 
}

if(isset($_POST['submit'])){
    //This sends the user to Stripe to input
    //their details to receive payments from
    //your application. 
    redirectToStripe($_POST['first_name']); 
}

if(isset($_GET['code'])){
    //Creates an Express Account. 
    /**@var stdClass $data */
    //Here is a sample of your data object: 
    // stdClass Object
    // (
    //     [access_token] => sk_test_**********************
    //     [livemode] => false
    //     [refresh_token] => rt_**********************
    //     [token_type] => bearer
    //     [stripe_publishable_key] => pk_test_**********************
    //     [stripe_user_id] => acct_**************
    //     [scope] => express
    // )
    $data = stripeCurlCommand($_GET['code']); 
    //Using the state you can identify the user via token or session. 
    //In the example I just put my name, however in your application 
    //you will want to saftely idenitfy the user. 
    $state = $_GET['state'];  
    //You will have to save this Id into your database somewhere
    //using the Stripe ID the user can login to their dashboard
    //or you can initate transfers to their account. 
    $stripeId = $data->stripe_user_id;
    echo '<pre>';
    print_r($data);
}

if(isset($_POST['login'])){
    //Send the user to Stripe where they can change
    //their info or view their payments. 
    loginToStripeDashboard($_POST['stripeId']);
}

?> 
