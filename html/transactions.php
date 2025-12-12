<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transactions</title>
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
                    <li><a href="auctions.php">Auctions</a></li>
                    <li><a href="add_car.php">Add Car</a></li>
                    <li><a href="transactions.php" class="active">Transactions</a></li>
                </ul>
            </nav>
            <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">🌙</button>
        </div>
    </div>
</header>

<main class="container">
    <div class="page-title">
        <h2>Completed Transactions</h2>
        <p>View all finalized auction transactions</p>
    </div>

    <input type="text" class="search-bar" placeholder="Search transactions...">

    <table id="transactionsTable" class="striped" border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
    <tr><th>ID</th><th>Auction ID</th><th>Buyer</th><th>Email</th><th>Final Price</th><th>Payment</th><th>Date</th></tr>
    </thead>
    <tbody></tbody>
    </table>

<script>
    let allTransactions = [];

    async function loadTransactions() {
        try {
            const response = await fetch('../api/api.php?action=transactions');
            const data = await response.json();
            if (data.success) {
                allTransactions = data.transactions;
                renderTransactions(allTransactions);
                setupSearch();
            } else {
                alert('Failed to load transactions: ' + data.error);
            }
        } catch (error) {
            console.error('Error loading transactions:', error);
            alert('Error loading transactions. See console for details.');
        }
    }

    function renderTransactions(transactions) {
        const tbody = document.querySelector('#transactionsTable tbody');
        tbody.innerHTML = '';
        transactions.forEach(tx => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${tx.transaction_id}</td>
                <td>${tx.auction_id}</td>
                <td>${tx.buyer}</td>
                <td>${tx.buyer_email}</td>
                <td>$${parseFloat(tx.final_price).toLocaleString()}</td>
                <td>${tx.payment_method}</td>
                <td>${tx.transaction_date}</td>
            `;
            tbody.appendChild(row);
        });
    }

    function setupSearch() {
        const searchBar = document.querySelector('.search-bar');
        searchBar.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allTransactions.filter(tx => 
                tx.buyer.toLowerCase().includes(query) ||
                tx.buyer_email.toLowerCase().includes(query) ||
                tx.payment_method.toLowerCase().includes(query) ||
                tx.transaction_date.includes(query) ||
                tx.auction_id.toString().includes(query) ||
                tx.final_price.toString().includes(query)
            );
            renderTransactions(filtered);
        });
    }

    document.addEventListener('DOMContentLoaded', loadTransactions);

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
<script src="../js/chatbot.js"></script>
</body>
</html>
