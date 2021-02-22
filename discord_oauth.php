<?php
/**
 * This file provides functions to complete the Discord OAuth2 Flow.
 * 
 * Use this as a guide only. It duplicates code for ease of assessibility.
 * 
 * Author:          Lachee
 * Last Updated:    Feb 2021
 * License:         MIT
*/

if (!defined("DISCORD_API"))
    define("DISCORD_API", "https://discordapp.com/api/v8");


/**
 * Creates a GET request to Discord API using the Bearer authentication mode.
 * See discord_get for Bot authentication mode.
 */
function discord_oauth_get($route, $accessToken) 
{
    $uri        = DISCORD_API . $route;
    $headers    = [ 
                    'Authorization: Bearer ' . $accessToken,    //Set the authorization to bearer
                    'Content-Type: application/json'            //We are going to be sending and receiving json
                  ];

    //Perform the CURL
    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     //We want data back
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');     //Its a GET request
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers );   //Set the headers

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);        //Handle some SSL errors that may occur if locally hosting
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);        //Handle some SSL errors that may occur if locally hosting

    //Execute and get the JSON data
    $response = curl_exec($ch);
    $json = json_decode($response, true);
    return $json;
}

/**
 * Exchanges the oauth code for bearers token
 */
function discord_oauth_exchange($clientID, $clientSecret, $scope, $redirect,  $code)
{
    $data = [
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect,
        'scope' => $scope
    ];

    $headers    = [ 'Content-Type: application/x-www-form-urlencoded' ];    //We explicitly need this header
    $query      = http_build_query($data);                                  //This outputs "client_id=XXX&client_secret=XXX&grant_Type=authroization_code ..."
    $uri        = DISCORD_API . "/oauth2/token";                            //This is the exchange URL
    

    //Perform the CURL
    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     //We want data back
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');    //Its a POST request
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);       //Give the encoded query string
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers );   //Set the headers

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);        //Handle some SSL errors that may occur if locally hosting
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);        //Handle some SSL errors that may occur if locally hosting

    //Execute and get the JSON data
    $response = curl_exec($ch);
    $json = json_decode($response, true);
    return $json;
}

/**
 * Sets the location header to the discord authorization screen and exits. 
 * This will cause the user to be redirected to discord to accept the oauth.
 * 
 * NOTE: This does not handle the 'state'. 
 * This is an optional security feature to ensure the user being redirected back from discord is the same one you sent to discord.
 */
function discord_oauth_redirect($clientID, $scope, $redirect, $no_prompt = true)
{ 
    $authurl = DISCORD_API . "/oauth2/authorize";
    $params = [
        "response_type" => "code",
        "client_id" => $clientID,
        "scope" => $scope,
        "redirect_uri" => $redirect
    ];

    //if no prompt, add to our array
    if ($no_prompt) $params['prompt'] = 'none'; 

    //Turn to a string. This outputs "response_type=code&client_id=XXX ..."
    $query = http_build_query($params);

    //Redirect back to the auth URL with our data.
    header("location: {$authurl}?{$query}");
    exit;
}