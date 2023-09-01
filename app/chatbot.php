<?php


ini_set('display_errors', 1);

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Similarity\CosineSimilarity;

class Chatbot {
    private $questions;
    private $keywords;
    private $answers;

    public function __construct($questions, $keywords, $answers) {
        $this->questions = $questions;
        $this->keywords = $keywords;
        $this->answers = $answers;
    }

    private function preprocessQuestion($question) {
        $tokenizer = new WhitespaceTokenizer();
        $vectorizer = new TokenCountVectorizer($tokenizer);
        $transformer = new TfIdfTransformer();

        $questionTokens = $vectorizer->transform($tokenizer->tokenize($question));
        $questionsTokens = $vectorizer->transform($this->questions);

        $transformer->fit($questionsTokens);
        $questionsTokens = $transformer->transform($questionsTokens);
        $questionTokens = $transformer->transform($questionTokens);

        return [$questionTokens, $questionsTokens];
    }

    public function getBestAnswer($question) {
        [$questionTokens, $questionsTokens] = $this->preprocessQuestion($question);

        $similarity = new CosineSimilarity();
        $bestSimilarity = 0;
        $bestAnswer = '';

        foreach ($questionsTokens as $index => $storedQuestionTokens) {
            $score = $similarity->similarity($questionTokens[0], $storedQuestionTokens);

            if ($score > $bestSimilarity && isset($this->keywords[$index])) {
                $bestSimilarity = $score;
                $bestAnswer = $this->answers[$index];
            }
        }

        return $bestAnswer;
    }

    public function handleRequest($question) {
        $response = array('response' => '');

        if (!empty($question)) {
            $resposta = $this->getBestAnswer($question);
            $response['response'] = 'Resposta do chatbot para a pergunta: ' . $question . ' é: ' . $resposta;
        } else {
            $response['response'] = 'Erro: Nenhuma pergunta foi enviada.';
        }

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

$questions = [];
$keywords = [];
$answers = [];

$spreadsheet = IOFactory::load('../public/planilha.xlsx');
$worksheet = $spreadsheet->getActiveSheet();

$logFile = 'debug_log.txt';
$fp = fopen($logFile, 'a');

$firstRow = true; // Variável para controlar se é a primeira linha (título) da coluna

foreach ($worksheet->getColumnIterator('A') as $column) {
    foreach ($column->getCellIterator() as $cell) {
        if (!$firstRow) {
            $questions[] = $cell->getValue();
            fwrite($fp, 'Loaded Question: ' . $cell->getValue() . PHP_EOL);
        }
        $firstRow = false;
    }
}

$firstRow = true; // Reinicializa para a próxima coluna

foreach ($worksheet->getColumnIterator('B') as $column) {
    foreach ($column->getCellIterator() as $cell) {
        if (!$firstRow) {
            $keywords[] = $cell->getValue();
            fwrite($fp, 'Loaded Keyword: ' . $cell->getValue() . PHP_EOL);
        }
        $firstRow = false;
    }
}

$firstRow = true; // Reinicializa para a próxima coluna

foreach ($worksheet->getColumnIterator('C') as $column) {
    foreach ($column->getCellIterator() as $cell) {
        if (!$firstRow) {
            $answers[] = $cell->getValue();
            fwrite($fp, 'Loaded Answer: ' . $cell->getValue() . PHP_EOL);
        }
        $firstRow = false;
    }
}

fclose($fp);

$chatbot = new Chatbot($questions, $keywords, $answers);

if (isset($_POST['question'])) {
    $question = $_POST['question'];
    $response = array('response' => 'Pergunta: foi enviada.');



} else  {
    $response = array('response' => 'Erro: Nenhuma pergunta foi enviada.');



    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}


?>