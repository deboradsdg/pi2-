// Arquivo: Pi2/JS/Principal.js

// Garante que o código só é executado após o HTML estar completamente carregado
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Definições de Constantes (Devem ser IDs do HTML)
    const pizzas = document.querySelectorAll('.pizza');
    const popup = document.getElementById('popup');
    const btnFechar = document.getElementById('fechar');
    const itensCarrinho = document.getElementById('itens');
    const totalEl = document.getElementById('total');
    const btnComprar = document.getElementById('comprar'); 
    
    // Variáveis injetadas pelo PHP (Definidas no Principal.php)
    // ATENÇÃO: Elas são definidas no PHP, mas usadas aqui. O PHP deve injetar:
    // const clienteLogado = true/false;
    // const userId = id_do_cliente / null;

    // --- FUNÇÃO PARA COLETAR DADOS DO CARRINHO ---
    function coletarItensDoCarrinho() {
        const itens = [];
        pizzas.forEach(pizza => {
            const qtd = parseInt(pizza.querySelector('.qtd').textContent);
            if (qtd > 0) {
                // Adiciona o nome do produto para exibição no carrinho
                const nomeProduto = pizza.dataset.nome; 
                
                itens.push({
                    id_produto: pizza.dataset.id,
                    nome: nomeProduto, // Adicionado para exibição
                    quantidade: qtd,
                    preco_unitario: parseFloat(pizza.dataset.preco)
                });
            }
        });
        return itens;
    }

    // --- FUNÇÃO DE ATUALIZAÇÃO DA INTERFACE ---
    function atualizarCarrinho() {
        let total = 0;
        let conteudo = '';
        let temPizza = false;
        
        const itensAtuais = coletarItensDoCarrinho();

        itensAtuais.forEach(item => {
            temPizza = true;
            const subtotal = item.preco_unitario * item.quantidade;
            total += subtotal;
            // Exibe o nome que foi adicionado na função coletarItensDoCarrinho
            conteudo += `<p>${item.nome} x${item.quantidade} — R$${subtotal.toFixed(2).replace('.', ',')}</p>`; 
        });

        if (temPizza) {
            itensCarrinho.innerHTML = conteudo;
            totalEl.textContent = `Total: R$${total.toFixed(2).replace('.', ',')}`; // Formatação BR
            popup.classList.add('show');
        } else {
            popup.classList.remove('show');
        }
    }
    
    // --- FUNÇÃO PARA SALVAR CARRINHO NA SESSÃO VIA AJAX ---
    function salvarCarrinhoNaSessao(itensCarrinho) {
        // Caminho do fetch corrigido: 'PHP/salvarcarrinho.php' (assumindo Principal.php está na raiz)
        return fetch('salvarcarrinho.php', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ itens: itensCarrinho })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor. Status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                return true; 
            } else {
                throw new Error(data.message || 'Erro desconhecido ao salvar carrinho.');
            }
        });
    }

    // --- LISTENERS DE QUANTIDADE (+ e -) ---
    pizzas.forEach(pizza => {
        const menos = pizza.querySelector('.decr');
        const mais = pizza.querySelector('.incr');
        const qtd = pizza.querySelector('.qtd');

        menos.addEventListener('click', () => {
            let valor = parseInt(qtd.textContent);
            if (valor > 0) {
                qtd.textContent = valor - 1;
                atualizarCarrinho();
            }
        });

        mais.addEventListener('click', () => {
            let valor = parseInt(qtd.textContent);
            qtd.textContent = valor + 1;
            atualizarCarrinho();
        });
    });

    // --- LISTENER DO BOTÃO COMPRAR (Função Final) ---
    if (btnComprar) {
        btnComprar.addEventListener('click', () => {

            console.log("Botão COMPRAR clicado");
            
            // 1. Validação de Itens
            const itensCarrinho = coletarItensDoCarrinho();
            if (itensCarrinho.length === 0) {
                alert("Seu carrinho está vazio!");
                return;
            }

            // 2. Validação de Login (Variável injetada pelo PHP)
            if (typeof clienteLogado === 'undefined' || !clienteLogado) {
                alert("Você precisa estar logado para finalizar o pedido! Redirecionando...");
                window.location.href = '../HTML/Cadastro.html'; 
                return;
            }
            
            // 3. Salva na Sessão e Redireciona
            salvarCarrinhoNaSessao(itensCarrinho)
                .then(() => {
                    // Se o salvamento AJAX for bem-sucedido, redireciona para a confirmação
                   window.location.href = 'confirmarendereco.php';
                })
                .catch(error => {
                    console.error("Erro ao salvar carrinho:", error);
                    alert("Não foi possível iniciar o pedido. Verifique o console para detalhes.");
                });
        });
    } else {
        console.error("Erro JS: O botão 'Comprar' (id=comprar) não foi encontrado no DOM.");
    }
    
    // --- LISTENER FECHAR POPUP ---
    if (btnFechar) {
        btnFechar.addEventListener('click', () => {
            popup.classList.remove('show');
        });
    }
});