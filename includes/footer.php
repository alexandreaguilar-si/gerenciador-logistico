</main>
<footer class="app-footer py-4 mt-4">
    <div class="container-fluid px-lg-4 d-flex flex-column flex-sm-row justify-content-between gap-2">
        <span>Gerenciador Logístico</span>
        <span>Gestão simples e eficiente de entregas</span>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e($prefixo) ?>/assets/js/viacep.js"></script>
<script src="<?= e($prefixo) ?>/assets/js/frete-dinamico.js"></script>
<script>
document.querySelectorAll('[data-confirm]').forEach((elemento) => {
    elemento.addEventListener('click', (evento) => {
        if (!window.confirm(elemento.dataset.confirm || 'Confirma esta ação?')) {
            evento.preventDefault();
        }
    });
});
</script>
</body>
</html>
