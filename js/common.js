const FIELD_PARENT = document.getElementById("input-area");
const ADD_FIELD_BTN = document.getElementById("add-field-btn");

// エラーメッセージ
const ILLEGAL_CHAR_ERR_MSG = "数字と「,」「，」で入力してください";
const OUT_RANGE_ERR_MSG = "「100,000,000」以下の値を入力してください"

// 入力値を変換するときに使用するMap
const REPLACE_MAP = {
    "０":"0", "１":"1", "２":"2", "３":"3", "４":"4", "５":"5",
    "６":"6", "７":"7", "８":"8", "９":"9", "，":"", ",":"", 
}


/**
 * String型の金額をint型の金額に変換する。
 * 小数点がある場合は小数点以下を切り捨てた値、文字や空白がある場合は NaN を返す。
 * カンマが入った金額と全角数字の型変換は可能。
 * 
 * /@param {cost} String型の金額
 * /@returns {number|NaN} 型変換に成功した場合は変換語の値、失敗した場合はNanを返す
 */
function convertStringCostToIntCost(cost){
    replacedCost = cost.replace(/[,，０-９]/g, function(match) {
        return REPLACE_MAP[match];
    });

    return Number(replacedCost);
}