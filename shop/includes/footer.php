    </main>
    
    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-shopping-bag"></i> <?php echo SITE_NAME; ?></h5>
                    <p>Your one-stop shop for greeting cards, gift articles, handbags, beauty products, and more!</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="products.php" class="text-white-50">Products</a></li>
                        <li><a href="categories.php" class="text-white-50">Categories</a></li>
                        <li><a href="cart.php" class="text-white-50">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope"></i> <?php echo ADMIN_EMAIL; ?><br>
                        <i class="fas fa-phone"></i> +1 (555) 123-4567<br>
                        <i class="fas fa-map-marker-alt"></i> 123 Shopping Street
                    </p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 3 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 3000);
        
        // Confirm delete actions
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }
        
        // Add to cart with animation
        function addToCartAnimation(button) {
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.add('fa-spin');
                setTimeout(() => {
                    icon.classList.remove('fa-spin');
                }, 500);
            }
        }
    </script>
</body>
</html>
