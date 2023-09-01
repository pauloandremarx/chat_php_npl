<?php
// Inclua aqui os headers de controle de acesso que você utiliza

// Coloque aqui o caminho correto para o autoload.php
require '../vendor/autoload.php';

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\NGramTokenizer;
use Phpml\Math\Distance\Euclidean;
use Phpml\Metric\ClassificationReport;


class Similarity {
    static public function dot($tags) {
        $tags = array_unique($tags);
        $tags = array_fill_keys($tags, 0);
        ksort($tags);
        return $tags;
    }

    protected function dot_product($a, $b) {
        $products = array_map(function($a, $b) {
            return $a * $b;
        }, $a, $b);
        return array_reduce($products, function($a, $b) {
            return $a + $b;
        });
    }

    protected function magnitude($point) {
        $squares = array_map(function($x) {
            return pow($x, 2);
        }, $point);
        return sqrt(array_reduce($squares, function($a, $b) {
            return $a + $b;
        }));
    }

    static public function cosine($a, $b, $base) {
        $a = array_fill_keys($a, 1) + $base;
        $b = array_fill_keys($b, 1) + $base;
        ksort($a);
        ksort($b);
        return self::dot_product($a, $b) / (self::magnitude($a) * self::magnitude($b));
    }
}


// Dados de treinamento (perguntas e respostas)
$trainingData = [
    ['pergunta' => 'Qual é a capital do Brasil?', 'resposta' => 'A capital do Brasil é Brasília.'],
    ['pergunta' => 'Quem escreveu Dom Quixote?', 'resposta' => 'Dom Quixote foi escrito por Miguel de Cervantes.'],
    ['pergunta' => 'Qual o seu nome', 'resposta' => 'Paulo'],
    ['pergunta' => 'Qual dia voce nasceu?', 'resposta' => '10'],
    // ... mais perguntas e respostas ...
];

// Extrair perguntas e respostas
$perguntas = array_column($trainingData, 'pergunta');
$respostas = array_column($trainingData, 'resposta');

// Criar vetorizador de contagem de tokens com NGramTokenizer
$vectorizer = new TokenCountVectorizer(new NGramTokenizer(1, 2));



$perguntasVetorizadas = $perguntas;
// Construir o dicionário para perguntas
$vectorizer->fit($perguntasVetorizadas);
$vectorizer->transform($perguntasVetorizadas);

// Pergunta a ser correspondida
$perguntaUsuario = [
    'Qual é a capital do Brasil?',
];

$perguntaUsuarioVetorizada = $perguntaUsuario;
$vectorizer->transform($perguntaUsuarioVetorizada);


$dot = Similarity::dot(call_user_func_array("array_merge", array_column($respostas, "resposta")));
// Encontrar o índice da pergunta mais similar

$indiceSimilaridade = findMostSimilar($perguntaUsuarioVetorizada[0], $perguntasVetorizadas, $dot);

// Exibir a resposta correspondente
if ($indiceSimilaridade !== null) {
    echo "Resposta: " . $respostas[$indiceSimilaridade];
} else {
    echo "Não foi encontrada uma resposta correspondente.";
}



function findMostSimilar($perguntaUsuarioVetorizada, $perguntasVetorizadas) {
    $maiorSimilaridade = -INF;
    $indiceSimilaridade = null;

    foreach ($perguntasVetorizadas as $indice => $perguntaVetorizada) {
        $euclidean = new Euclidean();
        $similaridade = $euclidean->distance($perguntaUsuarioVetorizada, $perguntaVetorizada);

        if ($similaridade > $maiorSimilaridade) {
            $maiorSimilaridade = $similaridade;
            $indiceSimilaridade = $indice;
        }
    }

    return $indiceSimilaridade;
}
?>
