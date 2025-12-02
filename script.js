// script.js - JavaScript for Kios Bungsu Jaya

// DOM Elements
const productsContainer = document.getElementById("products-container");
const orderModal = document.getElementById("orderModal");
const successModal = document.getElementById("successModal");
const closeButtons = document.querySelectorAll(
  ".close, .close-modal, .close-success"
);
const orderForm = document.getElementById("orderForm");
const quantityInput = document.getElementById("quantity");

// Product data
let currentProduct = {
  id: null,
  name: "",
  price: 0,
};

// Load products on page load
document.addEventListener("DOMContentLoaded", function () {
  // Set minimum delivery date to tomorrow
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  document.getElementById("delivery_date").min = tomorrow
    .toISOString()
    .split("T")[0];
  document.getElementById("delivery_date").value = tomorrow
    .toISOString()
    .split("T")[0];

  // Load products
  loadProducts();

  // Event listeners for modals
  closeButtons.forEach((button) => {
    button.addEventListener("click", closeAllModals);
  });

  // Close modal when clicking outside
  window.addEventListener("click", (e) => {
    if (e.target === orderModal || e.target === successModal) {
      closeAllModals();
    }
  });

  // Quantity change listener
  quantityInput.addEventListener("input", updateOrderSummary);
});

// Load products from server
async function loadProducts() {
  try {
    const response = await fetch("products.php");
    const data = await response.json();

    if (data.success && data.products.length > 0) {
      displayProducts(data.products);
    } else {
      productsContainer.innerHTML =
        '<div class="error">Tidak ada produk tersedia</div>';
    }
  } catch (error) {
    productsContainer.innerHTML =
      '<div class="error">Gagal memuat produk</div>';
    console.error("Error loading products:", error);
  }
}

// Display products in grid
function displayProducts(products) {
  productsContainer.innerHTML = "";

  products.forEach((product) => {
    const productCard = document.createElement("div");
    productCard.className = "product-card";

    productCard.innerHTML = `
            <div class="product-image">
                <img src="${product.image_url}" alt="${product.name}" 
                     onerror="this.src='https://images.unsplash.com/photo-1586201375761-83865001e31c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'">
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="product-desc">${product.description}</p>
                <div class="product-price">Rp ${formatRupiah(
                  product.price
                )}<span>/liter</span></div>
                <p class="product-stock">Stok: ${product.stock} liter</p>
                <button class="btn-buy" 
                        data-id="${product.id}"
                        data-name="${product.name}"
                        data-price="${product.price}">
                    <i class="fas fa-shopping-cart"></i> Beli Sekarang
                </button>
            </div>
        `;

    productsContainer.appendChild(productCard);
  });

  // Add event listeners to buy buttons
  document.querySelectorAll(".btn-buy").forEach((button) => {
    button.addEventListener("click", function () {
      openOrderModal(
        this.getAttribute("data-id"),
        this.getAttribute("data-name"),
        parseInt(this.getAttribute("data-price"))
      );
    });
  });
}

// Open order modal
function openOrderModal(id, name, price) {
  currentProduct = { id, name, price };

  // Set modal values
  document.getElementById("modal-product-id").value = id;
  document.getElementById("modal-product-name").value = name;
  document.getElementById("modal-product-price").value = price;

  // Update summary
  updateOrderSummary();

  // Show modal
  orderModal.style.display = "block";
  document.body.style.overflow = "hidden";
}

// Update order summary
function updateOrderSummary() {
  const quantity = parseInt(quantityInput.value) || 1;
  const total = currentProduct.price * quantity;

  // Update display
  document.getElementById("summary-product").textContent = currentProduct.name;
  document.getElementById("summary-quantity").textContent = `${quantity} liter`;
  document.getElementById("summary-total").textContent = `Rp ${formatRupiah(
    total
  )}`;
  document.getElementById("price-per-unit").textContent = `Rp ${formatRupiah(
    currentProduct.price
  )}`;
}

// Format Rupiah
function formatRupiah(amount) {
  return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Close all modals
function closeAllModals() {
  orderModal.style.display = "none";
  successModal.style.display = "none";
  document.body.style.overflow = "auto";
}

// Handle form submission
orderForm.addEventListener("submit", async function (e) {
  e.preventDefault();

  // Get form data
  const formData = {
    product_id: currentProduct.id,
    product_name: currentProduct.name,
    product_price: currentProduct.price,
    quantity: parseInt(document.getElementById("quantity").value),
    customer_name: document.getElementById("customer_name").value,
    whatsapp: document.getElementById("whatsapp").value,
    address: document.getElementById("address").value,
    delivery_date: document.getElementById("delivery_date").value,
    payment_method: document.querySelector(
      'input[name="payment_method"]:checked'
    ).value,
  };

  // Validate
  if (formData.quantity < 1) {
    alert("Jumlah minimal 1 liter");
    return;
  }

  if (!formData.whatsapp.match(/^[0-9]{10,15}$/)) {
    alert("Nomor WhatsApp tidak valid");
    return;
  }

  // Show loading
  const submitBtn = orderForm.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
  submitBtn.disabled = true;

  try {
    // Send to server
    const response = await fetch("process_order.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(formData),
    });

    const result = await response.json();

    if (result.success) {
      // Show success modal
      document.getElementById("success-order-number").textContent =
        result.order_number;
      document.getElementById(
        "success-total"
      ).textContent = `Rp ${result.total_price}`;

      orderModal.style.display = "none";
      successModal.style.display = "block";

      // Open WhatsApp
      setTimeout(() => {
        if (result.whatsapp_url) {
          window.open(result.whatsapp_url, "_blank");
        }
      }, 2000);

      // Reset form
      orderForm.reset();
    } else {
      alert(`Error: ${result.message}`);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("Terjadi kesalahan jaringan. Coba lagi.");
  } finally {
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
  }
});
