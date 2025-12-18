<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Mercado</title>
</head>
<body>

<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

echo "<h3>Sistema de Mercado</h3>";
echo "Versão do PHP: " . phpversion() . "<br><br>";

// ========================
// CONFIG BANCO
// ========================
$servername = "54.234.153.24";
$username   = "app_user";
$password   = getenv("DB_PASS");
$database   = "meubanco";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// ========================
// DADOS DO CLIENTE (exemplo)
// ========================
$nome     = "Cliente Teste";
$endereco = "Rua Central, 123";
$cidade   = "São Paulo";
$cpf      = strval(rand(10000000000, 99999999999));
$host     = gethostname();

// ========================
// 1️⃣ CADASTRAR CLIENTE
// ========================
$stmtCliente = $conn->prepare(
    "INSERT INTO CRM (NomeCompleto, Endereco, Cidade, Cpf, Host)
     VALUES (?, ?, ?, ?, ?)"
);

$stmtCliente->bind_param(
    "sssss",
    $nome,
    $endereco,
    $cidade,
    $cpf,
    $host
);

$stmtCliente->execute();
$clienteID = $stmtCliente->insert_id;
$stmtCliente->close();

echo "Cliente cadastrado (ID): $clienteID <br>";

// ========================
// 2️⃣ CRIAR PEDIDO
// ========================
$valorTotal     = rand(10, 300);
$formaPagamento = "PIX";
$statusPedido   = "PAGO";

$stmtPedido = $conn->prepare(
    "INSERT INTO PEDIDOS (ClienteID, ValorTotal, FormaPagamento, StatusPedido)
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

echo "Pedido criado (ID): $pedidoID <br>";
echo "Atendido pelo host: $host <br>";

// ========================
$conn->close();
?>

</body>
</html>
