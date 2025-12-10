<?php
ob_start();
require_once 'config.php';
require_once 'functions.php';

$cart_count = 0;
if (isLoggedIn()) {
    $cart_count = getCartCount($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #001f3f;
            --secondary-color: #003366;
            --dark-color: #333;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            margin: 0 10px;
            transition: all 0.3s;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .badge-cart {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff9800;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.3);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .card-img-top {
            height: 250px;
            object-fit: cover;
        }
        
        .product-price {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .category-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-card:hover {
            transform: scale(1.05);
        }
        
        footer {
            background: var(--dark-color);
            color: white;
            padding: 30px 0;
            margin-top: auto;
        }
        
        .search-form {
            max-width: 400px;
        }
        
        .cart-icon {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-bag"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-box"></i> Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php"><i class="fas fa-th-large"></i> Categories</a>
                    </li>
                </ul>
                
                <form class="d-flex search-form me-3" action="products.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link cart-icon" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge bg-warning badge-cart"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="orders.php">
                                    <i class="fas fa-box"></i> My Orders</a>
                                </li>
                                <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/shop/admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Admin Panel</a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main>
