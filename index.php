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
        echo json_encode(["erro" => "Falha na conexão"]);
        exit;
    }

    // ========================
    // RECEBER DADOS
    // ========================
    $input = json_decode(file_get_contents("php://input"), true);

    $nome     = $input["nome"]     ?? null;
    $endereco = $input["endereco"] ?? null;
    $cidade   = $input["cidade"]   ?? null;
    $cpf      = $input["cpf"]      ?? null;

    if (!$nome || !$cpf) {
        http_response_code(400);
        echo json_encode(["erro" => "Nome e CPF são obrigatórios"]);
        exit;
    }

    $host = gethostname();

    // ========================
    // 1️⃣ VERIFICAR CLIENTE
    // ========================
    $stmt = $conn->prepare("SELECT ClienteID FROM CRM WHERE Cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $clienteID = $result->fetch_assoc()["ClienteID"];
    } else {
        $stmtInsert = $conn->prepare(
            "INSERT INTO CRM (NomeCompleto, Endereco, Cidade, Cpf, Host)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmtInsert->bind_param("sssss", $nome, $endereco, $cidade, $cpf, $host);
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
        "INSERT INTO PEDIDOS (ClienteID, ValorTotal, FormaPagamento, StatusPedido)
         VALUES (?, ?, ?, ?)"
    );
    $stmtPedido->bind_param("idss", $clienteID, $valorTotal, $formaPagamento, $statusPedido);
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
        "valor"      => $valorTotal,
        "host"       => $host
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Mercado</title>
</head>
<body>

<h2>Sistema de Mercado</h2>

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
        document.getElementById("resultado").innerHTML =
            "Pedido ID: " + data.pedido_id +
            "<br>Valor: R$ " + data.valor +
            "<br>Servidor: " + data.host;
    })
    .catch(() => {
        document.getElementById("resultado").innerHTML = "Erro ao processar pedido";
    });
});
</script>

</body>
</html>
