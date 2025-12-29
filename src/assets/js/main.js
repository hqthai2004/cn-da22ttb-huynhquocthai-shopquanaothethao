// Lấy base URL
function getBaseUrl() {
    const path = window.location.pathname;
    const parts = path.split('/');
    // Tìm thư mục gốc (shopquanao)
    const rootIndex = parts.findIndex(p => p === 'shopquanao');
    if (rootIndex !== -1) {
        return '/' + parts.slice(1, rootIndex + 1).join('/');
    }
    return '';
}

// Thêm sản phẩm vào giỏ hàng
function addToCart(productId) {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/cart-add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
        .then(response => response.json())
        .then(data => {
            console.log('Cart add response:', data); // Debug
            if (data.success) {
                // Cập nhật số lượng giỏ hàng từ response
                const cartCountElement = document.getElementById('cart-count');
                console.log('Cart count element:', cartCountElement); // Debug
                console.log('New cart count:', data.cart_count); // Debug

                if (cartCountElement && data.cart_count !== undefined) {
                    cartCountElement.textContent = data.cart_count;
                }
                showNotification('✅ Đã thêm vào giỏ hàng!', 'success');
            } else {
                showNotification('❌ ' + (data.message || 'Có lỗi xảy ra'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('❌ Có lỗi xảy ra', 'error');
        });
}

// Cập nhật số lượng giỏ hàng
function updateCartCount() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Cập nhật số lượng wishlist
function updateWishlistCount() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/wishlist-count.php')
        .then(response => response.json())
        .then(data => {
            const wishlistBadge = document.querySelector('a[href="wishlist.php"] .badge');
            if (data.count > 0) {
                if (wishlistBadge) {
                    wishlistBadge.textContent = data.count;
                } else {
                    // Tạo badge mới nếu chưa có
                    const wishlistLink = document.querySelector('a[href="wishlist.php"]');
                    if (wishlistLink) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle';
                        badge.textContent = data.count;
                        wishlistLink.appendChild(badge);
                    }
                }
            } else {
                // Xóa badge nếu không còn sản phẩm
                if (wishlistBadge) {
                    wishlistBadge.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error updating wishlist count:', error);
        });
}

// Cập nhật cart count và wishlist count khi trang load
document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    updateWishlistCount();
    initNavbarEffects();
});

// Khởi tạo hiệu ứng navbar
function initNavbarEffects() {
    const navbar = document.querySelector('.modern-navbar');

    // Hiệu ứng scroll
    window.addEventListener('scroll', function () {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Hiệu ứng hover cho nav links
    const navLinks = document.querySelectorAll('.modern-nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px) scale(1.05)';
        });

        link.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Hiệu ứng cho action buttons
    const actionLinks = document.querySelectorAll('.modern-action-link');
    actionLinks.forEach(link => {
        link.addEventListener('mouseenter', function () {
            this.style.transform = 'scale(1.15) rotate(5deg)';
        });

        link.addEventListener('mouseleave', function () {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });

    // Hiệu ứng cho search input
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('focus', function () {
            this.parentElement.style.transform = 'scale(1.05)';
            this.parentElement.style.boxShadow = '0 0 30px rgba(255, 255, 255, 0.2)';
        });

        searchInput.addEventListener('blur', function () {
            this.parentElement.style.transform = 'scale(1)';
            this.parentElement.style.boxShadow = 'none';
        });
    }
}

// Hiển thị thông báo
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Xác nhận xóa
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}

// Format số tiền
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}
// Modern Navigation JavaScript
document.addEventListener('DOMContentLoaded', function () {
    initModernNavigation();
});

function initModernNavigation() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', function () {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });
    }

    // Mobile dropdown toggle
    const mobileDropdowns = document.querySelectorAll('.mobile-dropdown .dropdown-title');
    mobileDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function () {
            const parent = this.parentElement;
            parent.classList.toggle('active');
        });
    });

    // Scroll effect for header
    const header = document.querySelector('.modern-header');
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.modern-header')) {
            mobileMenu?.classList.remove('active');
            mobileToggle?.classList.remove('active');
        }
    });

    // Close mobile menu when window resizes
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            mobileMenu?.classList.remove('active');
            mobileToggle?.classList.remove('active');
        }
    });

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Add loading animation to navigation links
    const navLinks = document.querySelectorAll('.nav-item, .mobile-item');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            if (this.getAttribute('href') && !this.getAttribute('href').startsWith('#')) {
                this.style.opacity = '0.7';
                this.style.transform = 'scale(0.95)';
            }
        });
    });

    // Enhanced cart and wishlist animations
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
}

