    </div>
            </div>
        </div>

        <footer class="footer mt-5 py-4 bg-dark text-white">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-motorcycle"></i> Patra Jaya Variasi
                        </h5>
                        <p class="text-light small">
                            Tempatnya Modif Motor NO.1 Balikpapan
                        </p>
                        <div class="mt-3 social-links">
                            <a href="#" class="text-white me-3" title="Facebook"><i class="fab fa-facebook fa-lg"></i></a>
                            <a href="#" class="text-white me-3" title="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
                            <a href="#" class="text-white" title="WhatsApp"><i class="fab fa-whatsapp fa-lg"></i></a>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <h5 class="mb-3">
                            <i class="fas fa-address-book"></i> Kontak Kami
                        </h5>
                        <ul class="list-unstyled text-light small">
                            <li class="mb-2"><i class="fas fa-map-marker-alt text-danger"></i> Jl. Soekarno Hatta Km. 21 Rt. 41</li>
                            <li class="mb-2"><i class="fas fa-phone text-success"></i> <a href="tel:+6281351319657" class="text-light text-decoration-none">081351319657</a></li>
                            <li class="mb-2"><i class="fas fa-envelope text-primary"></i> <a href="mailto:10241061@student.itk.ac.id" class="text-light text-decoration-none">10241061@student.itk.ac.id</a></li>
                            <li class="mb-2"><i class="fas fa-clock text-warning"></i> Senin - Sabtu: 08:00 - 17:00</li>
                        </ul>
                    </div>
                </div>

                <hr class="my-4 bg-secondary">

                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-2">
                        <p class="mb-0 small text-muted">
                            &copy; <?= date('Y') ?> Patra Jaya Variasi, Balikpapan Utara
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0 small ">
                            Powered by <i class=></i> 
                            <strong>Patra Ananda 1061</strong>
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            window.addEventListener('scroll', function() {
                const scrollBtn = document.getElementById('scrollTopBtn');
                if (scrollBtn) {
                    if (window.pageYOffset > 300) {
                        scrollBtn.style.display = 'block';
                    } else {
                        scrollBtn.style.display = 'none';
                    }
                }
            });
            function scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        </script>
        <button onclick="scrollToTop()" id="scrollTopBtn" 
                style="display: none; position: fixed; bottom: 20px; right: 20px; 
                    z-index: 99; border: none; outline: none; 
                    background-color: #0d6efd; color: white; 
                    cursor: pointer; padding: 15px; border-radius: 50%; 
                    font-size: 18px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            <i class="fas fa-arrow-up"></i>
        </button>
        <style>
            body {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            .content-wrapper {
                flex: 1;
            }
            .footer {
                background-color: #212529;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
                margin-top: auto;
            }
            .footer h5 {
                color: white;
                font-weight: bold;
                border-bottom: 2px solid rgba(255, 255, 255, 0.5);
                display: inline-block;
                padding-bottom: 5px;
            }
            .footer a {
                transition: all 0.3s ease;
            }
            .footer a.nav-link:hover {
                color: white !important;
                transform: translateX(5px);
            }
            .footer .social-links a:hover {
                transform: scale(1.2);
                color: #d3d3d3 !important;
            }
            #scrollTopBtn:hover {
                background-color: #0b5ed7;
                transform: scale(1.1);
            }
        </style>
    </body>
    </html>