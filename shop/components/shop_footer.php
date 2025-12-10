<!-- Shop Footer -->
<footer class="shop-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-section">
                    <h5><i class="bi bi-phone"></i> PhoneStore</h5>
                    <p>Cửa hàng điện thoại uy tín, chất lượng cao với giá cả hợp lý. Chúng tôi cam kết mang đến cho khách hàng những sản phẩm chính hãng với dịch vụ tốt nhất.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-tiktok"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <div class="footer-section">
                    <h6>Sản phẩm</h6>
                    <ul class="footer-links">
                        <li><a href="products.php?category=1">iPhone</a></li>
                        <li><a href="products.php?category=2">Samsung</a></li>
                        <li><a href="products.php?category=3">Xiaomi</a></li>
                        <li><a href="products.php">Tất cả sản phẩm</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-section">
                    <h6>Hỗ trợ khách hàng</h6>
                    <ul class="footer-links">
                        <li><a href="support.php">Chính sách bảo hành</a></li>
                        <li><a href="support.php">Hướng dẫn mua hàng</a></li>
                        <li><a href="support.php">Chính sách đổi trả</a></li>
                        <li><a href="support.php">Câu hỏi thường gặp</a></li>
                        <li><a href="#" onclick="showOrderTrackingModal()">Tra cứu đơn hàng</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-section">
                    <h6>Liên hệ</h6>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>123 Đường Nguyễn Trãi, Quận 1, TP.HCM</span>
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-telephone"></i>
                            <span>0123 456 789</span>
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-envelope"></i>
                            <span>info@phonestore.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-clock"></i>
                            <span>8:00 - 22:00 (Thứ 2 - Chủ nhật)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr class="footer-divider">
        
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 PhoneStore Management System. Tất cả quyền được bảo lưu.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-nav">
                        <a href="about.php">Về chúng tôi</a>
                        <a href="contact.php">Liên hệ</a>
                        <a href="privacy.php">Chính sách bảo mật</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.shop-footer {
    background: var(--dark);
    color: white;
    padding: 50px 0 20px;
    margin-top: 80px;
}

.footer-section h5 {
    font-weight: 700;
    margin-bottom: 20px;
    color: white;
}

.footer-section h6 {
    font-weight: 700;
    margin-bottom: 20px;
    color: white;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

.footer-section p {
    color: rgba(255,255,255,0.8);
    line-height: 1.6;
    margin-bottom: 20px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 8px;
}

.footer-links a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: color 0.2s ease;
    font-size: 0.9rem;
}

.footer-links a:hover {
    color: white;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    color: white;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    color: rgba(255,255,255,0.8);
    font-size: 0.9rem;
}

.contact-item i {
    color: var(--primary);
    margin-top: 2px;
    flex-shrink: 0;
}

.footer-divider {
    border-color: rgba(255,255,255,0.2);
    margin: 40px 0 20px;
}

.footer-bottom {
    padding-top: 20px;
}

.footer-bottom p {
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
}

.footer-nav {
    display: flex;
    gap: 20px;
    justify-content: flex-end;
}

.footer-nav a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s ease;
}

.footer-nav a:hover {
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .shop-footer {
        padding: 40px 0 20px;
        margin-top: 60px;
    }
    
    .footer-nav {
        justify-content: flex-start;
        margin-top: 15px;
    }
    
    .social-links {
        justify-content: flex-start;
    }
}
</style>