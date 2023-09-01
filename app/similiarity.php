<?php
include ('../vendor/autoload.php');

use \NlpTools\Tokenizers\WhitespaceTokenizer;
use \NlpTools\Similarity\CosineSimilarity;

// Defina um array de perguntas e respostas
$perguntas = [
    "Qual é o seu nome?",
    "Onde você mora?",
    "Qual é a sua comida favorita?",
    "Como você está?",
    "O que você gosta de fazer?"
];

$respostas = [
    "Meu nome é ChatGPT.",
    "Eu sou uma IA, não tenho um local físico.",
    "Minha comida favorita é eletricidade!",
    "Estou bem, obrigado por perguntar. Como posso ajudar você?",
    "Eu gosto de ajudar as pessoas e responder perguntas."
];

// Tokenizador e similaridade de cosseno
$tokenizer = new WhitespaceTokenizer();
$cosineSimilarity = new CosineSimilarity();

// Pergunta do usuário
$perguntaDoUsuario = "Qual é o teu nom?";

// Encontre a pergunta mais semelhante nas perguntas existentes
$melhorSimilaridade = -1;
$respostaSelecionada = "Desculpe, não entendi a pergunta.";

foreach ($perguntas as $i => $pergunta) {
    $similaridade = $cosineSimilarity->similarity(
        $tokenizer->tokenize($perguntaDoUsuario),
        $tokenizer->tokenize($pergunta)
    );

    if ($similaridade > $melhorSimilaridade) {
        $melhorSimilaridade = $similaridade;
        $respostaSelecionada = $respostas[$i];
    }
}

// Exibir a resposta selecionada
echo "Usuário: $perguntaDoUsuario\n";
echo "ChatBot: $respostaSelecionada\n";
?>