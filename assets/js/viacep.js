(() => {
    const campoCep = document.querySelector('[data-viacep]');
    if (!campoCep) return;

    const campos = {
        logradouro: document.querySelector('[name="logradouro"]'),
        bairro: document.querySelector('[name="bairro"]'),
        cidade: document.querySelector('[name="cidade"]'),
        uf: document.querySelector('[name="uf"]'),
    };
    const feedback = document.querySelector('[data-viacep-feedback]');

    campoCep.addEventListener('blur', async () => {
        const cep = campoCep.value.replace(/\D/g, '');
        if (!cep) return;
        if (!/^\d{8}$/.test(cep)) {
            if (feedback) feedback.textContent = 'Informe um CEP válido com 8 números.';
            return;
        }

        if (feedback) feedback.textContent = 'Consultando CEP...';
        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                headers: { Accept: 'application/json' },
            });
            if (!resposta.ok) throw new Error('Serviço ViaCEP indisponível.');
            const dados = await resposta.json();
            if (dados.erro) throw new Error('CEP não encontrado.');

            for (const chave of Object.keys(campos)) {
                if (campos[chave] && dados[chave]) campos[chave].value = dados[chave];
            }
            if (feedback) feedback.textContent = 'Endereço preenchido pelo ViaCEP. Confira os dados.';
        } catch (erro) {
            if (feedback) feedback.textContent = erro.message || 'Não foi possível consultar o CEP.';
        }
    });
})();
