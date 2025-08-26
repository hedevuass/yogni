<div class="modal fade" id="modalEditPromotion" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $promotion['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <?= $specialization_html ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-price-id="<?= $price_id ?>" id="promotionEdit">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    console.log('script is working');
    document.body.addEventListener('shown.bs.modal', (event) => {
        console.log('modal is working');
        const modal = event.target; // Модальное окно, которое открылось
        if (modal.id === 'modalEditPromotion') {
            console.log('ModalEditPromotion is shown!');
            modal.querySelectorAll('.toggle-children').forEach(button => {
                button.addEventListener('click', () => {
                    const target = modal.querySelector(button.dataset.target);
                    if (target) {
                        target.classList.toggle('d-none');
                        button.textContent = target.classList.contains('d-none') ? '+' : '-';
                    }
                });
            });
        }
    });
});
</script>
