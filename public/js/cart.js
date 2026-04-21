// public/js/cart.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('cart.js loaded');

    const quantityInputs = document.querySelectorAll('.cart-quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function(event) {
            const productId = this.dataset.productId;
            const newQuantity = this.value;

            // Basic validation
            if (newQuantity < 1) {
                this.value = 1; // Reset to 1 if invalid
                return;
            }

            updateCart(productId, newQuantity);
        });
    });

    /**
     * Sends an AJAX request to update the cart on the server.
     * @param {number} productId The ID of the product to update.
     * @param {number} quantity The new quantity.
     */
    function updateCart(productId, quantity) {
        fetch('/api/update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Helps identify AJAX requests on the server
            },
            body: JSON.stringify({
                productId: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the UI dynamically without a page reload
                // E.g, update the item total and the cart grand total
                document.getElementById(`item-total-${productId}`).textContent = `R ${data.newItemTotal}`;
                document.getElementById('cart-grand-total').textContent = `R ${data.newGrandTotal}`;
                console.log('Cart updated successfully.');
            } else {
                alert('Error: ' + data.message);
                // Optionally revert the quantity input on failure
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            alert('Failed to update cart. Please check your connection and try again.');
        });
    }

});