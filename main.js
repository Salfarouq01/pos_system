let cart = [];
const cartElement = document.getElementById('cart');
const totalElement = document.getElementById('total');

document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const price = parseFloat(btn.getAttribute('data-price'));

        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({ id, name, price, quantity: 1 });
        }
        renderCart();
    });
});

function renderCart() {
    cartElement.innerHTML = '';
    let total = 0;
    cart.forEach(item => {
        total += item.price * item.quantity;
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            ${item.name} x${item.quantity}
            <span>TZS ${ (item.price * item.quantity).toFixed(2) }</span>
        `;
        cartElement.appendChild(li);
    });
    totalElement.textContent = total.toFixed(2);
}

document.getElementById('checkout').addEventListener('click', () => {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'process_checkout.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Order completed successfully!');
            cart = [];
            renderCart();
            totalElement.textContent = '0.00';
        } else {
            alert('Error processing order.');
        }
    };
    xhr.send(JSON.stringify(cart));
});
