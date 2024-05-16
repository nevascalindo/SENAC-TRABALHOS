<?php //index.php

require_once("./models/Tarefas.php");
require_once("./databases/MariaDb.php");

function dd($valor)
{
    echo "<pre>";
    print_r($valor);
    echo "</pre>";
    die();
}

$metodo = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];
$rota = explode('/', $path);

$status_code = 200;
$resposta = [
    "status" => true,
    "mensagem" => "",
];





if ($rota[1] == "tarefas") {
    $database = new MariaDb();
    $tarefa = new Tarefa($database->dbConnection());
    switch ($metodo) {
        case "GET":
            if (isset($rota[2]) && is_numeric($rota[2])) {
                $result = $tarefa->getUserById($rota[2]);
                if (count($result) == 0) {
                    $status_code = 404;
                    $resposta['status'] = false;
                    $resposta['mensagem'] = "Tarefa não encontrada";
                    break;
                }
                $resposta['dados'] = $result;
            } else {
                $resposta['dados'] = $tarefa->getAll();
            }
            break;
        case "DELETE": //  /tarefas/2
            if (isset($rota[2]) && is_numeric($rota[2])) {
                $tarefa->id_tarefa = $rota[2];
                $result =  $tarefa->remove();
                if ($result === false) {
                    $status_code = 403;
                    $resposta['status'] = false;
                    $resposta['mensagem'] = "Erro ao tentar remover a tarefa";
                    break;
                }
                if ($result === true) {
                    $status_code = 200;
                    $resposta['status'] = true;
                    $resposta['mensagem'] = "DELETADO PATRAO";
                    break;
                }
            } else {
                $status_code = 403;
                $resposta['status'] = false;
                $resposta['mensagem'] = "Tarefa não foi informada";
            }
            break;
        case "POST":
            $parametros = file_get_contents('php://input');
            $parametros = (array) json_decode($parametros, true);
            $tarefa->nome = $parametros['nome'];
            $tarefa->descricao = $parametros['descricao'];
            $tarefa->id_usuario = $parametros['id_usuario'];

            if (!$tarefa->create()) {
                $status_code = 403;
                $resposta['status'] = false;
                $resposta['mensagem'] = "Erro ao tentar cadastrar a tarefa";
                break;
            }
            $resposta['mensagem'] = "Tarefa cadastrada com sucesso!";
            break;

        case "PUT":
            $parametros = file_get_contents('php://input');
            $parametros = (array) json_decode($parametros, true);
            $tarefa->id_tarefa = $rota[2];
            $tarefa->nome = $parametros['nome'];
            $tarefa->descricao = $parametros['descricao'];
            $tarefa->id_usuario = $parametros['id_usuario'];    

            if (!$tarefa->update()) {
                $status_code = 403;
                $resposta['status'] = false;
                $resposta['mensagem'] = "Erro ao tentar atualizar a tarefa";
                break;
            }
            $resposta['mensagem'] = "Tarefa atualizada com sucesso!";
            break;
        default:
            $status_code = 403;
            $resposta['status'] = false;
            $resposta['mensagem'] = "Método não permitido";
    }
} else {
    $status_code = 403;
    $resposta['status'] = false;
    $resposta['mensagem'] = "Não foi possível atender a sua requisição!";
}


http_response_code($status_code);
header("Content-Type: application/json");
echo json_encode($resposta);
