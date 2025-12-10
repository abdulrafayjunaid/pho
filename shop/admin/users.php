<?php 
$page_title = 'Manage Users';
include '../includes/header.php';

requireAdmin();

global $db;
$result = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid my-4">
    <h1 class="mb-4"><i class="fas fa-users"></i> Manage Users</h1>
    
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
