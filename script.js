// Variabel global untuk menyimpan data pesanan
let selectedProduct = {};
let selectedPaymentMethod = "";

// Event listener untuk tombol beli
document.querySelectorAll(".btn-buy").forEach((button) => {
  button.addEventListener("click", function () {
    const productName = this.getAttribute("data-product");
    const productPrice = parseInt(this.getAttribute("data-price"));

    selectedProduct = {
      name: productName,
      price: productPrice,
    };

    // Isi form dengan data produk
    document.getElementById("productName").value = productName;
    document.getElementById("productPrice").value = formatRupiah(productPrice);
    document.getElementById("quantity").value = 1;
    document.getElementById("totalPrice").value = formatRupiah(productPrice);

    // Reset form
    document.getElementById("customerName").value = "";
    document.getElementById("customerPhone").value = "";
    document.getElementById("customerAddress").value = "";

    // Reset pilihan pembayaran
    document.querySelectorAll(".payment-method").forEach((method) => {
      method.classList.remove("selected");
    });
    selectedPaymentMethod = "";

    // Tampilkan modal
    document.getElementById("orderModal").style.display = "block";
  });
});

// Event listener untuk tombol tutup modal
document.querySelector(".close-modal").addEventListener("click", function () {
  document.getElementById("orderModal").style.display = "none";
});

// Event listener untuk perubahan jumlah pesanan
document.getElementById("quantity").addEventListener("input", function () {
  const quantity = parseInt(this.value) || 0;
  const totalPrice = selectedProduct.price * quantity;
  document.getElementById("totalPrice").value = formatRupiah(totalPrice);
});

// Event listener untuk pilihan metode pembayaran
document.querySelectorAll(".payment-method").forEach((method) => {
  method.addEventListener("click", function () {
    document.querySelectorAll(".payment-method").forEach((m) => {
      m.classList.remove("selected");
    });
    this.classList.add("selected");
    selectedPaymentMethod = this.getAttribute("data-method");
  });
});

// Event listener untuk tombol lanjutkan pembayaran
document.getElementById("btnPayment").addEventListener("click", function () {
  // Validasi form
  const customerName = document.getElementById("customerName").value.trim();
  const customerPhone = document.getElementById("customerPhone").value.trim();
  const customerAddress = document
    .getElementById("customerAddress")
    .value.trim();
  const quantity = parseInt(document.getElementById("quantity").value) || 0;

  if (!customerName) {
    alert("Mohon isi nama pembeli");
    return;
  }

  if (!customerPhone) {
    alert("Mohon isi nomor WhatsApp");
    return;
  }

  if (!customerAddress) {
    alert("Mohon isi alamat lengkap");
    return;
  }

  if (quantity < 1) {
    alert("Mohon isi jumlah pesanan yang valid");
    return;
  }

  if (!selectedPaymentMethod) {
    alert("Mohon pilih metode pembayaran");
    return;
  }

  // Jika pembayaran melalui DANA, arahkan ke aplikasi DANA
  if (selectedPaymentMethod === "dana") {
    // Simulasi pengalihan ke aplikasi DANA
    alert(
      "Anda akan diarahkan ke aplikasi DANA untuk melakukan pembayaran ke nomor 085774988295"
    );

    // Simulasi pembayaran berhasil
    setTimeout(function () {
      document.getElementById("orderModal").style.display = "none";
      document.getElementById("thankYouModal").style.display = "block";
    }, 2000);
  } else {
    // Untuk COD, langsung tampilkan terima kasih
    document.getElementById("orderModal").style.display = "none";
    document.getElementById("thankYouModal").style.display = "block";
  }
});

// Event listener untuk tombol tutup modal terima kasih
document.getElementById("closeThankYou").addEventListener("click", function () {
  document.getElementById("thankYouModal").style.display = "none";
});

// Fungsi untuk format Rupiah
function formatRupiah(amount) {
  return "Rp " + amount.toLocaleString("id-ID");
}

// Tutup modal jika klik di luar konten modal
window.addEventListener("click", function (event) {
  const orderModal = document.getElementById("orderModal");
  const thankYouModal = document.getElementById("thankYouModal");

  if (event.target === orderModal) {
    orderModal.style.display = "none";
  }

  if (event.target === thankYouModal) {
    thankYouModal.style.display = "none";
  }
});
