<?php

namespace KreaLab\CommonBundle\Entity;

use KreaLab\CommonBundle\Model\BlankLifeLog as BlankLifeLogModel;

class BlankLifeLog extends BlankLifeLogModel
{
    protected $end_status   = null;
    protected $start_status = null;
    protected $penalty_sum  = null;

    // Админ назначает штраф за утерю бланка
    const AO_SET_PENALTY_FOR_OPERATOR = 'admin_set_penalty_for_operator';

    // Кладовщик создает бланки
    const S_CREATE_BLANK = 'stock_create_blank';
    // Кладовщик создает конверт для последующей передачи какому либо справковеду
    const S_CREATE_ENVELOPE_FOR_REFERENCEMAN = 'stockman_create_envelope_for_referenceman';
    // Кладовщик назначает конверт с бланками на справковеда
    const SR_CREATE_ENVELOP_STOCK_TO_REFERENCE = 'stock_create_envelop_stock_to_refer';
    // Кладовщик удаляет не найденные справковедом бланки
    const RS_DELETED_BY_STOCKMAN = 'stock_deleted_by_stock';
    // Кладовщик возвращает не найденный бланк справковеду
    const RS_APPOINTED_TO_REFERENCE = 'stock_appointed_to_refer';
    // Кладовщик принимает конверт с бланками возвращенный справковедом
    const RS_ACCEPT_REVERT_ENVELOP_ALL_BLANKS_FROM_REFERENCE = 'stock_accept_reverting_envelop_all_blanks_from_refer';
    // Кладовщик принимает бланк возвращенный справковедом
    const RS_ACCEPT_REVERT_BLANK_FROM_REFERENCE = 'stock_accept_reverting_blank_from_refer';
    // Кладовщик назначает конверт на оператора
    const SR_ASSIGN_ENVELOPE_TO_REFERENCEMAN = 'stockman_assign_envelope_to_referenceman';
    // Кладовщик повторно назначает конверт, @TODO: после удаления конверта от справковеда от справковеда ?
    const SR_ASSIGN_ENVELOPE_TO_REFERENCEMAN_AGAIN = 'stockman_assign_envelope_to_referenceman_again';
    // Кладовщик отменяет назначение конверта на справковеда
    const SR_REMOVE_REFERENCEMAN_FROM_ENVELOPE = 'stockman_remove_referenceman_from_envelope';
    // Кладовщик удаляет бланк из конверта предназначенного справковеду
    const SR_REMOVE_BLANK_IN_ENVELOPE_FOR_REFERENCEMAN = 'stockman_remove_blank_in_envelope_for_referenceman';
    // Клалдовщик добавляет ненайденный бланк
    const S_ADDED_NOT_FOUND_BLANK = 'stockman_added_not_found_blank';
    // Справковед добавляет испорченные бланки
    const R_BROKEN_BY_REFERENCEMAN = 'referenceman_broken_by_referenceman';



