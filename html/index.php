<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Auction Management System</title>
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
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="auctions.php">View Auctions</a></li>
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
                <h2>Welcome to Car Auction</h2>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-3" id="statsGrid">
                <div class="card">
                    <h3>📊 Active Auctions</h3>
                    <div class="stat-number" id="activeAuctions">0</div>
                    <p>Currently running auctions</p>
                </div>
                <div class="card">
                    <h3>💰 Total Volume</h3>
                    <div class="stat-number" id="totalVolume">$0</div>
                    <p>Transaction volume this month</p>
                </div>
                <div class="card">
                    <h3>🏆 Successful Sales</h3>
                    <div class="stat-number" id="successfulSales">0</div>
                    <p>Completed transactions</p>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="grid-2">
                <div class="card">
                    <h3>🔍 Browse Active Auctions</h3>
                    <a href="auctions.php" class="cta-button">View Auctions</a>
                </div>
                <div class="card">
                    <h3>➕ Add Your Car</h3>
                    <a href="add_car.php" class="cta-button">Add Car</a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <h3>Recent Activity</h3>
                <div id="recentActivity">
                    <p>Loading recent auctions...</p>
                </div>
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
            console.log('Theme toggled:', isDark ? 'dark' : 'light');
            console.log('Body classes:', body.className);
            console.log('CSS Variables applied - bg-color:', getComputedStyle(document.body).getPropertyValue('--bg-color'));
            console.log('CSS Variables applied - text-color:', getComputedStyle(document.body).getPropertyValue('--text-color'));
            console.log('CSS Variables applied - card-bg:', getComputedStyle(document.body).getPropertyValue('--card-bg'));
        });

        // Load stats and recent activity
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadRecentActivity();
        });

        // Counting Animation Function
        function animateNumber(element, target, duration = 1000) {
            const start = 0;
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function for smooth animation
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const current = Math.floor(start + (target - start) * easeOutQuart);

                element.textContent = current;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    element.textContent = target;
                }
            }

            requestAnimationFrame(update);
        }

        async function loadStats() {
            try {
                const response = await fetch('../api/api.php?action=stats');
                const data = await response.json();
                if (data.success) {
                    // Animate the numbers
                    const activeAuctionsEl = document.getElementById('activeAuctions');
                    const totalVolumeEl = document.getElementById('totalVolume');
                    const successfulSalesEl = document.getElementById('successfulSales');

                    // Extract numeric values
                    const activeAuctions = parseInt(data.activeAuctions) || 0;
                    const totalVolume = parseFloat(data.totalVolume.replace(/[$,]/g, '')) || 0;
                    const successfulSales = parseInt(data.successfulSales) || 0;

                    // Start animations with a slight delay for visual effect
                    setTimeout(() => animateNumber(activeAuctionsEl, activeAuctions), 200);
                    setTimeout(() => animateNumber(totalVolumeEl, totalVolume), 400);
                    setTimeout(() => animateNumber(successfulSalesEl, successfulSales), 600);
                } else {
                    // Fallback to demo if API fails
                    document.getElementById('activeAuctions').textContent = '0';
                    document.getElementById('totalVolume').textContent = '$0';
                    document.getElementById('successfulSales').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                // Fallback
                document.getElementById('activeAuctions').textContent = '0';
                document.getElementById('totalVolume').textContent = '$0';
                document.getElementById('successfulSales').textContent = '0';
            }
        }

        async function loadRecentActivity() {
            try {
                const response = await fetch('../api/api.php?action=auctions');
                const data = await response.json();

                if (data.success && data.auctions.length > 0) {
                    const recentAuctions = data.auctions.slice(0, 5);
                    let html = '';

                    recentAuctions.forEach(auction => {
                        const timeRemaining = auction.time_remaining || { days: 0, hours: 0, minutes: 0, total_seconds: 0 };
                        let timeText = '';

                        if (timeRemaining.total_seconds > 0) {
                            if (timeRemaining.days > 0) {
                                timeText = `${timeRemaining.days}d ${timeRemaining.hours}h remaining`;
                            } else if (timeRemaining.hours > 0) {
                                timeText = `${timeRemaining.hours}h ${timeRemaining.minutes}m remaining`;
                            } else {
                                timeText = `${timeRemaining.minutes}m remaining`;
                            }
                        } else {
                            timeText = 'Auction ended';
                        }

                        const currentPrice = auction.current_price || auction.base_price || 0;
                        const totalBids = auction.total_bids || 0;

                        html += `
                            <div class="activity-item">
                                <div class="activity-info">
                                    <h4>${auction.year || 'N/A'} ${auction.make || ''} ${auction.model || ''}</h4>
                                    <p>${timeText} • ${totalBids} bids</p>
                                </div>
                                <div class="activity-price">$${parseFloat(currentPrice).toLocaleString()}</div>
                            </div>
                        `;
                    });

                    document.getElementById('recentActivity').innerHTML = html;
                } else {
                    document.getElementById('recentActivity').innerHTML = '<p style="text-align: center; color: #666;">No recent activity to display</p>';
                }
            } catch (error) {
                console.error('Error loading recent activity:', error);
                document.getElementById('recentActivity').innerHTML = '<p style="text-align: center; color: #666;">Error loading activity</p>';
            }
        }
    </script>
<script src="../js/chatbot.js"></script>
</body>
</html>
