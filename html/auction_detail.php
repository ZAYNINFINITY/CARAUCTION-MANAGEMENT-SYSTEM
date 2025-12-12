<?php
$auctionId = intval($_GET['auctionId'] ?? 0);
if ($auctionId <= 0) {
    die('Invalid auction ID');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Auction Details - Car Auction</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap">
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
            <li><a href="auctions.php" class="active">Auctions</a></li>
            <li><a href="add_car.php">Add Car</a></li>
            <li><a href="transactions.php">Transactions</a></li>
          </ul>
        </nav>
        <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">🌙</button>
      </div>
    </div>
  </header>

  <main class="container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="page-title">
      <h2>Auction Details</h2>
      <p>View car details and place your bid.</p>
    </div>

    <div class="auction-details card" style="width: 700px; max-width: 90vw; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 8px 32px rgba(0,0,0,0.1); border-radius: 20px;">
      <div id="carInfo"></div>
      <h3>Bids</h3>
      <table id="bidsTable" class="striped">
        <thead>
          <tr>
            <th>Bidder</th>
            <th>Amount</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

      <h3>Place Your Bid</h3>
      <form id="bidForm">
        <div class="form-group">
          <label>Your Name:</label>
          <input type="text" name="bidderName" required class="futuristic-input" />
        </div>
        <div class="form-group">
          <label>Your Email:</label>
          <input type="email" name="bidderEmail" required class="futuristic-input" />
        </div>
        <p class="instruction">Bid must exceed current highest by at least $0.01.</p>
        <div class="form-group">
          <label>Bid Amount:</label>
          <input type="number" step="0.01" name="bidAmount" id="bidAmount" required class="futuristic-input" />
        </div>
        <button type="submit" class="cta-button futuristic-button">Place Bid</button>
      </form>
      <p id="bidMsg"></p>
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
    const auctionId = <?= $auctionId ?>;
    const carInfoDiv = document.getElementById('carInfo');
    const bidsTbody = document.getElementById('bidsTable').querySelector('tbody');
    const bidForm = document.getElementById('bidForm');
    const bidAmountInput = document.getElementById('bidAmount');
    const bidMsg = document.getElementById('bidMsg');
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

    // Load car info and highest bid
    fetch(`../api/api.php?action=auction&auctionId=${auctionId}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const car = data.car;
          carInfoDiv.innerHTML = `
            <p><strong>${car.make} ${car.model} (${car.year})</strong></p>
            <p>Base Price: $${parseFloat(car.base_price).toLocaleString()}</p>
            <p>Current Highest Bid: $${parseFloat(data.highest_bid || car.base_price).toLocaleString()}</p>
          `;
          const currentHighest = parseFloat(data.highest_bid || car.base_price);
          const minBidRaw = Math.max(parseFloat(car.base_price), currentHighest + 0.01);
          const minBid = Math.ceil(minBidRaw); // allow clean integer minimum
          bidAmountInput.step = '0.01'; // ints still valid; decimals up to cents allowed
          bidAmountInput.min = minBid;
          bidAmountInput.placeholder = `Minimum: $${minBid.toFixed(2)}`;
        } else {
          carInfoDiv.innerHTML = '<p>Failed to load auction information.</p>';
        }
      })
      .catch(error => {
        console.error('Error fetching auction details:', error);
        carInfoDiv.innerHTML = '<p>Error loading auction details. Please try again.</p>';
      });

    // Load bids
    function loadBids() {
      fetch(`../api/api.php?action=bids&auctionId=${auctionId}`)
        .then(res => res.json())
        .then(data => {
          bidsTbody.innerHTML = '';
          if (data.success && data.bids && data.bids.length > 0) {
            data.bids.forEach(b => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td>${b.bidder_name}</td>
                <td>$${parseFloat(b.amount).toFixed(2)}</td>
                <td>${new Date(b.created_at).toLocaleString()}</td>
              `;
              bidsTbody.appendChild(tr);
            });
          } else {
            bidsTbody.innerHTML = `<tr><td colspan="3">No bids yet.</td></tr>`;
          }
        })
        .catch(error => {
          console.error('Error fetching bids:', error);
          bidsTbody.innerHTML = `<tr><td colspan="3">Error loading bids.</td></tr>`;
        });
    }
    loadBids();

    // Handle bid form
    bidForm.addEventListener('submit', e => {
      e.preventDefault();
      const formData = new FormData(bidForm);
      formData.append('action', 'place_bid');
      formData.append('auctionId', auctionId);

      const bidderName = bidForm.elements['bidderName'].value;
      const bidderEmail = bidForm.elements['bidderEmail'].value;
      const bidAmount = parseFloat(bidForm.elements['bidAmount'].value);

      fetch('../api/api.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            bidMsg.style.color = 'lightgreen';
            bidMsg.textContent = data.message || 'Bid placed successfully!';
            if (data.isHighest) {
              window.location.href = `transaction_form.php?auctionId=${auctionId}&bidderName=${encodeURIComponent(bidderName)}&bidderEmail=${encodeURIComponent(bidderEmail)}&finalPrice=${bidAmount}`;
            } else {
              bidForm.reset();
              loadBids(); // refresh bid table
            }
          } else {
            bidMsg.style.color = 'red';
            bidMsg.textContent = data.error || 'Failed to place bid.';
          }
        })
        .catch(error => {
          console.error('Error placing bid:', error);
          bidMsg.style.color = 'red';
          bidMsg.textContent = 'Error placing bid. Please try again.';
        });
    });
  </script>
</body>
</html>
