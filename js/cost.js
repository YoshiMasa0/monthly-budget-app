const FORM_AREA = document.getElementById("form-area");
let fixedCostHttp = new XMLHttpRequest();


// イベントハンドラを登録
ADD_FIELD_BTN.addEventListener("click", addInputField);
FORM_AREA.addEventListener("submit", hasErrorInputValue);
fixedCostHttp.addEventListener("load", displayIncome);

fixedCostHttp.open("GET", "http://localhost/budget/php/fixed_cost.php");
fixedCostHttp.send();

/**
 * リクエストを送信して取得した残りの予算を画面に表示する
 * @param {*} event 
 */
function displayIncome(event) {
    console.log(event);
    let response = JSON.parse(event.currentTarget.response);
    let costs = response["cost"];
    let costsLength = costs.length;
    
    if (costsLength == 0) {
        document.title = '固定費登録画面';
        addInputField();
    } else if (costsLength == 1 && costs[0] == 0) {
        // 収入が0円で登録されている場合
        addInputField();
    } else {
        // 登録されている収入が入力されている入力欄を表示する
        for (let i = 0; i < costsLength; i++) {
            addInputField();
            let inputField = document.getElementsByClassName("cost");
            inputField[i].value = costs[i];
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
    inputField.className = "cost form-control mt-2";
    inputField.name = "cost[]";
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
    const INPUT_FIELD = document.getElementsByClassName("cost");
    const ERR_MSG = document.getElementsByClassName("err-msg");
    
    let inputFieldLength = INPUT_FIELD.length;
    for(let i = 0; i < inputFieldLength; i++) {

        convertedCost = convertStringCostToIntCost(INPUT_FIELD[i].value);
        if (isNaN(convertedCost)) {
            // int型に変換できなかった場合
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = ILLEGAL_CHAR_ERR_MSG;
            event.preventDefault();
            continue;
        }
        if (convertedCost > 2147483647 || convertedCost < 0) {
            ERR_MSG[i].className = "err-msg form-text";
            ERR_MSG[i].textContent = OUT_RANGE_ERR_MSG;
            event.preventDefault();
            continue;
        }
        if (convertedCost == 0) {
            continue;
        }

        // エラーメッセージ欄を初期化
        ERR_MSG[i].className = "err-msg form-text hidden";
    }
}