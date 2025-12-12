<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Active Auctions - Car Auction</title>
    <link rel="stylesheet" href="../css/styles.css" />
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
                        <li><a href="auctions.php" class="active">View Auctions</a></li>
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
                <h2>Active Auctions</h2>
                <p>Browse current vehicle auctions and place your bids</p>
            </div>

            <input type="text" class="search-bar" placeholder="Search auctions...">

            <div class="grid-3" id="auctionsGrid"></div>
        </div>
    </main>

    <script>
        let allAuctions = [];

        async function loadAuctions() {
            try {
                const response = await fetch('../api/api.php?action=auctions');
                const data = await response.json();
                if (data.success) {
                    allAuctions = data.auctions;
                    renderAuctions(allAuctions);
                    setupSearch();
                } else {
                    alert('Failed to load auctions: ' + data.error);
                }
            } catch (error) {
                console.error('Error loading auctions:', error);
                alert('Error loading auctions. See console for details.');
            }
        }

        function renderAuctions(auctions) {
            const grid = document.getElementById('auctionsGrid');
            grid.innerHTML = '';
            auctions.forEach(auction => {
                const card = document.createElement('div');
                card.className = 'card';
                card.setAttribute('data-end-time', new Date(Date.now() + auction.time_remaining.total_seconds * 1000).toISOString());
                const timeRemaining = auction.time_remaining;
                const timeStr = timeRemaining.days > 0 ? `${timeRemaining.days}d ${timeRemaining.hours}h` : `${timeRemaining.hours}h ${timeRemaining.minutes}m`;
                card.innerHTML = `
                    <div style="width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #666;">No Image</div>
                    <h4>${auction.make} ${auction.model}</h4>
                    <p>Year: ${auction.year}</p>
                    <p>Color: ${auction.color_name}</p>
                    <p>Current Price: $${parseFloat(auction.current_price).toLocaleString()}</p>
                    <p>${auction.total_bids || 0} bids</p>
                    <p>Time Remaining: <span class="time-remaining">${timeStr}</span></p>
                    <a href="place_bid.php?auctionId=${auction.auction_id}" class="cta-button">Place Bid</a>
                `;
                grid.appendChild(card);
            });
            startCountdown();
        }

        function setupSearch() {
            const searchBar = document.querySelector('.search-bar');
            const datalist = document.createElement('datalist');
            datalist.id = 'auctionSuggestions';
            searchBar.setAttribute('list', 'auctionSuggestions');

            // Populate datalist with unique makes, models, and colors
            const suggestions = [...new Set(allAuctions.map(a => `${a.make} ${a.model}`).concat(allAuctions.map(a => a.make)).concat(allAuctions.map(a => a.color_name)))];
            suggestions.forEach(suggestion => {
                const option = document.createElement('option');
                option.value = suggestion;
                datalist.appendChild(option);
            });
            document.body.appendChild(datalist);

            searchBar.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const filtered = allAuctions.filter(auction => {
                    const fullName = `${auction.make} ${auction.model}`.toLowerCase();
                    return fullName.includes(query) ||
                           auction.make.toLowerCase().includes(query) ||
                           auction.model.toLowerCase().includes(query) ||
                           auction.color_name.toLowerCase().includes(query) ||
                           auction.year.toString().includes(query) ||
                           auction.current_price.toString().includes(query);
                });
                renderAuctions(filtered);
            });
        }

        function startCountdown() {
            const timeElements = document.querySelectorAll('.time-remaining');
            timeElements.forEach(element => {
                const card = element.closest('.card');
                const endTime = new Date(card.getAttribute('data-end-time')).getTime();
                const interval = setInterval(() => {
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    if (distance < 0) {
                        element.textContent = 'Expired';
                        clearInterval(interval);
                        return;
                    }
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    element.textContent = days > 0 ? `${days}d ${hours}h` : `${hours}h ${minutes}m`;
                }, 1000);
            });
        }

        document.addEventListener('DOMContentLoaded', loadAuctions);

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
<script src="../js/chatbot.js"></script>
</body>
</html>
