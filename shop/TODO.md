# TODO: Fix Header Error in checkout.php

- [x] Move requireLogin() call before include 'includes/header.php'; in checkout.php to prevent header output before redirects
- [x] Add require_once 'includes/functions.php'; to define requireLogin() function before calling it
