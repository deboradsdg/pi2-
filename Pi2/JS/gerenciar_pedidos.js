// Arquivo: Pi2/JS/gerenciar_pedidos.js

document.addEventListener('DOMContentLoaded', function() {
    const INTERVALO_RECARGA = 15000; // 15 segundos
    const LIMITE_SEGUNDOS = 1800;   // 30 minutos
    const listaPedidosBody = document.getElementById('lista-pedidos-body');

    // --- FUNÇÕES DE CRONÔMETRO ---

    // Formata o tempo (segundos totais -> HH:MM:SS ou MM:SS)
    function formatarTempo(totalSegundos) {
        const horas = Math.floor(totalSegundos / 3600);
        const minutos = Math.floor((totalSegundos % 3600) / 60);
        const segundos = totalSegundos % 60;
        
        const minutosFormatados = String(minutos).padStart(2, '0');
        const segundosFormatados = String(segundos).padStart(2, '0');
        
        if (horas > 0) {
            return `${String(horas).padStart(2, '0')}:${minutosFormatados}:${segundosFormatados}`;
        }
        return `${minutosFormatados}:${segundosFormatados}`;
    }

    // Inicia e mantém a contagem de um único cronômetro
    function iniciarCronometro(element) {
        let tempoDecorrido = parseInt(element.dataset.startTime);
        const status = element.dataset.status;
        
        // Se o pedido estiver finalizado ou cancelado, apenas exibe o tempo total e para
        if (status === 'Finalizado' || status === 'Cancelado') {
            element.textContent = formatarTempo(tempoDecorrido);
            return;
        }

        // Função que executa a cada segundo
        function tick() {
            tempoDecorrido++;
            element.textContent = formatarTempo(tempoDecorrido);
            
            // Lógica de COR VERMELHA (Se passar de 30 minutos)
            if (tempoDecorrido > LIMITE_SEGUNDOS) {
                // Adiciona a classe CSS 'tempo-critico' para colorir
                element.classList.add('tempo-critico'); 
            } else {
                element.classList.remove('tempo-critico');
            }
        }

        tick(); 
        // Armazena o ID do intervalo para possível limpeza futura (boa prática)
        element.dataset.intervalId = setInterval(tick, 1000);
    }
    
    // Inicia todos os cronômetros na tabela (Chamada após cada recarga)
    function iniciarTodosCronometros() {
        // Primeiro, limpa todos os intervalos existentes para evitar contagens duplas
        document.querySelectorAll('.cronometro').forEach(element => {
            if (element.dataset.intervalId) {
                clearInterval(parseInt(element.dataset.intervalId));
            }
        });

        // Em seguida, inicia a contagem para todos os elementos .cronometro
        document.querySelectorAll('.cronometro').forEach(iniciarCronometro);
    }

    // --- FUNÇÃO DE AUTO-RECARGA (AJAX) ---
    
    function recarregarPedidos() {
        // O fetch chama o script que retorna o HTML do corpo da tabela
        fetch('carregar_pedidos.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Falha ao carregar pedidos: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                // Substitui o corpo da tabela pelo novo HTML
                listaPedidosBody.innerHTML = html; 
                
                // CRÍTICO: Inicia os cronômetros nos novos elementos HTML
                iniciarTodosCronometros();
            })
            .catch(error => {
                console.error("Erro na recarga automática:", error);
            });
    }

    // --- INICIALIZAÇÃO ---
    
    // 1. Inicia os cronômetros na primeira carga da página
    iniciarTodosCronometros();
    
    // 2. Inicia a recarga automática a cada 15 segundos
    setInterval(recarregarPedidos, INTERVALO_RECARGA);
});