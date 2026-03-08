// function to update price and total days
const minus = document.getElementById("Minus");
const plus = document.getElementById("Plus");
const count = document.getElementById("CountDays");
const days = document.getElementById("Days");
const totalPrice = document.getElementById("Total");
const duration = document.getElementById("duration");
const productPrice = document.getElementById("productPrice");
const defaultPrice = productPrice.value;

// ✅ QTY elements
const minusQty = document.getElementById("MinusQty");
const plusQty  = document.getElementById("PlusQty");
const countQty = document.getElementById("CountQty");
const qtyInput = document.getElementById("Qty");
const maxQty   = parseInt(qtyInput.getAttribute("max"));

// ✅ Update total: days x qty x price
function updateTotalPrice() {
    const currentDays = parseInt(days.value) || 1;
    const currentQty  = parseInt(qtyInput.value) || 1;
    let subTotal = currentDays * currentQty * defaultPrice;
    totalPrice.innerText = "Rp " + subTotal.toLocaleString('id-ID');
}

// --- DAYS counter ---
minus.addEventListener("click", function() {
    let currentCount = parseInt(count.innerText);
    if (currentCount > 1) {
        currentCount -= 1;
        count.innerText = currentCount;
        days.value = currentCount;
        duration.value = currentCount;
        updateTotalPrice();
    }
});

plus.addEventListener("click", function() {
    let currentCount = parseInt(count.innerText);
    currentCount += 1;
    count.innerText = currentCount;
    days.value = currentCount;
    duration.value = currentCount;
    updateTotalPrice();
});

days.addEventListener("change", function () {
    count.innerText = days.value;
    updateTotalPrice();
});

// --- QTY counter ---
minusQty.addEventListener("click", function() {
    let current = parseInt(qtyInput.value);
    if (current > 1) {
        current -= 1;
        qtyInput.value = current;
        countQty.innerText = current;
        updateTotalPrice();
    }
});

plusQty.addEventListener("click", function() {
    let current = parseInt(qtyInput.value);
    if (current < maxQty) {
        current += 1;
        qtyInput.value = current;
        countQty.innerText = current;
        updateTotalPrice();
    } else {
        alert('Stok hanya tersedia ' + maxQty + ' unit');
    }
});

updateTotalPrice();

// funtion date
const datePicker = document.getElementById('date');
const btnText = document.getElementById('DateTriggerBtn');

datePicker.addEventListener('change', function () {
    if (datePicker.value) {
        btnText.innerText = datePicker.value
        btnText.classList.add("font-semibold");
    } else {
        btnText.innerText = "Select date"
        btnText.classList.remove("font-semibold"); 
    }
});

// funtion nav & tabs like bootstrap
document.addEventListener("DOMContentLoaded", function () {
    window.openPage = function (pageName, elmnt) {
        let i, tabcontent, tablinks;

        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.add("hidden");
        }

        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active", "ring-2", "ring-[#FCCF2F]");
        }
        
        document.getElementById(pageName).classList.remove("hidden");
        elmnt.classList.add("active", "ring-2", "ring-[#FCCF2F]");
    };

    document.getElementById("defaultOpen").click();
});

// funtion for changing required atribute
function toggleRequiredOptions() {
    const pickupRadio = document.getElementById('Pickup');
    const deliveryRadio = document.getElementById('Delivery');
    const deliveryType = document.getElementById('deliveryType');
    const storeRadios = document.getElementsByName('store');
    const addressTextarea = document.getElementsByName('address')[0];

    if (pickupRadio.checked) {
        storeRadios.forEach(radio => {
            radio.required = true;
        });
        addressTextarea.value = "Ambil di toko";
        deliveryType.value = "pickup";
    } else if (deliveryRadio.checked) {
        storeRadios.forEach(radio => {
            radio.required = false;
        });
        addressTextarea.value = '';
        deliveryType.value = "home_delivery";
        document.querySelector('input[name="store_id"]').value = 1;
    }
}