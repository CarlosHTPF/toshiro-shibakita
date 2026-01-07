<?php
/*************************************************
 * BACKEND (API)
 *************************************************/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Content-Type: application/json; charset=UTF-8");
    ini_set("display_errors", 1);
    error_reporting(E_ALL);

    // ========================
    // CONFIG BANCO
    // ========================
    $servername = "54.234.153.24";
    $username   = "app_user";
    $password   = getenv("DB_PASS");
    $database   = "meubanco";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["erro" => "Falha na conexão com o banco"]);
        exit;
    }

    // ========================
    // RECEBER DADOS
    // ========================
    $input = json_decode(file_get_contents("php://input"), true);

    $nome     = trim($input["nome"] ?? "");
    $endereco = trim($input["endereco"] ?? "");
    $cidade   = trim($input["cidade"] ?? "");
    $cpf      = trim($input["cpf"] ?? "");

    if (empty($nome) || empty($cpf)) {
        http_response_code(400);
        echo json_encode(["erro" => "Nome e CPF são obrigatórios"]);
        exit;
    }

    // ========================
    // 1️⃣ VERIFICAR / CRIAR CLIENTE
    // ========================
    $stmt = $conn->prepare(
        "SELECT cliente_id FROM clientes WHERE cpf = ?"
    );
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $clienteID = $result->fetch_assoc()["cliente_id"];
    } else {
        $stmtInsert = $conn->prepare(
            "INSERT INTO clientes (nome_completo, endereco, cidade, cpf)
             VALUES (?, ?, ?, ?)"
        );
        $stmtInsert->bind_param(
            "ssss",
            $nome,
            $endereco,
            $cidade,
            $cpf
        );
        $stmtInsert->execute();
        $clienteID = $stmtInsert->insert_id;
        $stmtInsert->close();
    }
    $stmt->close();

    // ========================
    // 2️⃣ CRIAR PEDIDO
    // ========================
    $valorTotal     = rand(20, 300);
    $formaPagamento = "PIX";
    $statusPedido   = "PAGO";

    $stmtPedido = $conn->prepare(
        "INSERT INTO pedidos (cliente_id, valor_total, forma_pagamento, status_pedido)
         VALUES (?, ?, ?, ?)"
    );
    $stmtPedido->bind_param(
        "idss",
        $clienteID,
        $valorTotal,
        $formaPagamento,
        $statusPedido
    );
    $stmtPedido->execute();

    $pedidoID = $stmtPedido->insert_id;
    $stmtPedido->close();

    $conn->close();

    // ========================
    // RESPOSTA
    // ========================
    echo json_encode([
        "mensagem"   => "Pedido criado com sucesso",
        "cliente_id" => $clienteID,
        "pedido_id"  => $pedidoID,
        "valor"      => $valorTotal
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Mini Mercado</title>
</head>
<body>

<h2>Sistema de Mini Mercado</h2>

<form id="pedidoForm">
    <label>Nome:</label><br>
    <input type="text" id="nome" required><br><br>

    <label>Endereço:</label><br>
    <input type="text" id="endereco"><br><br>

    <label>Cidade:</label><br>
    <input type="text" id="cidade"><br><br>

    <label>CPF:</label><br>
    <input type="text" id="cpf" required><br><br>

    <button type="submit">Criar Pedido</button>
</form>

<p id="resultado"></p>

<script>
document.getElementById("pedidoForm").addEventListener("submit", function(e) {
    e.preventDefault();

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nome: document.getElementById("nome").value,
            endereco: document.getElementById("endereco").value,
            cidade: document.getElementById("cidade").value,
            cpf: document.getElementById("cpf").value
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.erro) {
            document.getElementById("resultado").innerHTML = data.erro;
            return;
        }
        document.getElementById("resultado").innerHTML =
            "<strong>Pedido criado!</strong><br>" +
            "Pedido ID: " + data.pedido_id +
            "<br>Valor: R$ " + data.valor;
    })
    .catch(() => {
        document.getElementById("resultado").innerHTML =
            "Erro ao processar pedido";
    });
});
</script>

</body>
</html>
