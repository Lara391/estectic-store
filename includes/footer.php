<?php
/**
 * =====================================================
 * FOOTER DEL SITIO
 * =====================================================
 * Archivo: includes/footer.php
 * 
 * Pie de página común para todas las páginas
 * =====================================================
 */
?>

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row mb-4">
                <!-- Sobre nosotros -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-spa text-success"></i> Estetic Store
                    </h5>
                    <p class="text-muted">
                        Somos tu tienda online de confianza para productos de estética y belleza de calidad.
                    </p>
                    <!-- Redes sociales -->
                    <div class="d-flex gap-2 mt-3">
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-facebook fa-lg"></i>
                        </a>
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </a>
                        <a href="#" class="text-white text-decoration-none">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Información -->
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Información</h6>
                    <ul class="list-unstyled text-muted">
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Acerca de nosotros
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Política de privacidad
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Términos y condiciones
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Cambios y devoluciones
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Preguntas frecuentes
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Categorías -->
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Categorías</h6>
                    <ul class="list-unstyled text-muted">
                        <li>
                            <a href="<?php echo SITE_URL; ?>productos/catalogo.php?categoria=1" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Cremas Faciales
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>productos/catalogo.php?categoria=2" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Cremas Corporales
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>productos/catalogo.php?categoria=3" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Cremas Hidratantes
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>productos/catalogo.php?categoria=4" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Cremas Antiage
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>productos/catalogo.php?categoria=5" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right"></i> Sérums
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Contacto</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt text-success"></i>
                            Av. Principal 123, Buenos Aires, Argentina
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone text-success"></i>
                            <a href="tel:+541234567890" class="text-muted text-decoration-none">
                                +54 9 1234 567890
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope text-success"></i>
                            <a href="mailto:info@estectic-store.com" class="text-muted text-decoration-none">
                                info@estectic-store.com
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-clock text-success"></i>
                            Lun - Vie: 9:00 - 18:00 hs
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="bg-secondary">

            <!-- Newsletter -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold mb-2">Suscríbete a nuestro Newsletter</h6>
                    <p class="text-muted text-sm">Recibe las mejores ofertas y promociones directo en tu correo</p>
                </div>
                <div class="col-md-6">
                    <form action="<?php echo SITE_URL; ?>newsletter/suscribir.php" method="POST" class="d-flex">
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="Tu correo electrónico"
                            required
                        >
                        <button type="submit" class="btn btn-success ms-2">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <hr class="bg-secondary">

            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6 text-muted text-sm">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> Estetic Store. Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                        <img src="<?php echo SITE_URL; ?>assets/img/mercado-pago.png" alt="Mercado Pago" height="30" title="Mercado Pago">
                        <img src="<?php echo SITE_URL; ?>assets/img/visa.png" alt="Visa" height="30" title="Visa">
                        <img src="<?php echo SITE_URL; ?>assets/img/mastercard.png" alt="Mastercard" height="30" title="Mastercard">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (opcional, pero útil) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Script principal -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>

    <?php if (isset($scripts_adicionales)): ?>
        <?php foreach ($scripts_adicionales as $archivo): ?>
            <script src="<?php echo SITE_URL . $archivo; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
