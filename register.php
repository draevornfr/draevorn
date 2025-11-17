<?php
// =======================
// CONFIG SOAP EMUCOACH
// =======================
const SOAP_HOST = '127.0.0.1';          // si le site est sur la même machine que le worldserver
// si ton site est sur une autre machine, mets ici l'IP du serveur où tourne le worldserver
const SOAP_PORT = 7878;                 // Port SOAP (dans worldserver.conf)
const SOAP_USER = 'SOAPADMIN';          // Compte GM créé pour SOAP
const SOAP_PASS = 'MonMotDePasse123';   // Mot de passe du compte GM

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Nettoyage simple
    $username = trim($username);
    $password = trim($password);

    // Quelques règles de base
    if (!pregmatch('/^[A-Za-z0-9]{3,16}$/', $username)) {
        $error = "Le pseudo doit contenir 3 à 16 caractères (lettres, chiffres, _).";
    } elseif (strlen($password) < 6 || strlen($password) > 32) {
        $error = "Le mot de passe doit contenir entre 6 et 32 caractères.";
    } else {
        // Tentative de création de compte via SOAP
        $result = createAccountViaSOAP($username, $password);

        if ($result === true) {
            $success = "Compte créé avec succès ! Tu peux essayer de te connecter en jeu.";
        } else {
            $error = "Erreur lors de la création du compte : " . $result;
        }
    }
}

/**
 
Envoie la commande .account create via SOAP au worldserver EmuCoach*/
function createAccountViaSOAP(string $username, string $password)
{
    $command = ".account create {$username} {$password}";

    $location = "http://" . SOAP_HOST . ":" . SOAP_PORT . "/";
    $uri      = "urn:TC"; // EmuCoach est basé TrinityCore

    try {
        if (!class_exists('SoapClient')) {
            return "L’extension PHP SOAP n’est pas activée (vérifie php.ini).";
        }

        $client = new SoapClient(null, [
            'location' => $location,
            'uri'      => $uri,
            'style'    => SOAP_RPC,
            'login'    => SOAP_USER,
            'password' => SOAP_PASS,
            'connection_timeout' => 5,
            'trace'    => 1,
        ]);

        // EmuCoach/TrinityCore : méthode executeCommand avec param "command"
        $response = $client->executeCommand(new SoapParam($command, "command"));

        // Si besoin de debug, tu peux écrire la réponse dans un fichier :
        // file_put_contents(DIR . '/soap_debug.txt', print_r($response, true), FILE_APPEND);

        return true;
    } catch (SoapFault $e) {
        return "SOAPFault : " . $e->getMessage();
    } catch (Exception $e) {
        return "Exception : " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscription - Serveur EmuCoach</title>
</head>
<body>
    <h1>Inscription</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>
            Pseudo :
            <input type="text" name="username" required>
        </label><br><br>

        <label>
            Mot de passe :
            <input type="password" name="password" required>
        </label><br><br>

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
