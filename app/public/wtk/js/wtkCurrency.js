/* This can be used if you are allowing multiple currencies.  For example, use this HTML <select>

<select id="currency" name="currency" onchange="JavaScript:changeCurrency()">
    <option value="USD">US Dollar $</option>
    <option value="MXN">Mexican Pesos</option>
    <option value="EUR">Euro &euro;</option>
    <option value="GBP">British Pound &pound;</option>
    <option value="NZD">New Zealand $</option>
</select>
*/
function changeCurrency() {
    var fncCurrency = $('#currency').val();
    $.ajax({
        type: 'POST',
        url:  '/wtk/setCurrency.php',
        data: { Currency: fncCurrency },
            success: function(data) {
                let fncJSON = $.parseJSON(data);
                if (fncJSON.result == 'ok'){
                    $('#basicCost').html(fncJSON.basicCost);
                    $('#premCost').html(fncJSON.premCost);
                    $('#basicAmount').val(fncJSON.basicAmount);
                    $('#premAmount').val(fncJSON.premAmount);
                    M.toast({html: 'Currency preference saved', classes: 'rounded green'});
                } else {
                    M.toast({html: 'Problem setting currency', classes: 'rounded red'});
                }
            }
    })
}
