<?php

$data = [];

/**
 * @return array
 */
function get_all_documents(): array
{
    global $data_base;
    return $data_base->selectAll("SELECT * FROM dt_o_proekte ORDER BY id ASC");
}

$documents = get_all_documents();

$documents_html = '';

foreach ($documents as $document) {
    $documents_html .= '
    <div class="faq-item">
      <button class="faq-question">
        <div>' . htmlspecialchars_decode($document['title']) . '</div>
        <img src="../assets/img/cabinet/arrow-faq-down.svg" class="default-arrow" alt="">
        <img src="../assets/img/cabinet/arrow-faq-down-hover.svg" class="hover-arrow" alt="">
      </button>
      <div class="faq-answer">
        <p>' . htmlspecialchars_decode($document['content']) . '</p>
      </div>
    </div>';
}

$data['documents'] = $documents_html;

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

$data['popup_forbidden_words'] = '<input type="hidden" id="popup-forbidden-words" value="'. $escaped_forbidden_words . '">';

$data['input_specialist_email'] = '<input type="hidden" name="user_email" value=""><input type="hidden" name="user_name" value="">';

$phone = '';
$data['popup_tel_field'] = '<input type="text" name="phone" class="phone-input" placeholder="Номер вашего телефона для связи" value="">';

$data['popup_email_field'] = '<input type="email" name="email" class="field-input" placeholder="Электронная почта" value="">';

$name = '';
$data['popup_name_field'] = '<input type="text" name="fio_or_company" class="field-input" placeholder="ФИО или название компании" value="">';

$specialist_identifier = '';
$specialist_link = 'https://' . $_SERVER['HTTP_HOST'] . '/specialist/' . $specialist_identifier;
$data['popup_page_link'] = '<input type="hidden" name="link" value="">';

$data['date'] = '<input type="hidden" name="date" value="' . date('d.m.Y') .'" />';

$randId = 'id' . bin2hex(random_bytes(6));
$data['captcha-support'] = '<div class="field captcha-field">
    <div class="captcha-row">
        <div class="captcha-left">
            <img id="captcha_' . $randId . '" src="/libs/securimage/securimage_show.php?id=' . $randId . '" alt="CAPTCHA" aria-hidden="false" />
            <button type="button" id="captcha_refresh_' . $randId . '" class="captcha-refresh" title="Обновить каптчу" aria-label="Обновить каптчу">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M21 12a9 9 0 10-3.2 6.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div class="captcha-right">
            <label class="captcha-label" for="captcha_code_' . $randId . '">Подтвердите, что вы не робот</label>
            <input class="captcha-input" id="captcha_code_' . $randId . '" name="captcha_code" type="text" placeholder="Введите цифры" autocomplete="off" />
            <input type="hidden" id="captcha_captcha_id_' . $randId . '" name="captcha_id" value="' . $randId . '" />
        </div>
    </div>
    <div class="captcha-error"></div>
</div>';

$HTML = html_replace([
    'title' => 'ЮРФАК - О проекте'
], $HTML);

$HTML = html_explode($data, $HTML);