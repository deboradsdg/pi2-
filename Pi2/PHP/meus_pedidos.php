<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meus Pedidos</title>
<link rel="stylesheet" href="../CSS/style.css">
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #f4f4f4; }
    #status-atualizacao {
        font-size: 0.9em;
        color: #666;
        margin-top: 5px;
    }
</style>
</head>
<body>

<h2>Meus Pedidos</h2>
<p id="status-atualizacao">Última atualização: <span id="hora-atual"></span></p>

<table id="tabela-pedidos">
    <thead>
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Status</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="4">Carregando...</td></tr>
    </tbody>
</table>

<script>
async function carregarPedidos() {
    try {
        const resposta = await fetch('buscar_pedidos.php');
        if (!resposta.ok) throw new Error('Erro ao buscar pedidos');
        const pedidos = await resposta.json();

        const corpo = document.querySelector('#tabela-pedidos tbody');
        corpo.innerHTML = ''; // limpa a tabela

        if (pedidos.length === 0) {
            corpo.innerHTML = '<tr><td colspan="4">Você ainda não fez nenhum pedido.</td></tr>';
            return;
        }

        pedidos.forEach(pedido => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${pedido.id_pedido}</td>
                <td>${new Date(pedido.data_pedido).toLocaleString('pt-BR')}</td>
                <td>${pedido.status}</td>
                <td>R$ ${Number(pedido.valor_total).toFixed(2).replace('.', ',')}</td>
            `;
            corpo.appendChild(tr);
        });

        // Atualiza o horário da última atualização
        const agora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('hora-atual').textContent = agora;

    } catch (erro) {
        console.error(erro);
    }
}

// Carrega imediatamente e depois a cada 3 minutos (180000 ms)
carregarPedidos();
setInterval(carregarPedidos, 180000);
</script>

</body>
</html>

