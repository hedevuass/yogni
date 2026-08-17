<style>
  .table-responsive {
    overflow-x: auto;
    /* горизонтальная прокрутка при нехватке места */
  }

  #specialists,
  #deleted-specialists {
    min-width: 1400px;
    /* минимальная ширина всей таблицы для десктопа и моба */
    table-layout: fixed;
    /* колонки фиксированы по ширине */
  }

  #specialists th,
  #specialists td:not(:has(img)),
  #deleted-specialists th,
  #deleted-specialists td:not(:has(img)) {
    white-space: nowrap;
    /* чтобы текст не переносился */
    overflow: hidden;
    text-overflow: ellipsis;
    /* если не помещается, ставим троеточие */
  }
</style>
<div id="specialistPage" class="wrapper">
  <p class="h1 text-center">
    Список специалистов
    <button type="button" class="btn btn-success modalAdd">
      Добавить специалиста
    </button>
  </p>

  <div class="table-responsive">
    <div class="container-fluid">
      <table id="specialists" class="table table-striped align-middle" style="width:100%; table-layout:fixed;">
        <thead>
          <tr>
            <td style="width:4%;">ID</td>
            <td style="width:8%;">Фотография</td>
            <td style="width:12%;">Наименование</td>
            <td style="width:8%;">Email</td>
            <td style="width:8%;">Телефон</td>
            <td style="width:8%;">Категория</td>
            <td style="width:6%;">Город</td>
            <td style="width:6%;">Оценка</td>
            <td style="width:6%;">Рейтинг</td>
            <td style="width:8%;">Регистрация</td>
            <td style="width:8%;">Активация</td>
            <td style="width:16%;">Действие</td>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <p class="h1 text-center mt-5">
    Удаленные специалисты
  </p>

  <div class="table-responsive">
    <div class="container-fluid">

      <table id="deleted-specialists" class="table table-striped align-middle" style="width:100%; table-layout:fixed;">
        <thead>
          <tr>
            <td style="width:4%;">ID</td>
            <td style="width:8%;">Фотография</td>
            <td style="width:12%;">Наименование</td>
            <td style="width:8%;">Email</td>
            <td style="width:8%;">Телефон</td>
            <td style="width:8%;">Категория</td>
            <td style="width:6%;">Город</td>
            <td style="width:6%;">Оценка</td>
            <td style="width:6%;">Рейтинг</td>
            <td style="width:8%;">Регистрация</td>
            <td style="width:8%;">Активация</td>
            <td style="width:16%;">Удаление профиля</td>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="invoiceModalLabel">Отправка счёта на оплату</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="invoice-form">
          <div class="mb-3">
            <label for="service-name" class="form-label">Услуга</label>
            <input type="text" class="form-control" id="service-name" name="service_name" readonly>
          </div>
          <div class="mb-3 d-flex gap-3">
            <div class="flex-fill">
              <label for="period" class="form-label">Период</label>
              <input type="text" class="form-control" id="period" name="period" readonly>
            </div>
            <div class="flex-fill">
              <label for="place" class="form-label">Место</label>
              <input type="text" class="form-control" id="place" name="place" readonly>
            </div>
          </div>
          <div class="mb-3">
            <label for="amount" class="form-label">Сумма</label>
            <input type="text" class="form-control" id="amount" name="amount" readonly value="Будет рассчитана">
          </div>
          <div class="mb-3">
            <label for="inn" class="form-label">ИНН</label>
            <input type="text" class="form-control" id="inn" name="inn" required>
          </div>
          <div class="text-end">
            <button type="submit" style="width: 140px;" class="btn btn-primary">Отправить счёт</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>