<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About Us - AgriMart</title>

   <!-- Swiper CSS -->
   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- About Section -->
<section class="about">

   <div class="row">

      <div class="image">
         <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80" alt="AgriMart Fresh Produce Marketplace">
      </div>

      <div class="content">
         <h3>Why Choose AgriMart?</h3>
         <p>
            AgriMart bridges the gap between local agricultural farmers and consumers. Whether you are a household looking for fresh, daily harvested organic produce or a commercial vendor seeking bulk wholesale tiers, we offer transparent pricing, reliable logistics, and direct farm-to-table delivery.
         </p>
         <a href="contact.php" class="btn">Contact Us</a>
      </div>

   </div>

</section>

<!-- Reviews Section -->
<section class="reviews">
   
   <h1 class="heading">Client & Farmer Feedback</h1>

   <div class="swiper reviews-slider">

   <div class="swiper-wrapper">

      <div class="swiper-slide slide">
         <img src="https://i.pravatar.cc/150?img=11" alt="Anish Shrestha">
         <p>"The fresh produce delivered right to our kitchen in Pokhara has been outstanding. The vegetables are truly organic and harvested fresh from local farms."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Anish Shrestha</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="https://i.pravatar.cc/150?img=5" alt="Sujata Gurung">
         <p>"As a restaurant owner, sourcing quality ingredients at reasonable rates was always a challenge. AgriMart's wholesale supplier pricing tier saves us time and money every week."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Sujata Gurung</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="https://i.pravatar.cc/150?img=13" alt="Ramesh Thapa">
         <p>"Selling directly through AgriMart eliminated middleman commissions for our farm group. We get fair market prices for our harvest, and buyers receive genuine quality."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Ramesh Thapa</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="https://i.pravatar.cc/150?img=9" alt="Pooja Sharma">
         <p>"Extremely smooth order checkout process and prompt cash-on-delivery options. AgriMart has made grocery shopping so convenient for our family."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Pooja Sharma</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="https://i.pravatar.cc/150?img=60" alt="Bikram Adhikari">
         <p>"The ability to switch seamlessly between retail quantities and bulk orders is fantastic. High quality grains and fresh fruits delivered on schedule every time."</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Bikram Adhikari</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="https://i.pravatar.cc/150?img=24" alt="Kavya Rai">
         <p>"Reliable customer support and fast resolution whenever we have questions. AgriMart is setting a great standard for digital agricultural trade in Nepal!"</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
         </div>
         <h3>Kavya Rai</h3>
      </div>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>

<?php include 'components/footer.php'; ?>

<!-- Swiper JS -->
<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

<!-- Custom Script -->
<script src="js/script.js"></script>

<script>
const reviewsSwiper = new Swiper(".reviews-slider", {
   loop: true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable: true,
   },
   breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      991: {
        slidesPerView: 3,
      },
   },
});
</script>

</body>
</html>