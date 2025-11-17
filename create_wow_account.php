<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (!$username || !$password) {
    echo json_encode(['success'=>false,'error'=>'Champs manquants']);
    exit;
}

// CONFIG SOAP
$soap_host = "79.90.44.4";
$soap_port = 7878;
$soap_user = "admin";       // le SOAP.Username du serveur WoW
$soap_pass = "TON_MDP_ICI"; // le SOAP.Password du serveur WoW

$cmd = "account create $username $password";

$xml = '<?xml version="1.0"?>'
    .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">'
    .'<SOAP-ENV:Body>'
    .'<executeCommand>'
    .'<command>' . htmlspecialchars($cmd) . '</command>'
    .'</executeCommand>'
    .'</SOAP-ENV:Body>'
    .'</SOAP-ENV:Envelope>';

$curl = curl_init("http://$soap_host:$soap_port/");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Content-Type: text/xml",
    "Authorization: Basic ".base64_encode("$soap_user:$soap_pass")
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['success'=>false,'error'=>"Erreur SOAP : $err"]);
    exit;
}

echo json_encode(['success'=>true, 'message'=>"Compte WoW créé !"]);
