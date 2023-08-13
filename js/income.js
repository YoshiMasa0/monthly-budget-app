const FORM_AREA = document.getElementById("form-area");
let budgetHttp = new XMLHttpRequest();


// イベントハンドラを登録
ADD_FIELD_BTN.addEventListener("click", addInputField);
FORM_AREA.addEventListener("submit", hasErrorInputValue);
budgetHttp.addEventListener("load", displayIncome);

budgetHttp.open("GET", "http://localhost/budget/php/income.php");
budgetHttp.send();

/**
 * リクエストを送信して取得した残りの予算を画面に表示する
 * @param {*} event 
 */
function displayIncome(event) {
    console.log(event);
    let response = JSON.parse(event.currentTarget.response);
    let incomes = response["income"];
    let incomesLength = incomes.length;
    
    if (incomesLength <= 1 && incomes[0] == 0) {
        // 収入が0円で登録されている場合
        addInputField();
    } else {
        // 登録されている収入が入力されている入力欄を表示する
        for (let i = 0; i < incomesLength; i++) {
            addInputField();
            let inputField = document.getElementsByClassName("income");
            inputField[i].value = incomes[i];
        }
    }
}


/**
 * formの入力欄を追加する
 */
function addInputField() {
    // 金額入力欄を追加
    let inputField = document.createElement("input");
    inputField.type = "text";
    inputField.inputMode = "numeric";
    inputField.className = "income form-control mt-2";
    inputField.name = "income[]";
    FIELD_PARENT.appendChild(inputField);

    // エラーメッセージ表示欄を追加
    let errMsg = document.createElement("div");
    errMsg.className = "err-msg  form-text hidden";
    FIELD_PARENT.appendChild(errMsg);
}


/**
 * フォームに入力された値にエラーがないかチェックする
 * 
 * エラーがある場合は画面にエラーメッセージを表示する
 * 
 * @param {*} event 
 */
function hasErrorInputValue(event) {
    const INPUT_FIELD = document.getElementsByClassName("income");
    const ERR_MSG = document.getElementsByClassName("err-msg");
    
    let inputFieldLength = INPUT_FIELD.length;
    for(let i = 0; i < inputFieldLength; i++) {

        convertedIncome = convertStringCostToIntCost(INPUT_FIELD[i].value);
        if(isNaN(convertedIncome)) {
            // int型に変換できなかった場合
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = ILLEGAL_CHAR_ERR_MSG;
            event.preventDefault();
            continue;
        }
        if(convertedIncome > 100000000) {
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = OUT_RANGE_ERR_MSG;
            event.preventDefault();
            continue;
        }

        // エラーメッセージ欄を初期化
        ERR_MSG[i].className = "err-msg form-text hidden";
    }
}