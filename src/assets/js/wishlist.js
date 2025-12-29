// Wishlist Complete Functionality

// Toggle wishlist from product card
function toggleWishlistFromCard(productId, button) {
    const baseUrl = getBaseUrl();
    const icon = button.querySelector('i');

    // Disable button và thêm loading
    button.disabled = true;
    const originalClass = icon.className;
    icon.className = 'fas fa-spinner fa-spin';

    fetch(baseUrl + '/api/wishlist-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: parseInt(productId)
        })
    })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;

            if (data.success) {
                if (data.action === 'added') {
                    // Thêm vào yêu thích
                    icon.className = 'fas fa-heart';
                    icon.style.color = '#dc3545';
                    button.dataset.inWishlist = '1';
                    button.title = 'Xóa khỏi yêu thích';

                    // Animation
                    icon.style.transform = 'scale(1.4)';
                    setTimeout(() => {
                        icon.style.transform = 'scale(1)';
                    }, 300);

                    showNotification('❤️ Đã thêm vào yêu thích!', 'success');
                } else {
                    // Xóa khỏi yêu thích
                    icon.className = 'far fa-heart';
                    icon.style.color = '#6c757d';
                    button.dataset.inWishlist = '0';
                    button.title = 'Thêm vào yêu thích';

                    showNotification('💔 Đã xóa khỏi yêu thích!', 'info');
                }

                // Cập nhật badge với delay nhỏ để đảm bảo DB đã cập nhật
                setTimeout(() => {
                    updateWishlistBadge();
                }, 100);
            } else {
                icon.className = originalClass;
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            button.disabled = false;
            icon.className = originalClass;
            console.error('Error:', error);
            showNotification('Không thể kết nối server', 'error');
        });
}

// Cập nhật badge wishlist
function updateWishlistBadge() {
    const baseUrl = getBaseUrl();
    console.log('Updating wishlist badge...');

    // Thêm timestamp để tránh cache
    const timestamp = new Date().getTime();
    fetch(baseUrl + '/api/wishlist-count.php?t=' + timestamp)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Wishlist count response:', data);
            // Chỉ tìm wishlist link trong header/navbar (không phải trong product cards)
            const wishlistLinks = document.querySelectorAll('header a[href="wishlist.php"], .navbar a[href="wishlist.php"], .nav a[href="wishlist.php"]');
            console.log('Found wishlist links:', wishlistLinks.length);

            // Lấy count từ response (tương thích với cả format cũ và mới)
            const count = data.count || 0;
            console.log('Processed count:', count);

            wishlistLinks.forEach(link => {
                let badge = link.querySelector('.badge');
                console.log('Current badge:', badge, 'New count:', count);

                if (count > 0) {
                    if (badge) {
                        // Cập nhật badge có sẵn
                        badge.textContent = count;
                    } else {
                        // Tạo badge mới nếu chưa có
                        badge = document.createElement('span');
                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        badge.textContent = count;
                        badge.style.fontSize = '0.7rem';
                        link.appendChild(badge);
                    }
                } else {
                    // Xóa badge nếu count = 0
                    if (badge) {
                        badge.remove();
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error updating wishlist badge:', error);
        });
}

// Khởi tạo
document.addEventListener('DOMContentLoaded', function () {
    // XÓA MẠNH MẼ TẤT CẢ BADGE SỐ TRONG PRODUCT CARDS
    const removeWishlistBadgesFromCards = () => {
        // Tìm tất cả product cards
        const productCards = document.querySelectorAll('.product-card, .card');
        productCards.forEach(card => {
            // Xóa tất cả badge có số
            const numberBadges = card.querySelectorAll('.badge');
            numberBadges.forEach(badge => {
                const text = badge.textContent.trim();
                // Xóa nếu là số hoặc có class translate-middle
                if (text.match(/^\d+$/) || badge.classList.contains('translate-middle') || badge.classList.contains('start-100')) {
                    console.log('Removing badge:', text, badge.className);
                    badge.remove();
                }
            });

            // Xóa tất cả span có số bên trong wishlist button
            const wishlistBtns = card.querySelectorAll('.wishlist-btn, button[data-product-id]');
            wishlistBtns.forEach(btn => {
                const spans = btn.querySelectorAll('span');
                spans.forEach(span => {
                    if (span.textContent.trim().match(/^\d+$/)) {
                        console.log('Removing span with number:', span.textContent);
                        span.remove();
                    }
                });
            });
        });
    };

    // Chạy cleanup liên tục để đảm bảo xóa hết số 7
    removeWishlistBadgesFromCards();

    // Chạy cleanup mỗi giây trong 10 giây đầu
    for (let i = 1; i <= 10; i++) {
        setTimeout(removeWishlistBadgesFromCards, i * 1000);
    }

    // Chạy cleanup mỗi khi có thay đổi DOM
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'childList') {
                removeWishlistBadgesFromCards();
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Cập nhật badge khi load trang
    updateWishlistBadge();

    // Event delegation cho wishlist buttons
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.wishlist-btn');
        if (button) {
            e.preventDefault();
            const productId = button.dataset.productId;
            if (productId) {
                toggleWishlistFromCard(productId, button);
            }
        }
    });
});

// Export global
window.toggleWishlistFromCard = toggleWishlistFromCard;
window.updateWishlistBadge = updateWishlistBadge;