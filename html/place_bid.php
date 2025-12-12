<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Bid - Car Auction</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;600&display=swap">
    <style>
        .countdown-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .countdown-content {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }
        .countdown-timer {
            font-size: 4rem;
            font-weight: bold;
            color: var(--button-bg);
            margin: 20px 0;
            font-family: 'Roboto', sans-serif;
        }
        .countdown-message {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--text-color);
        }
        .countdown-submessage {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 30px;
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
                        <li><a href="transactions.php">Transactions</a></li>
                    </ul>
                </nav>
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">🌙</button>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="page-title">
                <h2>Place a Bid</h2>
                <p>Review the car details, check previous bids, and place your bid. If you're the highest bidder, proceed to transaction.</p>
            </div>

            <!-- Car Details -->
            <div class="car-details card">
                <h2>Car Information</h2>
                <p><strong>Make:</strong> <span id="carMake"></span></p>
                <p><strong>Model:</strong> <span id="carModel"></span></p>
                <p><strong>Year:</strong> <span id="carYear"></span></p>
                <p><strong>Mileage:</strong> <span id="carMileage"></span> km</p>
                <p><strong>Base Price:</strong> $<span id="carBasePrice"></span></p>
            </div>

            <!-- Previous Bids -->
            <div class="previous-bids card">
                <h2>Previous Bids</h2>
                <table id="bidsTable" class="striped">
                    <thead>
                        <tr>
                            <th>Bidder</th>
                            <th>Bid Amount ($)</th>
                            <th>Bid Time</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <p id="noBids" style="color: #666;">No bids yet.</p>
            </div>

            <!-- Place Bid Form -->
            <div class="card">
                <h2>Your Bid</h2>
                <form id="placeBidForm">
                    <input type="hidden" id="auctionId" name="auctionId" />
                    <div class="form-group">
                        <label for="bidderName">Bidder Name:</label>
                        <input type="text" id="bidderName" name="bidderName" required />
                    </div>
                    <div class="form-group">
                        <label for="bidderEmail">Bidder Email:</label>
                        <input type="email" id="bidderEmail" name="bidderEmail" required />
                    </div>
                    <p class="instruction">Bid must exceed current highest by at least $1.</p>
                    <div class="form-group">
                        <label for="bidAmount">Bid Amount ($):</label>
                        <input type="number" id="bidAmount" name="bidAmount" min="0" step="0.01" placeholder="Enter bid amount (e.g., 15000.00)" required />
                        <small>Enter a valid bid amount with up to 2 decimal places</small>
                    </div>
                    <button type="submit" class="cta-button">Submit Bid</button>
                </form>
            </div>
        </div>
    </main>

    <!-- JS -->
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const auctionId = urlParams.get('auctionId');
document.getElementById('auctionId').value = auctionId || '';

        if (!auctionId) {
            alert('Auction ID is required.');
            window.location.href = 'auctions.php';
        }

        // Fetch auction & car details
        fetch(`../api/api.php?action=auction&auctionId=${auctionId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error || !data.success) {
                    alert(data.error || 'Failed to load auction details');
                    document.querySelector('.car-details').innerHTML = '<p>Failed to load auction information.</p>';
                    return;
                }

                document.getElementById('carMake').textContent = data.car.make;
                document.getElementById('carModel').textContent = data.car.model;
                document.getElementById('carYear').textContent = data.car.year;
                document.getElementById('carMileage').textContent = data.car.mileage;
                document.getElementById('carBasePrice').textContent = parseFloat(data.car.base_price).toLocaleString();

                const minBid = Math.max(data.car.base_price, (data.highest_bid || 0) + 1);
                document.getElementById('bidAmount').min = minBid;
                document.getElementById('bidAmount').placeholder = `Minimum: $${minBid.toLocaleString()}`;
            })
            .catch(error => {
                console.error('Error fetching auction details:', error);
                alert('Error loading auction details. Please try again.');
                document.querySelector('.car-details').innerHTML = '<p>Failed to load auction information.</p>';
            });

        // Fetch previous bids
        fetch(`../api/api.php?action=bids&auctionId=${auctionId}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.querySelector('#bidsTable tbody');
                const noBids = document.getElementById('noBids');
                tbody.innerHTML = '';

                if (data.bids && data.bids.length > 0) {
                    noBids.style.display = 'none';
                    data.bids.forEach(bid => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${bid.bidder_name}</td>
                            <td>${bid.amount}</td>
                            <td>${new Date(bid.created_at).toLocaleString()}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    noBids.style.display = 'block';
                }
            });

        // Submit bid
        document.getElementById('placeBidForm').addEventListener('submit', e => {
            e.preventDefault();

            const bidderName = document.getElementById('bidderName').value;
            const bidderEmail = document.getElementById('bidderEmail').value;
            const bidAmount = parseFloat(document.getElementById('bidAmount').value);

            const formData = new FormData();
            formData.append('action', 'place_bid');
            formData.append('auctionId', auctionId);
            formData.append('bidderName', bidderName);
            formData.append('bidderEmail', bidderEmail);
            formData.append('bidAmount', bidAmount);

            fetch('../api/api.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.isHighest) {
                            // Start countdown for highest bidder
                            startCountdown(bidderName, bidderEmail, bidAmount);
                        } else {
                            alert('Bid placed successfully! You are not the highest bidder yet.');
                            location.reload();
                        }
                    } else {
                        alert('Error placing bid: ' + data.error);
                    }
                });
        });

        // Countdown function for highest bidder
        function startCountdown(bidderName, bidderEmail, bidAmount) {
            // Create countdown modal
            const modal = document.createElement('div');
            modal.className = 'countdown-modal';
            modal.innerHTML = `
                <div class="countdown-content">
                    <h2>🎉 You're the Highest Bidder!</h2>
                    <div class="countdown-message">Auction will close in:</div>
                    <div class="countdown-timer" id="countdownTimer">15</div>
                    <div class="countdown-submessage">If no higher bids are placed, this auction will be automatically completed.</div>
                </div>
            `;
            document.body.appendChild(modal);

            let timeLeft = 15;
            const timerElement = document.getElementById('countdownTimer');

            const countdownInterval = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;

                // Check for higher bids every second
                checkForHigherBids(auctionId, bidAmount).then(hasHigherBid => {
                    if (hasHigherBid) {
                        clearInterval(countdownInterval);
                        modal.remove();
                        alert('A higher bid was placed! Try bidding again.');
                        location.reload();
                        return;
                    }
                });

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    modal.remove();
                    // Auto-complete the transaction
                    autoCompleteTransaction(auctionId, bidderEmail, bidAmount);
                }
            }, 1000);
        }

        // Check if a higher bid has been placed
        async function checkForHigherBids(auctionId, currentBid) {
            try {
                const response = await fetch(`../api/api.php?action=check_higher_bids&auctionId=${auctionId}&currentBid=${currentBid}`);
                const data = await response.json();
                return data.hasHigherBid || false;
            } catch (error) {
                console.error('Error checking for higher bids:', error);
                return false;
            }
        }

        // Auto-complete the transaction
        async function autoCompleteTransaction(auctionId, bidderEmail, finalPrice) {
            try {
                const formData = new FormData();
                formData.append('action', 'auto_complete_transaction');
                formData.append('auctionId', auctionId);
                formData.append('buyerEmail', bidderEmail);
                formData.append('finalPrice', finalPrice);
                formData.append('paymentMethod', 'Card'); // Default to Card for auto-completion
                formData.append('cardNumber', '0000000000000000'); // Dummy card for auto-completion

                const response = await fetch('../api/api.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    alert('Congratulations! Auction completed successfully. You won the car!');
                    window.location.href = 'index.php';
                } else {
                    alert('Error completing transaction: ' + data.error);
                    // Fallback to manual transaction form
                    window.location.href = `transaction_form.php?auctionId=${auctionId}&bidderEmail=${encodeURIComponent(bidderEmail)}&finalPrice=${finalPrice}`;
                }
            } catch (error) {
                console.error('Error auto-completing transaction:', error);
                alert('Error completing transaction. Please complete manually.');
                window.location.href = `transaction_form.php?auctionId=${auctionId}&bidderEmail=${encodeURIComponent(bidderEmail)}&finalPrice=${finalPrice}`;
            }
        }

        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            themeToggle.textContent = '☀️';
        }

        // Toggle theme
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            themeToggle.textContent = isDark ? '☀️' : '🌙';
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
<script src="../js/chatbot.js"></script>

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
</body>
