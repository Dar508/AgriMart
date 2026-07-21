<footer class="footer">

   <section class="grid">

      <div class="box">
         <h3>quick links</h3>
         <a href="home.php"> <i class="fas fa-angle-right"></i> HOME</a>
         <a href="about.php"> <i class="fas fa-angle-right"></i> ABOUT</a>
         <a href="shop.php"> <i class="fas fa-angle-right"></i> SHOP</a>
         <a href="contact.php"> <i class="fas fa-angle-right"></i> CONTACT</a>
      </div>

      <div class="box">
         <h3>extra links</h3>
         <?php if (!empty($user_id)): ?>
            <a href="orders.php"> <i class="fas fa-angle-right"></i> ORDERS</a>
            <a href="cart.php"> <i class="fas fa-angle-right"></i> CART</a>
            <a href="update_user.php"> <i class="fas fa-angle-right"></i> PROFILE</a>
            <a href="components/user_logout.php" onclick="return confirm('Logout from AgriMart?');"> <i class="fas fa-angle-right"></i> LOGOUT</a>
         <?php else: ?>
            <a href="user_login.php"> <i class="fas fa-angle-right"></i> LOGIN</a>
            <a href="user_register.php"> <i class="fas fa-angle-right"></i> REGISTER</a>
            <a href="cart.php"> <i class="fas fa-angle-right"></i> CART</a>
            <a href="orders.php"> <i class="fas fa-angle-right"></i> ORDERS</a>
         <?php endif; ?>
      </div>

      <div class="box">
         <h3>contact us</h3>
         <a href="tel:+977980000000000"><i class="fas fa-phone"></i> +977 980000000000</a>
         <a href="tel:+977980001111111"><i class="fas fa-phone"></i> +977 980001111111</a>
         <a href="mailto:info@agrimart.com"><i class="fas fa-envelope"></i> info@agrimart.com</a>
         <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer"><i class="fas fa-map-marker-alt"></i> Kaski, Nepal - 37000 </a>
      </div>

      <div class="box">
         <h3>follow us</h3>
         <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i>Facebook</a>
         <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i>Twitter</a>
         <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i>Instagram</a>
         <a href="#" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin"></i>LinkedIn</a>
      </div>

   </section>

   <div class="credit">&copy; copyright @ <?= date('Y'); ?> by <span>AgriMart</span> | all rights reserved!</div>

</footer>