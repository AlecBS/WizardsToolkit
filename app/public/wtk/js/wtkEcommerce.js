// These functions are used for ecommerce and Stripe specifically
function wtkAddItemToCart(fncType, fncItemUID){
    waitLoad('on');
    let fncQty = 1;
    if (elementExist('itemQty')) {
        fncQty = $('#itemQty').val();
    }
    let fncAddOnQty = 0;
    if (elementExist('addOnQty')) {
        fncAddOnQty = $('#addOnQty').val();
    }
    let fncKidUIDs = 0;
    if (elementExist('KidUIDs')) {
        fncKidUIDs = $('#KidUIDs').val();
    }
    $.ajax({
        type: 'POST',
        url: 'ajxAddItemToCart.php',
        data: { apiKey: pgApiKey, rng: fncType, id: fncItemUID, qty: fncQty, addOnQty: fncAddOnQty, KidUIDs: fncKidUIDs },
        success: function(data) {
            waitLoad('off');
            $('#cartBtn').removeClass('hide');
            M.toast({html: 'Item added to cart', classes: 'green rounded'});
            if (elementExist('buyMsg')) {
                $('#buyMsg').removeClass('hide');
            }
            // show cart button
        }
    })
} // wtkAddItemToCart

function wtkAddEventToCart(fncItemUID){
    waitLoad('on');
    let fncFormData = $('#eventForm').serialize();
    fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
    $.ajax({
        type: 'POST',
        url: 'ajxAddItemToCart.php',
        data: (fncFormData),
        success: function(data) {
            waitLoad('off');
            $('#cartBtn').removeClass('hide');
            M.toast({html: 'Item added to cart', classes: 'green rounded'});
            if (elementExist('buyMsg')) {
                $('#buyMsg').removeClass('hide');
            }
            // show cart button
        }
    })
} // wtkAddEventToCart

function wtkStripeCart() {
    waitLoad('on');
    var stripe = Stripe(gloStripePublicKey);
    fetch('/cartToStripe.php', {
        method: 'POST',
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(session) {
        let fncResult = session.result;
        if (fncResult == 'error') {
            alert(session.error);
        } else {
            M.toast({html: 'Redirecting to Stripe', classes: 'green rounded'});
            return stripe.redirectToCheckout({ sessionId: session.id });
        }
    })
    .then(function(result) {
        if (result.error) {
            alert(result.error.message);
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
} // wtkStripeCart

function wtkAdjustQty(fncThis){
    waitLoad('on');

    let fncParams = new FormData();
    var fncForm = document.createElement('form');
    fncForm.setAttribute('method', 'post');
    fncForm.setAttribute('action', 'shoppingCart.php');

    let fncHiddenField = document.createElement('input');
    fncHiddenField.setAttribute('type', 'hidden');
    fncHiddenField.setAttribute('name', 'task');
    fncHiddenField.setAttribute('value', 'qty');
    fncForm.appendChild(fncHiddenField);

    fncHiddenField = document.createElement('input');
    fncHiddenField.setAttribute('type', 'hidden');
    fncHiddenField.setAttribute('name', 'id');
    fncHiddenField.setAttribute('value', fncThis.id);
    fncForm.appendChild(fncHiddenField);

    fncHiddenField = document.createElement('input');
    fncHiddenField.setAttribute('type', 'hidden');
    fncHiddenField.setAttribute('name', 'qty');
    fncHiddenField.setAttribute('value', fncThis.value);
    fncForm.appendChild(fncHiddenField);

    if (typeof pgApiKey !== 'undefined' && pgApiKey !== '') {
        fncHiddenField = document.createElement('input');
        fncHiddenField.setAttribute('type', 'hidden');
        fncHiddenField.setAttribute('name', 'apiKey');
        fncHiddenField.setAttribute('value', pgApiKey);
        fncForm.appendChild(fncHiddenField);
    }
    document.body.appendChild(fncForm);
    fncForm.submit();
} // wtkAdjustQty

function wtkStripeSubscription() {
    waitLoad('on');
    var stripe = Stripe(gloStripePublicKey);
    fetch('/subscribeStripe.php', {
        method: 'POST',
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(session) {
        let fncResult = session.result;
        if (fncResult == 'error') {
            alert(session.error);
        } else {
            M.toast({html: 'Redirecting to Stripe', classes: 'green rounded'});
            return stripe.redirectToCheckout({ sessionId: session.id });
        }
    })
    .then(function(result) {
        if (result.error) {
            alert(result.error.message);
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
} // wtkStripeCart
