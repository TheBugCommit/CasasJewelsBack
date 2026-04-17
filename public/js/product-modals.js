document.addEventListener('DOMContentLoaded', function() {

    const editModal = document.getElementById('editProductModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            const dataset = btn.dataset;

            const form = document.getElementById('editProductForm');
            form.action = `/admin/products/${dataset.id}/edit`;

            document.getElementById('editCsrfToken').value = dataset.csrfToken || '';
            document.getElementById('editName').value        = dataset.name;
            document.getElementById('editPrice').value       = dataset.price;
            document.getElementById('editDescription').value = dataset.description;

            const categorySelect = document.getElementById('editCategory');
            Array.from(categorySelect.options).forEach(opt => {
                opt.selected = opt.value === dataset.category;
            });

            const imageDiv = document.getElementById('editCurrentImage');
            if (dataset.image) {
                imageDiv.innerHTML = `<img src="${dataset.image}" class="img-thumbnail" style="height:60px;" alt="Imatge actual"> <small class="text-muted">Imatge actual</small>`;
            } else {
                imageDiv.innerHTML = '<small class="text-muted">Sense imatge actual</small>';
            }
        });
    }

    const deleteForms = document.querySelectorAll('.js-delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const productName = this.dataset.name;
            if (confirm(`Segur que vols eliminar «${productName}»?`)) {
                this.submit();
            }
        });
    });
});
