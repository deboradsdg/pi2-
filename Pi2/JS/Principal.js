// Principal.js
const pizzas = document.querySelectorAll('.pizza'); // Alterado de itens para pizzas para maior clareza
const popup = document.getElementById('popup');
const btnFechar = document.getElementById('fechar');
const itensCarrinho = document.getElementById('itens');
const totalEl = document.getElementById('total');
const btnComprar = document.getElementById('comprar'); // Novo Elemento

const clienteLogado = true;
// Variáveis injetadas pelo PHP no final do Principal.php:
// const clienteLogado = true/false;
// const userId = id_do_cliente / null;


// --- FUNÇÃO PARA COLETAR DADOS DO CARRINHO ---
// Usada tanto para atualizar a interface quanto para enviar ao PHP
function coletarItensDoCarrinho() {
    const itens = [];
    pizzas.forEach(pizza => {
        const qtd = parseInt(pizza.querySelector('.qtd').textContent);
        if (qtd > 0) {
            itens.push({
                id_produto: pizza.dataset.id,
                quantidade: qtd,
                preco_unitario: parseFloat(pizza.dataset.preco)
            });
        }
    });
    return itens;
}

// --- FUNÇÃO DE ATUALIZAÇÃO DA INTERFACE (Sua função original) ---
function atualizarCarrinho() {
    let total = 0;
    let conteudo = '';
    let temPizza = false;
    
    const itensAtuais = coletarItensDoCarrinho();

    itensAtuais.forEach(item => {
        temPizza = true;
        const subtotal = item.preco_unitario * item.quantidade;
        total += subtotal;
        conteudo += `<p>${item.id_produto} - ${item.nome} x${item.quantidade} — R$${subtotal.toFixed(2)}</p>`;
        // Nota: O nome não está no objeto do carrinho coletado, mas você pode usar o id para exibição
    });

    if (temPizza) {
        itensCarrinho.innerHTML = conteudo;
        totalEl.textContent = `Total: R$${total.toFixed(2).replace('.', ',')}`; // Formatação BR
        popup.classList.add('show');
    } else {
        popup.classList.remove('show');
    }
}

// --- FUNÇÃO PARA ENVIAR PEDIDO VIA AJAX ---
function enviarPedidoParaBanco(clienteId, itens) {
    // Exibe um loading ou desativa o botão aqui
    
    fetch('../PHP/finalizar_pedido.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cliente_id: clienteId,
            itens: itens
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Pedido finalizado com sucesso! ID do Pedido: ' + data.id_pedido);
            // Limpa o carrinho na interface
            pizzas.forEach(pizza => pizza.querySelector('.qtd').textContent = 0);
            atualizarCarrinho(); // Fecha o popup
            
            // REDIRECIONAMENTO 1: Para a página de acompanhamento
            window.location.href = `acompanhamento.php?pedido=${data.id_pedido}`;
            
        } else {
            alert('Erro ao finalizar pedido: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro de rede:', error);
        alert('Ocorreu um erro de comunicação. Tente novamente.');
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

// --- LISTENER DO BOTÃO COMPRAR (Verificação de Login) ---
// Principal.js (Dentro do listener do btnComprar)

btnComprar.addEventListener('click', () => {
    const itensCarrinho = coletarItensDoCarrinho();

    if (itensCarrinho.length === 0) {
        alert("Seu carrinho está vazio!");
        return;
    }

    if (!clienteLogado) {
        // Cliente NÃO logado: Redireciona imediatamente
        alert("Você precisa estar logado para finalizar o pedido! Redirecionando...");
        window.location.href = 'HTML/Cadastro.html'; // Caminho corrigido (Assumindo que está na pasta HTML/)
        return;
    }
    
    // 2. Cliente LOGADO: SALVA NA SESSÃO E REDIRECIONA APÓS O SUCESSO DO SALVAMENTO
    salvarCarrinhoNaSessao(itensCarrinho)
        .then(() => {
            // CORREÇÃO: O redirecionamento deve incluir a pasta PHP/
            window.location.href = 'PHP/confirmarendereco.php'; 
        })
        .catch(error => {
            // Se houver erro, exibe a mensagem de erro
            console.error("Erro ao salvar carrinho:", error);
            alert("Não foi possível iniciar o pedido. Verifique o console para detalhes.");
        });
});

// --- LISTENER FECHAR POPUP (Permanece o mesmo) ---
btnFechar.addEventListener('click', () => {
    popup.classList.remove('show');
});


// --- FUNÇÃO SALVAR CARRINHO NA SESSÃO ---
function salvarCarrinhoNaSessao(itensCarrinho) {
    
    // CORREÇÃO: O fetch deve usar o caminho completo ou relativo correto para o arquivo PHP
    // Se o Principal.php está na raiz, o caminho é PHP/salvarcarrinho.php
    return fetch('PHP/salvarcarrinho.php', { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ itens: itensCarrinho })
    })
    .then(response => {
        if (!response.ok) {
            // Se o PHP retornar status 404/500, lança um erro com a resposta do servidor
            throw new Error('Erro na resposta do servidor. Status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            return true; // Retorna true se a operação PHP foi bem-sucedida
        } else {
            // Se o PHP retornar JSON com status 'error'
            throw new Error(data.message || 'Erro desconhecido ao salvar carrinho.');
        }
    });
}