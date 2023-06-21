<?php

require('Anao.php');

//PREVENÇÃO DE ERRO #1: Executar comando php de forma errada
if ($argc != 2) {
    echo "\033[H\033[2J";
    echo "Comando incorreto!\n\nUtilize a seguinte sintaxe: php {$argv[0]} <endereco-servico-dados>\n\nGeralmente é: http://127.0.0.1:8000\n";
    exit(1);
}

//CONSTANTES E FUNÇÕES AUXILIARES
$message = '';
$url = $argv[1];
$urlRestart = $argv[1];

$urls = GET_URLS($url);

function options()
{
    $options = [
        1 => 'Verificar estoque',
        2 => 'Criar novo anão',
        3 => 'Alterar um anão',
        4 => 'Apagar um anão',
        0 => 'Sair do sistema',
    ];
    return $options;
}

function restartUrl()
{
    global $url;
    global $urlRestart;
    $url = $urlRestart;
}

function hasMessage($value, $situation = 1)
{
    global $message;
    if (strlen($message) > 0 && $situation == 1) {
        $message = "Aviso: $value\n\n";
    } elseif ($situation == 0) {
        $message = "Resultado:\n$value\n----------------------------------------------\n";
    }
    return true;
}

//MENU
function menu()
{
    global $message;

    echo "\033[H\033[2J";

    echo $message;

    echo "\n=============== SISTEMA DE VENDA DE ANÕES ===============\n\n";
    foreach (options() as $key => $value) {
        echo $key . " - " . $value . "\n";
    }
    echo "----------------------------------------------\n";
    echo "Escolha: ";
    $escolha = (int)trim(readline());
    return $escolha;
}

//REDIRECIONAR ESCOLHA
do {
    $choice = menu();
    switch ($choice) {
        case 1:
            GET("baldes");
            break;
        case 2:
            create();
            break;
        case 3:
            update();
            break;
        case 4:
            delete();
            break;
        case 0:
            echo "\033[H\033[2J";
            echo "O sistema foi encerrado! Obrigado por apoiar nosso projeto ❤";
            exit(0);
            break;
        default:
            hasMessage("Escolha uma opção disponível!");
            break;
    }
} while ($choice != 0);

//FUNÇÕES PRINCIPAIS
function create()
{
    restartUrl();
    echo "\033[H\033[2J";
    echo "Iniciando sistema de cadastro de anões\n\n";

    echo "Digite o nome do seu anão: ";
    $name = (string)readline();
    echo "\nDigite o tipo do seu anão: ";
    $type = (string)readline();
    echo "\nDigite a idade do seu anão: ";
    $age = (int)readline();
    echo "\nDigite o preço do seu anão: ";
    $price = (float)readline();

    $anao = new Anao($name, $type, $age, $price);

    $res = request(
        _url('balde', [['{balde}', $name]]),
        [
            [CURLOPT_CUSTOMREQUEST, 'PUT'],
            [CURLOPT_HTTPHEADER, array('Content-Type: application/json')],
            [CURLOPT_POSTFIELDS, json_encode(['usuario' => "$name"])]
        ]
    );

    if ($res["codigo"] == 201) {
        hasMessage("O anão $name foi criado com sucesso", 0);
    } else {
        hasMessage($res["codigo"], 1);
    }

    restartUrl();

    sendData($name, $anao);
}

function delete()
{
    global $message;

    restartUrl();
    echo "\033[H\033[2J";
    echo "Iniciando sistema de deletar anões\n\n";


    if (!empty(GET("baldes"))) {

        GET("baldes");

        echo "\n" . $message;

        echo "Escolha qual o anão que deseja deletar: ";
        $choice = (string)readline();
        $delete = '';

        foreach (GET("baldes") as $value) {
            if ($value["id"] == $choice) {
                $delete = $value;
            }
        }

        $res = request(
            _url('objeto', [['{balde}', $delete["nome"]], ['{chave}', $delete["id"]]]),
            [[CURLOPT_CUSTOMREQUEST, 'DELETE']]
        );

        restartUrl();

        sendDelete($value);

        if ($res["codigo"] == 200) {
            hasMessage("O anão \"" . $value["nome"] . "\" foi deletado com sucesso", 0);
        } else {
            hasMessage($res["codigo"], 1);
        }
    } else {
        hasMessage("Não possui anões para serem deletados", 1);
    }
}

