<?php 
$apiKey = getenv('OPENAI_API_KEY');
$ghKey = getenv('GITHUB_TOKEN');

function call_API_POST_ChatGPT($json_body, $apiKey){
    $curl = curl_init('https://api.openai.com/v1/responses');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
      'Authorization: bearer '. $apiKey,
      'Content-Type: application/json',
      'Content-Length: ' . strlen($json_body)
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json_body);
    $api_response = curl_exec($curl);
    curl_close($curl);
    return $api_response;
}

// Récupérer les deux derniers tags
$tags = shell_exec("git tag --sort=-creatordate | head -2");
// Convertir la sortie en tableau et récupérer les tags
$tags = explode("\n", trim($tags));
$latest_tag = $tags[0];   // Dernier tag
$previous_tag = $tags[1]; // Tag précédent

echo "Dernier tag : ".$latest_tag."\n";
echo "Tag précédent : ".$previous_tag."\n";

if (empty($previous_tag) || empty($latest_tag)) {
    die("Erreur : l'un des tags est vide.");
}

// Authentification à Github CLI - Nécessaire uniquement pour exécuter en local
// shell_exec("echo ".$ghKey." | gh auth login --with-token");
echo shell_exec("gh auth status");

echo shell_exec("git tag --sort=-creatordate");

// Récupération du git log de la version qui correspond au dernier tag
$command = 'git log '.$previous_tag.'..'.$latest_tag; // Commande Git à exécuter
$git_log = shell_exec($command);
echo "Git log result : ".$git_log;

if ($git_log === null) {
    echo "Erreur lors de l'exécution de la commande.";
    exit();
} else { // call API OpenAI
    $prompt = "Je te fournis à la fin de ce prompt un extract de gitlog en input et voici les instructions que tu dois suivre :
- La sortie doit être en français.
- Le format de la sortie doit être du Markdown (.md) afin que je copie/colle la release note générée dans Github. Donne moi une sortie comme une citation de code au format Markdown.
- Ne mets pas de balises Markdown autour de la sortie.
- Ne mets pas ``` avant et après la sortie.
- Ne mets pas de liens vers les commits. Seulement les changements en français.
- il faut que tu mentionnes les nouvelles features, les évolutions
de features visibles par les utilisateurs, les bugs corrigés, 
les optimisations (mais sans rentrer dans les détails techniques)
et les évolutions techniques importantes
- Les optimisation doivent être incluses dans 'Evolutions techniques'
- Ne mentionne pas les commits de merge
- Ne mentionne pas les choses qui sont des petits détails techniques
- Ne mentionne pas les modifications du modèle de données
- Traite les commits par ordre du plus ancien au plus récent
- Les suppressions de code obsolète ou nettoyages de commentaires ne 
doivent pas être mentionnés.
Voici maintenant le texte du git log sur lequel tu dois travailler : ".$git_log;

    $body = [
        'model' => 'gpt-5',
        'input' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ];
    $json_body = json_encode($body);
    $api_response = call_API_POST_ChatGPT($json_body, $apiKey);
    echo "API response : ".$api_response;

    $json_response = json_decode($api_response);

    $release_note =  $json_response->output[0]->content[0]->text;

    // Créer un fichier temporaire
    $filePath = "RELEASE_NOTES.md";
    file_put_contents($filePath, $release_note);

    // Créer une release sur Github avec la CLI Github
    $response_create_release = shell_exec("gh release create ".$latest_tag." --title \"Release ".$latest_tag."\" --notes-file $filePath --draft");
}

