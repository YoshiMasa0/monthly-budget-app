const BUDGET = document.getElementById("budget");
const FORM_AREA = document.getElementById("form-area");
let budgetHttp = new XMLHttpRequest();


// イベントハンドラを登録
ADD_FIELD_BTN.addEventListener("click", addInputField);
FORM_AREA.addEventListener("submit", calcMonthlyBudget);
budgetHttp.addEventListener("load", displayBudget);

budgetHttp.open("GET", "http://localhost/budget/php/index");
budgetHttp.send();
addInputField();


/**
 * リクエストを送信して取得した残りの予算を画面に表示する
 * @param {*} event 
 */
function displayBudget(event) {
    console.log(event);
    response = JSON.parse(event.currentTarget.response);
    BUDGET.textContent = response["budget"].toLocaleString();
}


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
 * 
 * @param {*} event 
 */
function calcMonthlyBudget(event) {
    const INPUT_FIELD = document.getElementsByClassName("cost");
    const ERR_MSG = document.getElementsByClassName("err-msg");
    
    let inputFieldLength = INPUT_FIELD.length;
    let costs = new FormData();
    for(let i = 0; i < inputFieldLength; i++) {

        convertedCost = convertStringCostToIntCost(INPUT_FIELD[i].value);
        if (isNaN(convertedCost)) {
            // int型に変換できなかった場合
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = ILLEGAL_CHAR_ERR_MSG;
            continue;
        }
        if (convertedCost > 2147483647 || convertedCost < 0) {
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = OUT_RANGE_ERR_MSG;
            continue;
        }
        if (convertedCost == 0) {
            continue;
        }
        
        costs.append("cost[]", convertedCost);

        // 入力欄とエラーメッセージ欄を初期化
        INPUT_FIELD[i].value = "";
        ERR_MSG[i].className = "err-msg form-text hidden";
    }
    // 使用金額を登録するリクエストを送信
    budgetHttp.open("POST", "http://localhost/budget/php/index");
    budgetHttp.send(costs);

    // form 送信のデフォルト動作停止する
    event.preventDefault();
}