    // Справковед принимает бланки от кладовщика
    const SR_ACCEPT_BLANK_FROM_STOCK = 'refer_accept_blank_from_stock';
    // Справковед принимает коверт с бланками от справковеда
    const RR_ACCEPT_ENVELOP_ALL_BLANK_FROM_REFERENCE = 'refer_accept_envelop_all_blank_from_reference';
    // Справковед принимает бланки от другого справковеда
    const RR_ACCEPT_BLANK_FROM_REFERENCE = 'refer_accept_blank_from_refer';
    // Справковед принимает коверт с бланками
    const OR_ACCEPT_ENVELOP_ALL_BLANK_FROM_OPERATOR = 'refer_accept_envelop_all_blank_from_operator';
    // Справковед принимает бланк от оператора
    const OR_ACCEPT_BLANK_FROM_OPERATOR = 'refer_accept_blank_from_operator';
    // Справковед создает конверт для последующей передачи какому либо оператору
    const RR_CREATE_ENVELOP_REFERENCE_TO_OPERATOR = 'refer_create_envelop_refer_to_operator';
    // Справковед назначает конверт на оператора
    const RO_ASSIGN_ENVELOP_TO_OPERATOR = 'refer_assign_envelop_to_operator';
    // Справковед повторное назначает конверт, после удаления конверта от оператора
    const RO_REPEATED_ASSIGN_ENVELOP_TO_OPERATOR = 'refer_repeated_assign_envelop_to_operator';
    // Справковед отменяет назначение конверта на оператора
    const OR_REMOVE_ENVELOP_FROM_OPERATOR = 'refer_remove_envelop_from_operator';
    // Справковед удаляет конверт созданный для оператора
    const RR_DELETE_ENVELOP_BY_REFERENCE = 'refer_delete_envelop_by_refer';
    // Справковед удаляет бланк из конверта предназначенного оператору
    const RR_REMOVE_BLANK_IN_ENVELOP_FOR_OPERATOR = 'refer_remove_blank_in_envelop_for_operator';
    // Справковед добавляет не найденный бланк
    const RR_ADDED_NOT_FOUND_BLANK = 'refer_added_not_found_blank';
    // Справковед отменяет не найденный бланк
    const RR_CANCELED_NOT_FOUND_BLANK = 'refer_canceled_not_found_blank';
    // Справковед передает бланк другому справковеду
    const RR_REVERT_BLANK_TO_REFERENCE = 'refer_revert_blank_to_refer';
    // Справковед возвращает бланк кладовщику
    const RS_REVERT_BLANK_TO_STOCK = 'refer_revert_envelop_blanks_to_stock';
    // Справковед отменяет возврат бланка кладовщику
    const SR_CANCELED_REVERT_BLANK_TO_STOCK = 'refer_canceled_envelop_blanks_to_stock';
    // Справковед отменяет возврат бланка другому справковеду
    const RR_CANCELED_REVERT_BLANK_TO_REFER = 'refer_canceled_revert_blank_to_refer';
    // Справковед архивирует бланк, испорченный оператором
    const OR_ARHIVED_BY_REFERENCE = 'refer_archived_by_refer';
    const OR_ARHIVED_BROKEN_BY_REFERENCE = 'refer_archived_broken_by_refer';
    // Справковед подтверждает потерянный бланк
    const OR_CONFIRM_LOST_BLANK = 'refer_confirm_lost_blank';
    // Справковед отказывается подтвердить потерянный бланк
    const OR_NOT_CONFIRM_LOST_BLANK = 'refer_not_confirm_lost_blank';

    // Оператор принимает бланки от справковеда
    const RO_ACCEPT_BLANK_FROM_REFERENCE = 'operator_accept_blank_from_refer';
    // Оператор оказал услугу
    const OO_USED_BY_OPERATOR = 'operator_used_by_operator';
    // Оператор оказал услугу по ГНОЧ
    const OR_USED_BLANK_GNOCH = 'operator_used_by_operator_by_gnoch';
    // Оператор оказал услугу по ошибке медцентра
    const OO_USED_BY_OPERATOR_BY_MEDICAL_ERROR = 'operator_used_by_operator_by_medical_error';
    // Оператор оказал услугу по ошибке медцентра по ГНОЧ
    const OO_USED_BY_OPERATOR_BY_MEDICAL_ERROR_GNOCH = 'operator_used_by_operator_by_medical_error_gnoch';
    // Оператор оказал услугу дубликат
    const OO_USED_BY_OPERATOR_BY_DUPLICATE = 'operator_used_by_operator_by_duplicate';
    // Оператор оказал услугу дубликат по ГНОЧ
    const OO_USED_BY_OPERATOR_BY_DUPLICATE_GNOCH = 'operator_used_by_operator_by_duplicate_gnoch';

    // Оператор добавляет испорченные бланки
    const OO_CANCELLED_BY_OPERATOR = 'operator_cancelled_by_operator';
    // Оператор добавляет испорченные бланки по ошибке медцентра
    const OO_CANCELLED_BY_OPERATOR_MEDICAL_ERROR = 'operator_cancelled_by_medical_error';
    // Оператор добавляет испорченные бланки по ошибке медцентра по гноч
    const OO_CANCELLED_BY_OPERATOR_MEDICAL_ERROR_GNOCH = 'operator_used_by_operator_by_medical_error_gnoch';

    //Оператор возвращает бланки справковеду
    const OR_REVERT_BLANK_TO_REFER = 'operator_revert_blank_to_refer';
    // Оператор удаляет бланк из коверта для возврата справковеду
    const OO_REMOVE_BLANK_IN_ENVELOP_FOR_REFERENCE = 'operator_remove_blank_in_evelop_for_refer';
    // Оператор возвращает ипорченный бланк в работу
    const OO_ACCEPT_CANCELED_BLANK = 'operator_accept_canceled_blank';
    // Оператор потерял бланки
    const OO_LOST_BLANK = 'operator_lost_blank';
}
