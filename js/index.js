const BUDGET = document.getElementById("budget");
const FORM_AREA = document.getElementById("form-area");

addInputField();

// イベントハンドラを登録
ADD_FIELD_BTN.addEventListener("click", addInputField);
FORM_AREA.addEventListener("submit", calcMonthlyBudget);


/**
 * formの入力欄を追加する
 */
function addInputField() {
    // 金額入力欄を追加
    let inputField = document.createElement("input");
    inputField.type = "text";
    inputField.inputMode = "numeric";
    inputField.className = "cost form-control mt-2";
    FIELD_PARENT.appendChild(inputField);

    // エラーメッセージ表示欄を追加
    let errMsg = document.createElement("div");
    errMsg.className = "err-msg  form-text hidden";
    FIELD_PARENT.appendChild(errMsg);
}


/**
 * 残りの予算を計算する
 * 計算できない値が入力されている箇所にはエラーメッセージを表示する
 */
function calcMonthlyBudget(event) {
    const INPUT_FIELD = document.getElementsByClassName("cost");
    const ERR_MSG = document.getElementsByClassName("err-msg");
    
    let inputFieldLength = INPUT_FIELD.length;
    let totalCost = 0;
    for(let i = 0; i < inputFieldLength; i++) {

        convertedCost = convertStringCostToIntCost(INPUT_FIELD[i].value);
        if(isNaN(convertedCost)) {
            // int型に変換できなかった場合
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = ILLEGAL_CHAR_ERR_MSG;
            continue;
        }
        if(convertedCost > 100000000) {
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = OUT_RANGE_ERR_MSG;
            continue;
        }
        
        totalCost += convertedCost;

        // 入力欄とエラーメッセージ欄を初期化
        INPUT_FIELD[i].value = "";
        ERR_MSG[i].className = "err-msg form-text hidden";
    }
    // 使用金額を登録するリクエストを送信
    // 残りの予算を取得する
    let convertedBudget = convertStringCostToIntCost(BUDGET.textContent);
    let calculatedBudget = convertedBudget - totalCost;
    BUDGET.textContent = calculatedBudget.toLocaleString();

    // form 送信のデフォルト動作停止する
    event.preventDefault();
}