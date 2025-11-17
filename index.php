<?php
// On renvoie toujours du JSON
header('Content-Type: application/json; charset=utf-8');

// (Optionnel, si la page HTML est sur un autre domaine/IP/port)
// header('Access-Control-Allow-Origin: *');

// =======================
// CONFIG SOAP EMUCOACH
// =======================
const SOAP_HOST = 79.90.44.4'';          // 
const SOAP_PORT = 7878;                 // Port SOAP (worldserver.conf)
const SOAP_USER = 'SOAPAdmin';          // Compte GM que tu as créé pour SOAP
const SOAP_PASS = 'authserver5815';   // Mot de passe de ce compte GM

// Répondre erreur simple si pas en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 'Méthode invalide.'
    ]);
    exit;
}

// Récupération des champs envoyés par ton JS
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$username = trim($username);
$password = trim($password);

// Validation basique
if (!pregmatch('/^[A-Za-z0-9]{3,16}$/', $username)) {
    echo jsonencode([
        'success' => false,
        'error'   => 'Le pseudo doit contenir 3 à 16 caractères (lettres, chiffres, ).'
    ]);
    exit;
}

if (strlen($password) < 6 || strlen($password) > 32) {
    echo json_encode([
        'success' => false,
        'error'   => 'Le mot de passe doit contenir entre 6 et 32 caractères.'
    ]);
    exit;
}

// Vérifier que l’extension SOAP est disponible
if (!class_exists('SoapClient')) {
    echo json_encode([
        'success' => false,
        'error'   => 'L’extension PHP SOAP n’est pas activée (php.ini).'
    ]);
    exit;
}

// =======================
// FONCTION SOAP
// =======================
function createAccountViaSOAP(string $username, string $password)
{
    $command  = ".account create {$username} {$password}";
    $location = "http://" . SOAP_HOST . ":" . SOAP_PORT . "/";
    $uri      = "urn:TC"; //

    try {
        $client = new SoapClient(null, [
            'location' => $location,
            'uri'      => $uri,
            'style'    => SOAP_RPC,
            'login'    => SOAP_USER,
            'password' => SOAP_PASS,
            'connection_timeout' => 5,
            'trace'    => 1,
        ]);

        $response = $client->executeCommand(new SoapParam($command, "command"));

        // Si tu veux voir ce que répond le core :
        // file_put_contents(DIR.'/soap_debug.txt', print_r($response, true), FILE_APPEND);

        return [true, null];
    } catch (SoapFault $e) {
        return [false, "SOAPFault : " . $e->getMessage()];
    } catch (Exception $e) {
        return [false, "Exception : " . $e->getMessage()];
    }
}

// Appel de la fonction
list($ok, $err) = createAccountViaSOAP($username, $password);

if ($ok) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error'   => $err ?? 'Erreur inconnue lors de la création du compte.'
    ]);
}
✅ Ce que ça fait, en lien avec ton HTML/JS
Dans ta page, tu as :

js
Copier le code
const res = await fetch('http://79.90.44.4/create_wow_account.php', {
  method: 'POST',
  body: fd
});
const json = await res.json();

document.getElementById('result').textContent = json.success
  ? "✔ Compte WoW créé !"
  : "❌ " + json.error; 
