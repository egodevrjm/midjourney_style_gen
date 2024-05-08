<?php
$apiKey = "YOUR_API_KEY";
$apiUrl = "https://api.groq.com/openai/v1/chat/completions";

$prompt = $_POST['prompt'];

$data = [
    "model" => "llama3-70b-8192",
    "messages" => [
        [
            "role" => "user",
            "content" => "Create a visually compelling prompt for an AI image generator that captures the essence of {$prompt}. Focus on specific details about the scene, characters, colors, lighting, mood, and style to create a vivid and immersive description. Limit the response to no more than four concise sentences, avoiding any additional analysis or commentary."
        ]
    ],
    "max_tokens" => 1500,
    "temperature" => 0
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');

if ($response === false) {
    echo json_encode(["error" => "Unable to query Groq API", "details" => curl_error($ch)]);
} else {
    $result = json_decode($response, true);
    if ($result === null) {
        echo json_encode(["error" => "Failed to parse JSON response", "details" => $response]);
    } else {
        if (isset($result['error'])) {
            $error = $result['error'];
            echo json_encode(['error' => 'Groq API Error', 'type' => $error['type'], 'message' => $error['message'], 'details' => $error['details'] ?? null]);
            exit();
        } elseif (isset($result['choices'][0]['message']['content'])) {
            $haikuPrompt = $result['choices'][0]['message']['content'];
            $haikuPrompt = preg_replace('/^Here is a visually compelling prompt for an AI image generator:\s*/', '', $haikuPrompt);
            $haikuPrompt = preg_replace('/--sref.*|--ar.*/i', '', $haikuPrompt);
            $haikuPrompt = trim($haikuPrompt);
            echo json_encode(['haikuPrompt' => $haikuPrompt]);
        } else {
            echo json_encode(['error' => 'No prompt found in response', 'fullResponse' => $result]);
        }
    }
}