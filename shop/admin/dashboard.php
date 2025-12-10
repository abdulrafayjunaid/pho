<?php 
$page_title = 'Admin Dashboard';
include '../includes/header.php';

requireAdmin();

$stats = getStats();
$recent_orders = getOrders(null);
$recent_orders = array_slice($recent_orders, 0, 5);
?>

<div class="container-fluid my-4">
    <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Total Users</h6>
                            <h2 class="mb-0"><?php echo $stats['users']; ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="users.php" class="text-white small"><i class="fas fa-eye"></i> View All</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Total Products</h6>
                            <h2 class="mb-0"><?php echo $stats['products']; ?></h2>
                        </div>
                        <i class="fas fa-box fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="products.php" class="text-white small"><i class="fas fa-eye"></i> Manage Products</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Total Orders</h6>
                            <h2 class="mb-0"><?php echo $stats['orders']; ?></h2>
                        </div>
                        <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="orders.php" class="text-white small"><i class="fas fa-eye"></i> View Orders</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white-50">Total Revenue</h6>
                            <h2 class="mb-0"><?php echo formatPrice($stats['revenue']); ?></h2>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="orders.php" class="text-white small"><i class="fas fa-chart-line"></i> View Report</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="products.php?action=add" class="btn btn-primary me-2 mb-2">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                    <a href="categories.php?action=add" class="btn btn-success me-2 mb-2">
                        <i class="fas fa-plus"></i> Add Category
                    </a>
                    <a href="orders.php" class="btn btn-warning me-2 mb-2">
                        <i class="fas fa-list"></i> View Orders
                    </a>
                    <a href="users.php" class="btn btn-info me-2 mb-2">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Orders</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_orders)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'shipped' => 'primary',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $status_color = $status_class[$order['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $status_color; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($recent_orders)): ?>
                    <div class="card-footer text-center">
                        <a href="orders.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-list"></i> View All Orders
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