function update()
{
    global $message;

    restartUrl();
    echo "\033[H\033[2J";
    echo "Iniciando sistema de atualizar anões\n\n";


    if (!empty(GET("baldes"))) {

        GET("baldes");

        echo "\n" . $message;

        echo "Escolha qual o anão que deseja atualizar: ";
        $choice = (string)readline();
        $update = '';

        foreach (GET("baldes") as $value) {
            if ($value["id"] == $choice) {
                $update = $value;
            }
        }

        echo "Digite o nome do seu anão: ";
        $name = (string)readline();
        echo "\nDigite o tipo do seu anão: ";
        $type = (string)readline();
        echo "\nDigite a idade do seu anão: ";
        $age = (int)readline();
        echo "\nDigite o preço do seu anão: ";
        $price = (float)readline();

        $object = new Anao($name, $type, $age, $price);

        $res = request(
            _url('objeto', [['{balde}', $update["nome"]], ['{chave}', $update["id"]]]),
            [
                [CURLOPT_CUSTOMREQUEST, 'PUT'],
                [CURLOPT_HTTPHEADER, array('Content-Type: application/json')],
                [CURLOPT_POSTFIELDS, json_encode(['usuario' => "$name", 'valor' => json_encode($object)])]
            ]
        );

        if ($res["codigo"] == 200) {
            hasMessage("O objeto foi atulizado para \"" . $name . "\" ", 0);
        } else {
            hasMessage($res["codigo"], 1);
        }
    } else {
        hasMessage("Não possui anões para serem atualizados", 1);
    }
}

//FUNÇÕES DE REQUISIÇÃO
function GET_URLS($url)
{
    $resp = request($url);
    $urls = json_decode($resp['corpo'], $associative = true, flags: JSON_THROW_ON_ERROR);
    return $urls;
}

function GET($scope)
{
    restartUrl();
    global $url;
    global $urls;
    $res = request($url . $urls[$scope]);

    if ($res['codigo'] == 200) {
        $dataJson = json_decode($res['corpo'], true);
        $string = '';
        foreach ($dataJson as $value) {
            $string .= $value["id"] . ' - ' . $value["nome"] . "\n";
            $data[] = ["id" => $value["id"], "nome" => $value["nome"]];
        }
        hasMessage($string, 0);
    } else {
        hasMessage("Erro ao obter os dados dos anões.\nErro: {$res['codigo']}: {$res['corpo']}\n");
    }
    return $data;
}

function sendData($key, $object)
{
    $res = request(
        _url('balde', [['{balde}', $key]]),
        [
            [CURLOPT_CUSTOMREQUEST, 'POST'],
            [CURLOPT_HTTPHEADER, array('Content-Type: application/json')],
            [CURLOPT_POSTFIELDS, json_encode(['usuario' => "$key", 'valor' => json_encode($object)])]
        ]
    );

    if ($res["codigo"] != 201) {
        hasMessage("\nSendData: " . $res["codigo"], 1);
    }
}

function sendDelete($value)
{
    $res = request(
        _url('balde', [['{balde}', $value["nome"]]]),
        [[CURLOPT_CUSTOMREQUEST, 'DELETE']]
    );

    if ($res["codigo"] != 200) {
        hasMessage("\nSendDelete: " . $res["codigo"], 1);
    }
}

function _url(string $url_key, array $substituicoes = [])
{
    global $url, $urls;
    $url = $url;
    $url .= $urls[$url_key];
    foreach ($substituicoes as [$var, $val]) {
        $url = str_replace($var, $val, $url);
    }
    return $url;
}

//ENVIAR REQUISIÇÃO
function request($url, $curl_options = [])
{
    // Cria array para guardar as informações da resposta
    $resposta = [];

    // Inicializa o canal de comunicação
    $ch = curl_init($url);
    // Esta opção configura o cURL para retornar o valor da resposta, em vez de
    // apenas exibí-lo na tela.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // Esta opção configura o cURL para preservar os cabeçalhos na resposta, em
    // vez de descartá-los.
    curl_setopt($ch, CURLOPT_HEADER, 1);
    // Atribui as demais opções passadas como parâmetro.
    foreach ($curl_options as [$opt, $val]) {
        curl_setopt($ch, $opt, $val);
    }

    // Envia a requisição HTTP e retorna o corpo da resposta HTTP
    $str_resp = curl_exec($ch);
    // Acessa informações sobre a resposta
    $info = curl_getinfo($ch);
    // Extrai o código de estado HTTP
    $resposta['codigo'] = $info['http_code'];
    // Extrai os cabecalhos da resposta HTTP
    $resposta['cabecalhos'] = substr($str_resp, 0, $info['header_size']);
    // Extrai o corpo da resposta HTTP
    $resposta['corpo'] = substr($str_resp, $info['header_size']);
    // Extrai possível erro na requisição
    $resposta['erro'] = curl_error($ch);
    // Fecha o canal de comunicação
    curl_close($ch);

    // Retorna a resposta e as informações
    return $resposta;
}
