 <!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Car</title>
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
                    <li><a href="add_car.php" class="active">Add Car</a></li>
                    <li><a href="transactions.php">Transactions</a></li>
                </ul>
            </nav>
            <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">🌙</button>
        </div>
    </div>
</header>

<main class="container" style="display: flex; flex-direction: column; align-items: center; gap: 24px; padding: 30px 0;">
    <div class="page-title" style="text-align: center;">
        <h2>Manage Cars</h2>
        <p>Add new cars or delete existing ones from the auction system</p>
    </div>

    <!-- Add Car Section -->
    <div class="card" style="width: 760px; max-width: 95vw; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 8px 32px rgba(0,0,0,0.1); border-radius: 20px; margin: 0 auto;">
        <h3>Add a Car</h3>
        <form id="addCarForm">
            <div class="form-group">
                <label>Car Name:</label>
                <input type="text" name="carName" required placeholder="Make Model" class="futuristic-input">
                <small>e.g., Toyota Camry</small>
            </div>
            <div class="form-group">
                <label>Model Year:</label>
                <input type="number" name="modelYear" required class="futuristic-input">
                <small>e.g., 2020</small>
            </div>
            <div class="form-group">
                <label>Mileage:</label>
                <input type="number" name="mileage" required class="futuristic-input">
                <small>e.g., 25000 miles</small>
            </div>
            <div class="form-group">
                <label>Color:</label>
                <select name="colorId" id="colorSelect" required class="futuristic-input">
                    <option value="">Select Color</option>
                </select>
                <small>Choose the car's color</small>
            </div>
            <div class="form-group">
                <label>Base Price:</label>
                <input type="number" step="0.01" name="basePrice" required class="futuristic-input">
                <small>Starting bid amount in USD</small>
            </div>
            <button type="submit" class="cta-button futuristic-button" id="addCarBtn">
                <span class="btn-text">Add Car</span>
                <span class="loading-spinner" style="display: none;">⏳</span>
            </button>
        </form>
        <div id="msg" class="alert" style="display: none;"></div>
    </div>

    <!-- Existing Cars Section -->
    <div class="card" style="width: 760px; max-width: 95vw; margin: 0 auto 20px;">
        <h3>Existing Cars</h3>
        <div id="carsLoading" class="loading-indicator" style="display: none; text-align: center; padding: 20px;">
            <span>⏳ Loading cars...</span>
        </div>
        <div id="carsList" class="cars-list">
            <!-- Cars will be loaded here -->
        </div>
        <div id="carsError" class="alert alert-error" style="display: none;">
            Failed to load cars. Please check your connection and try again.
        </div>
    </div>
</main>

<script>
// Load colors on page load
fetch('../api/api.php?action=colors')
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const select = document.getElementById('colorSelect');
      data.colors.forEach(color => {
        const option = document.createElement('option');
        option.value = color.color_id;
        option.textContent = color.color_name;
        select.appendChild(option);
      });
    }
  })
  .catch(console.error);

document.getElementById('addCarForm').addEventListener('submit', async e=>{
  e.preventDefault();

  // Show loading state
  const btn = document.getElementById('addCarBtn');
  const btnText = btn.querySelector('.btn-text');
  const spinner = btn.querySelector('.loading-spinner');
  const msgDiv = document.getElementById('msg');

  btn.disabled = true;
  btnText.textContent = 'Adding Car...';
  spinner.style.display = 'inline';
  msgDiv.style.display = 'none';

  try {
    const formData = new FormData(e.target);
    const response = await fetch('../api/api.php?action=add_car', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Network error: ${response.status}`);
    }

    const data = await response.json();

    // Hide loading state
    btn.disabled = false;
    btnText.textContent = 'Add Car';
    spinner.style.display = 'none';

    // Show message
    msgDiv.style.display = 'block';
    msgDiv.className = data.success ? 'alert alert-success' : 'alert alert-error';
    msgDiv.textContent = data.success ? 'Car added successfully!' : 'Error: ' + data.error;

    if (data.success) {
      e.target.reset(); // Clear form
      loadCars(); // Refresh the cars list
    }
  } catch (error) {
    console.error('Error adding car:', error);

    // Hide loading state
    btn.disabled = false;
    btnText.textContent = 'Add Car';
    spinner.style.display = 'none';

    // Show error message
    msgDiv.style.display = 'block';
    msgDiv.className = 'alert alert-error';
    msgDiv.textContent = 'Network error: Please check your connection and try again.';
  }
});

// Load existing cars
async function loadCars() {
  const carsLoading = document.getElementById('carsLoading');
  const carsList = document.getElementById('carsList');
  const carsError = document.getElementById('carsError');

  // Show loading, hide others
  carsLoading.style.display = 'block';
  carsList.style.display = 'none';
  carsError.style.display = 'none';

  try {
    const response = await fetch('../api/api.php?action=get_cars');

    if (!response.ok) {
      throw new Error(`Network error: ${response.status}`);
    }

    const data = await response.json();

    // Hide loading
    carsLoading.style.display = 'none';

    if (data.success) {
      carsList.innerHTML = '';
      data.cars.forEach(car => {
        const carDiv = document.createElement('div');
        carDiv.className = 'car-item';
        let statusText = '';
        let canDelete = true;

        if (car.auction_status === 'active_auction') {
          statusText = '<span style="color: #e74c3c; font-weight: bold;">⚠️ Currently in active auction - cannot delete</span>';
          canDelete = false;
        } else if (car.total_bids > 0) {
          statusText = '<span style="color: #f39c12; font-weight: bold;">⚠️ Has existing bids - cannot delete</span>';
          canDelete = false;
        } else if (car.auction_status === 'has_auction') {
          statusText = '<span style="color: #27ae60; font-weight: bold;">✓ Auction completed - can delete</span>';
        } else {
          statusText = '<span style="color: #27ae60; font-weight: bold;">✓ No auction - can delete</span>';
        }

        carDiv.innerHTML = `
          <div class="car-info">
            <h4>${car.make} ${car.model}</h4>
            <p>Year: ${car.year} | Mileage: ${car.mileage} | Color: ${car.color_name} | Base Price: $${car.base_price}</p>
            <p class="car-status">${statusText}</p>
          </div>
          <button class="delete-btn ${canDelete ? '' : 'disabled'}" data-car-id="${car.car_id}" ${canDelete ? '' : 'disabled'}>Delete</button>
        `;
        carsList.appendChild(carDiv);
      });

      // Show the list
      carsList.style.display = 'block';

      // Add event listeners to delete buttons
      document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          const carId = e.target.getAttribute('data-car-id');
          if (confirm('Are you sure you want to delete this car?')) {
            try {
              const response = await fetch('../api/api.php?action=delete_car', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `carId=${carId}`
              });
              const data = await response.json();
              if (data.success) {
                alert('Car deleted successfully!');
                loadCars(); // Refresh the list
              } else {
                alert('Error: ' + data.error);
              }
            } catch (error) {
              console.error('Error deleting car:', error);
              alert('Network error: Please check your connection and try again.');
            }
          }
        });
      });
    } else {
      throw new Error(data.error || 'Failed to load cars');
    }
  } catch (error) {
    console.error('Error loading cars:', error);

    // Hide loading, show error
    carsLoading.style.display = 'none';
    carsError.style.display = 'block';
    carsError.textContent = 'Failed to load cars. Please check your connection and try again.';
  }
}

// Load cars on page load
document.addEventListener('DOMContentLoaded', loadCars);

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
</body>
