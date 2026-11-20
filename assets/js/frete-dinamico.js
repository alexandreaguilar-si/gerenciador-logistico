(() => {
    const lista = document.querySelector('[data-produtos-lista]');
    const template = document.querySelector('#produto-frete-template');
    const botaoAdicionar = document.querySelector('[data-adicionar-produto]');
    if (!lista || !template || !botaoAdicionar) return;

    const adicionarLinha = () => {
        lista.append(template.content.cloneNode(true));
    };

    botaoAdicionar.addEventListener('click', adicionarLinha);
    lista.addEventListener('click', (evento) => {
        const botao = evento.target.closest('[data-remover-produto]');
        if (botao) botao.closest('.item-row')?.remove();
    });

    adicionarLinha();
})();
