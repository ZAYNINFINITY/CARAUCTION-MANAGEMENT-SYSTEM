<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add Transaction - Car Auction</title>
  <link rel="stylesheet" href="../css/styles.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap">
  <style>
    .success-message {
      animation: fadeInUp 0.5s ease-out;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
      <div class="header-content glass">
        <div class="logo">
          <h1>Car Auction</h1>
          <p class="animated-text">Car Auction Management</p>
        </div>
        <nav>
          <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="auctions.php">Auctions</a></li>
            <li><a href="add_car.php">Add Car</a></li>
            <li><a href="transactions.php" class="active">Transactions</a></li>
          </ul>
        </nav>
        <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">🌙</button>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
      <div class="page-title">
        <h2>Add New Transaction</h2>
        <p>Fill in the details to complete a transaction.</p>
      </div>

      <div class="card">
        <form id="transactionForm">
          <div class="form-group">
            <label for="auctionId">Auction ID:</label>
            <input type="number" id="auctionId" name="auctionId" required>
          </div>

          <div class="form-group">
            <label for="buyerEmail">Buyer Email:</label>
            <input type="email" id="buyerEmail" name="buyerEmail" required>
          </div>

          <div class="form-group">
            <label for="finalPrice">Final Price ($):</label>
            <input type="number" id="finalPrice" name="finalPrice" required>
          </div>

          <div class="form-group">
            <label for="paymentMethod">Payment Method:</label>
            <select id="paymentMethod" name="paymentMethod" required>
              <option value="">Select</option>
              <option value="Card">Card</option>
              <option value="Cash">Cash</option>
            </select>
          </div>
          <p class="instruction">For Card payments, enter a valid 16-digit number.</p>

          <div class="form-group" id="cardGroup">
            <label for="cardNumber">Card Number (if Card):</label>
            <input type="text" id="cardNumber" name="cardNumber" maxlength="16">
          </div>

          <button type="submit" class="cta-button">Submit Transaction</button>
        </form>

        <p id="formMessage"></p>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container footer-content">
      <div class="footer-links">
        <a href="index.php">Home</a>
        <a href="auctions.php">Auctions</a>
        <a href="add_car.php">Add Car</a>
        <a href="transactions.php">Transactions</a>
      </div>
      <div>Dev By ZAIN UL ABIDEEN</div>
    </div>
  </footer>

  <script>
    const form = document.getElementById('transactionForm');
    const message = document.getElementById('formMessage');
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Theme toggle
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
      body.classList.add('dark-mode');
      themeToggle.textContent = '☀️';
    }
    themeToggle.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      const isDark = body.classList.contains('dark-mode');
      themeToggle.textContent = isDark ? '☀️' : '🌙';
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Pre-fill form if parameters are passed
    const urlParams = new URLSearchParams(window.location.search);
    const auctionId = urlParams.get('auctionId');
    const bidderName = urlParams.get('bidderName');
    const bidderEmail = urlParams.get('bidderEmail');
    const finalPrice = urlParams.get('finalPrice');

    if (auctionId) document.getElementById('auctionId').value = auctionId;
    if (bidderEmail) document.getElementById('buyerEmail').value = bidderEmail;
    if (finalPrice) document.getElementById('finalPrice').value = finalPrice;

    // Handle payment method change to show/hide card input
    const paymentMethod = document.getElementById('paymentMethod');
    const cardGroup = document.getElementById('cardGroup');
    const cardNumber = document.getElementById('cardNumber');

    paymentMethod.addEventListener('change', () => {
      if (paymentMethod.value === 'Card') {
        cardGroup.style.display = 'block';
        cardNumber.required = true;
      } else {
        cardGroup.style.display = 'none';
        cardNumber.required = false;
        cardNumber.value = '';
      }
    });

    form.addEventListener('submit', e => {
      e.preventDefault();

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      fetch('../api/api.php?action=complete_transaction', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
      })
      .then(res => res.json())
      .then(result => {
        if(result.success){
          message.style.color = 'lime';
          message.innerHTML = '<span style="font-size: 1.2em;">✓</span> Transaction completed successfully!';
          message.classList.add('success-message');
          form.reset();
          // Redirect to transactions page to see the update
          setTimeout(() => window.location.href = 'transactions.php', 2000);
        } else {
          message.style.color = 'red';
          message.textContent = 'Error: ' + result.error;
          message.classList.remove('success-message');
        }
      })
      .catch(err => {
        message.style.color = 'red';
        message.textContent = 'Failed to submit transaction.';
        console.error(err);
      });
    });
  </script>
<script src="../js/chatbot.js"></script>
</body>
</html>