// Update cart count with animation
function updateCartCountAnimated() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-btn .badge');
            if (cartBadge) {
                // Animation effect
                cartBadge.style.transform = 'scale(1.5)';
                cartBadge.style.background = '#e74c3c';
                cartBadge.textContent = data.count;

                setTimeout(() => {
                    cartBadge.style.transform = 'scale(1)';
                    cartBadge.style.background = '#f39c12';
                }, 300);
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Update wishlist count with animation
function updateWishlistCountAnimated() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/wishlist-count.php')
        .then(response => response.json())
        .then(data => {
            const wishlistBadge = document.querySelector('.wishlist-btn .badge');
            const wishlistBtn = document.querySelector('.wishlist-btn');

            if (data.count > 0) {
                if (wishlistBadge) {
                    wishlistBadge.textContent = data.count;
                    wishlistBadge.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        wishlistBadge.style.transform = 'scale(1)';
                    }, 300);
                } else if (wishlistBtn) {
                    // Create new badge
                    const badge = document.createElement('span');
                    badge.className = 'badge';
                    badge.textContent = data.count;
                    badge.style.transform = 'scale(0)';
                    wishlistBtn.appendChild(badge);

                    setTimeout(() => {
                        badge.style.transform = 'scale(1)';
                    }, 100);
                }
            } else {
                // Remove badge if count is 0
                if (wishlistBadge) {
                    wishlistBadge.style.transform = 'scale(0)';
                    setTimeout(() => {
                        wishlistBadge.remove();
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error updating wishlist count:', error);
        });
}

// Override existing functions with animated versions
updateCartCount = updateCartCountAnimated;
updateWishlistCount = updateWishlistCountAnimated;
// Simple Navigation JavaScript
document.addEventListener('DOMContentLoaded', function () {
    initSimpleNavigation();
});

function initSimpleNavigation() {
    // Scroll effect for navbar
    const navbar = document.querySelector('.simple-nav');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Enhanced action link animations
    const actionLinks = document.querySelectorAll('.action-link');
    actionLinks.forEach(link => {
        link.addEventListener('click', function () {
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
}

// Enhanced cart count update
function updateCartCountEnhanced() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('#cart-count');
            if (cartBadge) {
                cartBadge.textContent = data.count;
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Enhanced wishlist count update
function updateWishlistCountEnhanced() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/wishlist-count.php')
        .then(response => response.json())
        .then(data => {
            const wishlistLink = document.querySelector('.wishlist-link');
            let wishlistBadge = wishlistLink?.querySelector('.badge');

            if (data.count > 0) {
                if (wishlistBadge) {
                    wishlistBadge.textContent = data.count;
                } else if (wishlistLink) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-danger';
                    badge.textContent = data.count;
                    wishlistLink.appendChild(badge);
                }
            } else {
                if (wishlistBadge) {
                    wishlistBadge.style.transform = 'scale(0)';
                    setTimeout(() => {
                        wishlistBadge.remove();
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error updating wishlist count:', error);
        });
}
// Toggle wishlist from product card
function toggleWishlistFromCard(productId, button) {
    const baseUrl = getBaseUrl();
    const icon = button.querySelector('i');
    const isInWishlist = button.dataset.inWishlist === '1';

    fetch(baseUrl + '/api/wishlist-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.action === 'added') {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    button.dataset.inWishlist = '1';
                    button.classList.add('text-danger');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    button.dataset.inWishlist = '0';
                    button.classList.remove('text-danger');
                }
                showNotification(data.message, 'success');
                // Cập nhật số lượng wishlist trong header
                updateWishlistCount();
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra', 'error');
        });
}
// Cải thiện toggle wishlist from product card với animation
function toggleWishlistFromCard(productId, button) {
    const baseUrl = getBaseUrl();
    const icon = button.querySelector('i');

    // Thêm animation loading
    button.disabled = true;
    icon.classList.add('fa-spin');

    fetch(baseUrl + '/api/wishlist-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
        .then(response => response.json())
        .then(data => {
            // Xóa loading animation
            button.disabled = false;
            icon.classList.remove('fa-spin');

            if (data.success) {
                if (data.action === 'added') {
                    // Thêm vào yêu thích
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    button.dataset.inWishlist = '1';
                    button.classList.add('text-danger');

                    // Animation bounce effect
                    icon.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        icon.style.transform = 'scale(1)';
                    }, 200);

                    showNotification('❤️ ' + data.message, 'success');
                } else {
                    // Xóa khỏi yêu thích
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    button.dataset.inWishlist = '0';
                    button.classList.remove('text-danger');

                    showNotification('💔 ' + data.message, 'info');
                }

                // Cập nhật số lượng wishlist trong header ngay lập tức
                updateWishlistCountImmediate();
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            // Xóa loading animation khi có lỗi
            button.disabled = false;
            icon.classList.remove('fa-spin');
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra', 'error');
        });
}

// Cập nhật wishlist count ngay lập tức với animation
function updateWishlistCountImmediate() {
    const baseUrl = getBaseUrl();
    fetch(baseUrl + '/api/wishlist-count.php')
        .then(response => response.json())
        .then(data => {
            const wishlistLink = document.querySelector('a[href="wishlist.php"]');
            let wishlistBadge = wishlistLink?.querySelector('.badge');

            if (data.count > 0) {
                if (wishlistBadge) {
                    // Cập nhật số lượng với animation
                    wishlistBadge.style.transform = 'scale(1.5)';
                    wishlistBadge.style.background = '#28a745';
                    wishlistBadge.textContent = data.count;

                    setTimeout(() => {
                        wishlistBadge.style.transform = 'scale(1)';
                        wishlistBadge.style.background = '#dc3545';
                    }, 300);
                } else if (wishlistLink) {
                    // Tạo badge mới với animation
                    const badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    badge.textContent = data.count;
                    badge.style.transform = 'scale(0)';
                    wishlistLink.appendChild(badge);

                    setTimeout(() => {
                        badge.style.transform = 'scale(1)';
                        badge.style.transition = 'transform 0.3s ease';
                    }, 100);
                }
            } else {
                // Xóa badge nếu không còn sản phẩm yêu thích
                if (wishlistBadge) {
                    wishlistBadge.style.transform = 'scale(0)';
                    setTimeout(() => {
                        wishlistBadge.remove();
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error updating wishlist count:', error);
        });
}