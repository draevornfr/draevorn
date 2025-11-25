<?php
// ai.php
header('Content-Type: application/json');

// Remplace par ta clé OpenAI
$OPENAI_API_KEY = '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';

// Lire le JSON envoyé
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['prompt'])) {
    echo json_encode(['response' => '❌ Pas de prompt reçu']);
    exit;
}

$prompt = $input['prompt'];

// Préparer la requête à OpenAI
$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role"=>"system","content"=>"Tu es Draevorn IA, assistante RP pour le site Draevorn, personnalité magique et mystérieuse."],
        ["role"=>"user","content"=>$prompt]
    ],
    "temperature"=>0.7,
    "max_tokens"=>300
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $OPENAI_API_KEY"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if(curl_errno($ch)){
    echo json_encode(['response'=>"❌ Erreur CURL : ".curl_error($ch)]);
    exit;
}
curl_close($ch);

// Traiter la réponse
$resData = json_decode($response, true);
if(isset($resData['choices'][0]['message']['content'])){
    $text = trim($resData['choices'][0]['message']['content']);
    echo json_encode(['response'=>$text]);
} else {
    echo json_encode(['response'=>"❌ Erreur API : réponse vide"]);
}
