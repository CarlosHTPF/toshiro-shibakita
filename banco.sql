CREATE TABLE usuarios (
    usuario_id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(50) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    categoria_id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(100)
);

CREATE TABLE produtos (
    produto_id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(150),
    codigo_barras VARCHAR(50) UNIQUE,
    preco NUMERIC(10,2) NOT NULL,
    categoria_id INT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias (categoria_id)
);

CREATE TABLE estoque (
    estoque_id SERIAL PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    quantidade_minima INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_estoque_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos (produto_id)
);

CREATE TABLE itens_pedido (
    item_id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario NUMERIC(10,2) NOT NULL,

    CONSTRAINT fk_itens_pedido
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos (pedido_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_itens_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos (produto_id)
);

CREATE TABLE pagamentos (
    pagamento_id SERIAL PRIMARY KEY,
    pedido_id INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    valor NUMERIC(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'APROVADO',
    data_pagamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pagamentos_pedido
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos (pedido_id)
);

CREATE TABLE caixa (
    caixa_id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fechamento TIMESTAMP,
    saldo_inicial NUMERIC(10,2) NOT NULL,
    saldo_final NUMERIC(10,2),

    CONSTRAINT fk_caixa_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios (usuario_id)
);

CREATE TABLE movimentacoes_caixa (
    movimentacao_id SERIAL PRIMARY KEY,
    caixa_id INT NOT NULL,
    tipo VARCHAR(20) NOT NULL, -- ENTRADA / SAIDA
    descricao VARCHAR(150),
    valor NUMERIC(10,2) NOT NULL,
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mov_caixa
        FOREIGN KEY (caixa_id)
        REFERENCES caixa (caixa_id)
);

CREATE TABLE fornecedores (
    fornecedor_id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(14) UNIQUE,
    telefone VARCHAR(20),
    email VARCHAR(100)
);

CREATE TABLE entrada_produtos (
    entrada_id SERIAL PRIMARY KEY,
    fornecedor_id INT NOT NULL,
    data_entrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_total NUMERIC(10,2),

    CONSTRAINT fk_entrada_fornecedor
        FOREIGN KEY (fornecedor_id)
        REFERENCES fornecedores (fornecedor_id)
);

CREATE TABLE itens_entrada (
    item_entrada_id SERIAL PRIMARY KEY,
    entrada_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_custo NUMERIC(10,2) NOT NULL,

    CONSTRAINT fk_itens_entrada
        FOREIGN KEY (entrada_id)
        REFERENCES entrada_produtos (entrada_id),

    CONSTRAINT fk_itens_entrada_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos (produto_id)
);

