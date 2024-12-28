<?php

$data = [];

/**
 * @return array
 */
function get_document_by_id($id): array
{
    global $data_base;
    return $data_base->selectRow("SELECT * FROM dt_o_proekte WHERE id = ?", [$id]);
}

// Получаем данные для id с 1 по 5
$document_data_1 = get_document_by_id(1);
$document_data_2 = get_document_by_id(2);
$document_data_3 = get_document_by_id(3);
$document_data_4 = get_document_by_id(4);
$document_data_5 = get_document_by_id(5);

// Генерируем HTML
$data['documents'] = '
<div class="faq-item">
  <button class="faq-question">
    <div>' . htmlspecialchars_decode($document_data_1['title']) . '</div>
    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="">
  </button>
  <div class="faq-answer">
    <p>' . htmlspecialchars_decode($document_data_1['content']) . '</p>
  </div>
</div>

<div class="faq-item">
  <button class="faq-question">
    <div>' . htmlspecialchars_decode($document_data_2['title']) . '</div>
    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="">
  </button>
  <div class="faq-answer">
    <p>' . htmlspecialchars_decode($document_data_2['content']) . '</p>
  </div>
</div>

<div class="faq-item">
  <button class="faq-question">
    <div>' . htmlspecialchars_decode($document_data_3['title']) . '</div>
    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="">
  </button>
  <div class="faq-answer">
    <p>' . htmlspecialchars_decode($document_data_3['content']) . '</p>
  </div>
</div>

<div class="faq-item">
  <button class="faq-question">
    <div>' . htmlspecialchars_decode($document_data_4['title']) . '</div>
    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="">
  </button>
  <div class="faq-answer">
    <p>' . htmlspecialchars_decode($document_data_4['content']) . '</p>
  </div>
</div>

<div class="faq-item">
  <button class="faq-question">
    <div>' . htmlspecialchars_decode($document_data_5['title']) . '</div>
    <img src="../assets/img/cabinet/arrow-faq-down.svg" alt="">
  </button>
  <div class="faq-answer">
    <p>' . htmlspecialchars_decode($document_data_5['content']) . '</p>
  </div>
</div>
';


$HTML = html_replace([
    'title' => 'ЮРФАК - О проекте'
], $HTML);

$HTML = html_explode($data, $HTML);